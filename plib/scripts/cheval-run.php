<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$bh='/var/www/vhosts/bahman.ir/httpdocs';

function fcp($fm,$user,$src,$dst){
  copy($src,'/tmp/'.basename($src).'.deploy');
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg('/tmp/'.basename($src).'.deploy').' '.escapeshellarg($dst).' 2>&1');
}

// 1) deploy file.php
fcp($fm,$sfUser,"$res/file.php","$sf/file.php");
echo "file.php size=".filesize("$sf/file.php")."\n";

// 2) ensure v/ exists + htaccess video expires
@mkdir("$sf/v", 0775, true);
$ht=file_get_contents("$sf/.htaccess");
if ($ht !== false && strpos($ht, 'video/mp4') === false) {
  $ht=str_replace(
    'ExpiresByType image/gif "access plus 1 year"',
    "ExpiresByType image/gif \"access plus 1 year\"\n    ExpiresByType video/mp4 \"access plus 1 year\"\n    ExpiresByType video/webm \"access plus 1 year\"",
    $ht
  );
  file_put_contents('/tmp/htaccess.deploy',$ht);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' cp '.escapeshellarg('/tmp/htaccess.deploy').' '.escapeshellarg("$sf/.htaccess").' 2>&1');
}

// 3) patch index.tpl — remove eager src+autoplay on first hero video
$tpl="$bh/templates/bahman/index.tpl";
$tplBody=file_get_contents($tpl);
$old='data-src="{$admin_setting.file_url}/{$item.image}"{if $hero_i eq 0} src="{$admin_setting.file_url}/{$item.image}" autoplay{/if}>';
$new='data-src="{$admin_setting.file_url}/{$item.image}">';
if (strpos($tplBody,$old)!==false) {
  $tplBody=str_replace($old,$new,$tplBody);
  file_put_contents('/tmp/index.tpl.deploy',$tplBody);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/index.tpl.deploy').' '.escapeshellarg($tpl).' 2>&1');
  echo "index.tpl patched\n";
} else if (strpos($tplBody,'src="{$admin_setting.file_url}/{$item.image}" autoplay')===false) {
  echo "index.tpl already without eager src\n";
} else {
  echo "index.tpl pattern NOT FOUND\n";
}

// 4) deploy hero-media-fix.js
fcp($fm,$bhUser,"$res/hero-media-fix.js","$bh/templates/bahman/assets/hero-media-fix.js");
echo "hero-js size=".filesize("$bh/templates/bahman/assets/hero-media-fix.js")."\n";

// bump template_version if present in a settings file / config
$config="$bh/templates/bahman/config.php";
if (is_file($config)) {
  $c=file_get_contents($config);
  if (preg_match('/template_version[\'\"]?\s*=>\s*[\'\"](\d+)[\'\"]/',$c,$m) || preg_match('/\$template_version\s*=\s*[\'\"]?(\d+)/',$c,$m)) {
    echo "found template_version=".$m[1]."\n";
  }
}

// 5) clear home HTML cache
foreach (glob('/tmp/bahman_*page*.html') ?: [] as $p) { @unlink($p); echo "rm ".basename($p)."\n"; }

// 6) verify sitefile redirect
$id='61491e0c-bfc8-11f1-9600-005056b972d8';
$hdr=shell_exec("curl -skI --max-time 20 -H 'Host: sitefile.bahman.ir' -H 'Referer: https://bahman.ir/' -H 'Range: bytes=0-' 'https://172.20.14.76/$id'");
echo "VIDEO_UUID_HEADERS:\n$hdr\n";
if (preg_match('/^Location:\s*(\S+)/mi',$hdr,$m)) {
  $loc=trim($m[1]);
  echo "follow $loc\n";
  $hdr2=shell_exec("curl -skI --max-time 20 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76$loc'");
  echo "STATIC_HEADERS:\n$hdr2\n";
}
echo "symlink:\n";
echo shell_exec("ls -la $sf/v/ | head -10");

// 7) verify homepage markup
sleep(1);
$html=shell_exec("curl -sk --max-time 30 -H 'Host: bahman.ir' 'https://172.20.14.76/?nocache=".time()."'");
echo "home_len=".strlen($html)."\n";
if (preg_match('/<video class="hero-media[^"]*"[^>]*>/',$html,$m)) {
  echo "FIRST_VIDEO_TAG:\n".$m[0]."\n";
  echo "has_eager_src=".(preg_match('/\ssrc="/',$m[0])?'YES':'NO')."\n";
  echo "has_autoplay=".(stripos($m[0],'autoplay')!==false?'YES':'NO')."\n";
}
echo "hero_js_ref=".(strpos($html,'hero-media-fix.js')!==false?'yes':'no')."\n";
echo "DONE\n";
