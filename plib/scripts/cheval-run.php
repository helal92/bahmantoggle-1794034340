<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$id='61491e0c-bfc8-11f1-9600-005056b972d8';

function fcp($fm,$user,$src,$dst){
  $tmp='/tmp/'.basename($src).'.'.getmypid();
  copy($src,$tmp);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dst).' 2>&1');
  @unlink($tmp);
}

// 1) force redeploy file.php
fcp($fm,$sfUser,"$res/file.php","$sf/file.php");
$has = strpos(file_get_contents("$sf/file.php"), "Location: /v/") !== false;
echo "file.php_redirect=".($has?'YES':'NO')." size=".filesize("$sf/file.php")."\n";
if (!$has) { echo "FATAL no redirect in deployed file\n"; exit(1); }

// 2) ensure v dir + symlink for known hero videos
@mkdir("$sf/v",0775,true);
foreach (['61491e0c-bfc8-11f1-9600-005056b972d8','a9b88900-aead-11f1-ad46-005056b972d8'] as $uuid) {
  // resolve via meta cache
  $meta=null;
  foreach (glob("$sf/cache/meta_*.json") ?: [] as $mf) {
    $j=json_decode(@file_get_contents($mf),true);
    if (($j['val']['uuid'] ?? '') === $uuid) { $meta=$j['val']; break; }
  }
  if (!$meta) continue;
  $real=realpath($sf.'/'.$meta['url'].'/'.$meta['file_name']);
  if (!$real) $real=realpath('/var/www/vhosts/siteapi.bahman.ir/httpdocs/'.ltrim(str_replace('../','',$meta['url'].'/'.$meta['file_name']),'/'));
  // url is ../uploads/... from file_manager
  if (!$real) {
    $cand = $sf . '/' . $meta['url'] . '/' . $meta['file_name'];
    $real = realpath($cand);
  }
  $link="$sf/v/$uuid.mp4";
  if ($real && is_file($real)) {
    if (is_link($link) || is_file($link)) @unlink($link);
    symlink($real,$link);
    echo "link $uuid -> $real\n";
  } else {
    echo "MISSING real for $uuid\n";
  }
}

// 3) patch index.tpl data-src to static /v/ path (skip PHP entirely for hero video)
$tpl="$bh/templates/bahman/index.tpl";
$body=file_get_contents($tpl);
$from='data-src="{$admin_setting.file_url}/{$item.image}">';
$to='data-src="{$admin_setting.file_url}/v/{$item.image}.mp4">';
if (strpos($body,$from)!==false) {
  $body=str_replace($from,$to,$body);
  file_put_contents('/tmp/index.tpl.vpath',$body);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/index.tpl.vpath').' '.escapeshellarg($tpl).' 2>&1');
  echo "index.tpl data-src -> /v/\n";
} elseif (strpos($body,$to)!==false) {
  echo "index.tpl already /v/\n";
} else {
  echo "index.tpl pattern missing\n";
  echo shell_exec("grep -n 'data-src' ".escapeshellarg($tpl)." | head");
}

// 4) hero js
fcp($fm,$bhUser,"$res/hero-media-fix.js","$bh/templates/bahman/assets/hero-media-fix.js");

// 5) clear caches
foreach (glob('/tmp/bahman_*page*.html') ?: [] as $p) @unlink($p);
foreach (glob('/tmp/bahman_product_menu*') ?: [] as $p) @unlink($p);
$tc="$bh/templates_c";
if (is_dir($tc)) foreach (glob("$tc/*")?:[] as $p) if(is_file($p)) @unlink($p);

// purge via PHP request
file_put_contents('/tmp/_purge.php','<?php $d=sys_get_temp_dir(); foreach(glob($d."/bahman_*")?:[] as $p){@unlink($p);} header("Content-Type:text/plain"); echo "purged\\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_purge.php').' '.escapeshellarg("$bh/_purge.php").' 2>&1');
echo shell_exec('curl -sk --max-time 15 -H "Host: bahman.ir" "https://172.20.14.76/_purge.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_purge.php").' 2>&1');

// 6) reload sitefile php-fpm to drop stuck video streams
echo shell_exec("kill -USR2 \$(pgrep -f 'php-fpm: master process.*7.4' | head -1) 2>&1; echo fpm_usr2_done");

sleep(1);

// 7) verify
echo "=== UUID request (must 302) ===\n";
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/$id'");
echo "=== /v static ===\n";
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/$id.mp4'");

$html=shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/?r='.time().'"');
echo "home_len=".strlen($html)."\n";
if (preg_match('/<video class="hero-media[^"]*"[^>]*>/',$html,$m)) echo "V0: ".$m[0]."\n";

// parallel: homepage + 8 css + static video range should stay fast
echo "\nparallel stress:\n";
$cmds=[
  "curl -sk --max-time 20 -o /dev/null -w 'home %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/'",
];
foreach (['navigation.css','index.css','hero-bahman.css','hero-media-fix.css','index2.css','product-tabs.css','footer-bahman.css','active-sale-btn.css'] as $i=>$css) {
  $cmds[]="curl -sk --max-time 20 -o /dev/null -w '$css %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/templates/bahman/assets/$css'";
}
$cmds[]="curl -sk --max-time 20 -o /dev/null -w 'video %{http_code} %{time_total} %{size_download}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-65535' -r 0-65535 'https://172.20.14.76/v/$id.mp4'";
echo shell_exec(implode(' & ',$cmds).' & wait');
echo "load: ".@file_get_contents('/proc/loadavg')."\n";
echo "DONE\n";
