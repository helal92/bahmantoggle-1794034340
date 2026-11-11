<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
$jsPath=$root.'/assets/brand-slider.js';
$js = file_get_contents('/tmp/brand-slider.js.new');
if ($js===false || strlen($js)<500) { echo "JS_SRC_MISS\n"; exit(1); }
$tmp='/tmp/brand-slider.js.deploy';
file_put_contents($tmp,$js);
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($jsPath).' 2>&1');
echo "js_bytes=".filesize($jsPath)."\n";
echo "lint_markers=".((strpos(file_get_contents($jsPath),'ResizeObserver')!==false && strpos(file_get_contents($jsPath),'document.fonts')!==false)?'1':'0')."\n";

// Load brand-slider.css as normal stylesheet on homepage path (not print/onload race).
// header_include uses media=print trick for many CSS files — switch brand-slider to blocking.
$hdr=$root.'/includes/header_include.tpl';
$h=file_get_contents($hdr);
$old='<link rel="stylesheet" media="print" onload="this.onload=null;this.media=\'all\'" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">';
$new='<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">';
if (strpos($h,$new)!==false) echo "HDR_ALREADY\n";
elseif (strpos($h,$old)!==false) {
  $h=str_replace($old,$new,$h);
  $tmp='/tmp/header_include.tpl.brand';
  file_put_contents($tmp,$h);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($hdr).' 2>&1');
  echo "HDR_PATCHED\n";
} else {
  echo "HDR_LINK_MISS\n";
  if (preg_match('/brand-slider\.css[^>]*>/',$h,$m)) echo $m[0]."\n";
}

// clear homepage html cache
$tmpDir=rtrim(sys_get_temp_dir(), '/');
foreach (glob($tmpDir.'/bahman_*.html') ?: [] as $f) { @unlink($f); echo "rm ".basename($f)."\n"; }
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*index*') ?: [] as $f) @unlink($f);

$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/?bcfix='.time().'"');
echo "page_has_brand_css_blocking=".(preg_match('/brand-slider\.css[^>]*>/',$page,$m) && strpos($m[0],'media="print"')===false?'1':'0')."\n";
if (preg_match('/brand-slider\.css[^>]*>/',$page,$m)) echo "css_tag=".$m[0]."\n";
$jsLive=sh('curl -s --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/templates/bahman/assets/brand-slider.js?v='.time().'"');
echo "live_js_has_RO=".(strpos($jsLive,'ResizeObserver')!==false?'1':'0')." live_len=".strlen($jsLive)."\n";
echo "DONE\n";
