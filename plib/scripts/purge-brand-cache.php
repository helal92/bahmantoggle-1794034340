<?php
function sh($c){return trim((string)shell_exec($c));}
$hdr='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/includes/header_include.tpl';
$h=file_get_contents($hdr);
if (preg_match('#<link[^>]+brand-slider\.css[^>]*>#',$h,$m)) echo "disk_tag=".$m[0]."\n";
else echo "disk_no_tag\n";

$tmp=rtrim(sys_get_temp_dir(),'/');
$n=0;
foreach (glob($tmp.'/bahman_*') ?: [] as $f) {
  if (is_file($f)) { @unlink($f); $n++; echo "rm ".basename($f)."\n"; }
}
foreach (glob($tmp.'/cms_cache/*') ?: [] as $f) { if (is_file($f)) { @unlink($f); $n++; } }
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*') ?: [] as $f) {
  $b=basename($f);
  if (strpos($b,'index')!==false || strpos($b,'header')!==false || strpos($b,'footer')!==false) { @unlink($f); echo "rmc $b\n"; }
}
echo "purged=$n\n";

// touch brand-slider.js to bump template_version if based on mtimes
@touch('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/brand-slider.js');
@touch('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/brand-slider.css');

$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/?purgebrand='.time().'"');
$hdrs=sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/?purgebrand2='.time().'"');
echo "hdrs=\n$hdrs\n";
if (preg_match('#<link[^>]+brand-slider\.css[^>]*>#',$page,$m)) {
  echo "page_tag=".$m[0]."\n";
  echo "blocking=".(strpos($m[0],'media="print"')===false?'1':'0')."\n";
} else echo "page_no_css_tag\n";
if (preg_match('#brand-slider\.js\?v=([^"]+)#',$page,$m)) echo "js_v=".$m[1]."\n";
// index.js imports brand-slider - version is on index.js
if (preg_match('#assets/index\.js\?v=([^"]+)#',$page,$m)) echo "index_js_v=".$m[1]."\n";
echo "DONE\n";
