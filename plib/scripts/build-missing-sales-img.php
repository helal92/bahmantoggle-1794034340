<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'));}

$dir='/var/www/vhosts/bahman.ir/httpdocs/sales-img';
@mkdir($dir, 0755, true);

// Collect UUIDs from live /sales HTML + active directive images that appear there
$html=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/sales');
preg_match_all('#/sales-img/([0-9a-fA-F-]{36})_(600|960|1920)\\.(png|jpg)#', $html, $m);
$uuids=array_values(array_unique($m[1] ?? []));
echo "uuids_from_html=".count($uuids)."\n";

$sizes=[600,960,1920];
$made=0; $skip=0; $fail=0; $logs=[];
foreach ($uuids as $uuid) {
  foreach ($sizes as $w) {
    $outPng="{$dir}/{$uuid}_{$w}.png";
    $outJpg="{$dir}/{$uuid}_{$w}.jpg";
    if (is_file($outPng) || is_file($outJpg)) { $skip++; continue; }
    $tmp="/tmp/salesimg_{$uuid}_{$w}.img";
    // Prefer sitefile thumb endpoint (works for this uuid)
    $url="http://172.20.14.76/thumb/{$uuid}/{$w}";
    $cmd="curl -sL --max-time 30 -H 'Host: sitefile.bahman.ir' -o ".escapeshellarg($tmp)." -w '%{http_code}:%{content_type}:%{size_download}' ".escapeshellarg($url);
    $meta=sh($cmd);
    [$code,$ctype,$size]=array_pad(explode(':',$meta,3),3,'');
    if ($code!=='200' || !is_file($tmp) || filesize($tmp)<1000) {
      // fallback raw file
      @unlink($tmp);
      $cmd2="curl -sL --max-time 30 -H 'Host: sitefile.bahman.ir' -o ".escapeshellarg($tmp)." -w '%{http_code}:%{content_type}:%{size_download}' ".escapeshellarg("http://172.20.14.76/{$uuid}");
      $meta=sh($cmd2);
      [$code,$ctype,$size]=array_pad(explode(':',$meta,3),3,'');
    }
    if ($code!=='200' || !is_file($tmp) || filesize($tmp)<500) {
      $fail++; $logs[]="FAIL $uuid $w meta=$meta"; @unlink($tmp); continue;
    }
    // convert/resize to exact width with ImageMagick if available, else copy
    $hasConvert=sh('command -v convert || command -v magick');
    if ($hasConvert!=='') {
      $bin=str_contains($hasConvert,'magick')?'magick':'convert';
      $conv=sh($bin.' '.escapeshellarg($tmp).' -resize '.escapeshellarg($w.'x').' -strip PNG32:'.escapeshellarg($outPng).' 2>&1');
      if (!is_file($outPng) || filesize($outPng)<500) {
        // copy as-is
        copy($tmp, $outPng);
      }
    } else {
      copy($tmp, $outPng);
    }
    @unlink($tmp);
    // ownership
    root('chown bmcars.bahman.ir_vzb4o368qoo:psacln '.escapeshellarg($outPng).' 2>/dev/null; chmod 644 '.escapeshellarg($outPng));
    // also create .jpg alias for 1920 banner template if needed
    if ($w===1920 && !is_file($outJpg)) {
      // hardlink/copy png to jpg name only if template expects jpg - better convert
      if ($hasConvert!=='') {
        $bin=str_contains($hasConvert,'magick')?'magick':'convert';
        sh($bin.' '.escapeshellarg($outPng).' -quality 85 '.escapeshellarg($outJpg).' 2>&1');
        root('chown bmcars.bahman.ir_vzb4o368qoo:psacln '.escapeshellarg($outJpg).' 2>/dev/null; chmod 644 '.escapeshellarg($outJpg));
      }
    }
    $made++;
    $logs[]="OK $uuid $w bytes=".filesize($outPng)." meta=$meta";
  }
}
echo "MADE=$made SKIP=$skip FAIL=$fail\n";
echo implode("\n", $logs)."\n";
$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
echo "check=".sh('ls -la '.escapeshellarg($dir.'/'.$uuid.'_600.png').' 2>&1')."\n";
echo "curl=".sh('curl -s -o /dev/null -w "%{http_code} %{size_download} %{content_type}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$uuid.'_600.png"')."\n";
echo "DONE\n";
