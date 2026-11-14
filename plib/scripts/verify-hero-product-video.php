<?php
$fm='/opt/psa/admin/bin/filemng';
$f='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/includes/footer_include.tpl';
$c=file_get_contents($f);
echo "footer_size=".strlen($c)."\n";
if (preg_match('/show_product.*?\{else if/s',$c,$m)) {
  echo "SHOW_PRODUCT_BLOCK:\n".$m[0]."\n";
} else {
  echo "NO_BLOCK\n";
  $p=strpos($c,'show_product');
  echo substr($c,max(0,$p-50),500)."\n";
}
$hb=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/hero-bahman.js');
$i=strpos($hb,'function V(a)');
echo "V=".substr($hb,$i,220)."\n";
echo "has_datasrc_in_V=".(strpos(substr($hb,$i,300),'data-src')!==false?'1':'0')."\n";
// clear caches
$cmd=$fm.' root exec / bash -c '.escapeshellarg(
  'rm -rf /var/www/vhosts/bahman.ir/httpdocs/templates_c/*; '
 .'rm -f /tmp/bahman_*.html /var/www/vhosts/bahman.ir/httpdocs/tmp/bahman_*.html; '
 .'find /var/www/vhosts/bahman.ir/httpdocs -maxdepth 3 -name "bahman_*page*.html" -delete 2>/dev/null; '
 .'rg -n "template_version" /var/www/vhosts/bahman.ir/httpdocs --glob "!templates/**" --glob "!templates_c/**" -g "*.php" | head -20; '
 .'echo CLEARED'
).' --allow-root 2>&1';
echo trim((string)shell_exec($cmd))."\n";
echo "DONE\n";
