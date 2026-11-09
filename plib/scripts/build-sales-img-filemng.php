<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$dir='/var/www/vhosts/bahman.ir/httpdocs/sales-img';

$html=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/sales');
preg_match_all('#/sales-img/([0-9a-fA-F-]{36})_(600|960|1920)\\.(png|jpg)#', $html, $m);
$uuids=array_values(array_unique($m[1] ?? []));
echo "uuids=".count($uuids)."\n";

$sizes=[600,960,1920];
$made=0; $skip=0; $fail=0;
foreach ($uuids as $uuid) {
  foreach ($sizes as $w) {
    $dest="{$dir}/{$uuid}_{$w}.png";
    $destJpg="{$dir}/{$uuid}_{$w}.jpg";
    if (is_file($dest) || is_file($destJpg)) { $skip++; continue; }
    $tmp="/tmp/salesimg_{$uuid}_{$w}.png";
    @unlink($tmp);
    $meta=sh("curl -sL --max-time 30 -H 'Host: sitefile.bahman.ir' -o ".escapeshellarg($tmp)." -w '%{http_code}:%{size_download}' ".escapeshellarg("http://172.20.14.76/thumb/{$uuid}/{$w}"));
    if (!is_file($tmp) || filesize($tmp) < 500) {
      $fail++; echo "FAIL_DL $uuid $w $meta\n"; continue;
    }
    // resize with convert into tmp2 if possible
    $outTmp="/tmp/salesimg_out_{$uuid}_{$w}.png";
    @unlink($outTmp);
    $has=sh('command -v convert');
    if ($has !== '') {
      sh('convert '.escapeshellarg($tmp).' -resize '.escapeshellarg($w.'x').' -strip PNG32:'.escapeshellarg($outTmp).' 2>&1');
    }
    $src = (is_file($outTmp) && filesize($outTmp)>500) ? $outTmp : $tmp;
    $cp = sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($src).' '.escapeshellarg($dest).' 2>&1');
    if ($w === 1920) {
      // also jpg for banner template refs
      $jpgTmp="/tmp/salesimg_out_{$uuid}_{$w}.jpg";
      if ($has !== '') {
        sh('convert '.escapeshellarg($src).' -quality 85 '.escapeshellarg($jpgTmp).' 2>&1');
        if (is_file($jpgTmp)) {
          sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($jpgTmp).' '.escapeshellarg($destJpg).' 2>&1');
          @unlink($jpgTmp);
        }
      }
    }
    @unlink($tmp); @unlink($outTmp);
    if (is_file($dest)) {
      $made++;
      echo "OK $uuid $w bytes=".filesize($dest)." cp=$cp\n";
    } else {
      $fail++;
      echo "FAIL_CP $uuid $w cp=$cp\n";
    }
  }
}
echo "MADE=$made SKIP=$skip FAIL=$fail\n";
$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
echo "ls=".sh('ls -la '.escapeshellarg($dir.'/'.$uuid.'_600.png').' 2>&1')."\n";
echo "curl=".sh('curl -s -o /dev/null -w "%{http_code} %{size_download} %{content_type}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$uuid.'_600.png"')."\n";
echo "DONE\n";
