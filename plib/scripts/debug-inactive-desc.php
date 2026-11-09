<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
$tpl=file_get_contents($root.'/sales.tpl');
echo "tpl_has_desc_span=".(strpos($tpl,'sales-inactive-item__desc')!==false?'1':'0')."\n";
if (preg_match('/sales-inactive-item__title.{0,450}/s',$tpl,$m)) echo "block:\n".$m[0]."\n---\n";

// compiled?
echo "compiled=\n".sh('ls -la /var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales* 2>/dev/null')."\n";
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) {
  $c=file_get_contents($f);
  echo basename($f)." has_desc=".(strpos($c,'sales-inactive-item__desc')!==false?'1':'0')." mtime=".date('c',filemtime($f))."\n";
}

// page cache files
$tmp=rtrim(sys_get_temp_dir()),'/');
echo "html_caches:\n";
foreach (glob($tmp.'bahman_*.html') ?: [] as $f) {
  echo basename($f)." ".filesize($f)." ".date('c',filemtime($f))." has_desc=".(strpos(file_get_contents($f),'sales-inactive-item__desc')!==false?'1':'0')."\n";
}

// Check if inactive circulars have desc in DB via a quick bootstrap if possible
// Look at how sales data is loaded - maybe values keys
echo sh('grep -n "groupedInactive\|inactive_count\|values\[" '.$root.'/config.php | head -40')."\n";

// Fetch with cache buster header and check X-Bahman-Cache
$hdr=sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/sales?x='.time().'"');
echo "hdr=\n$hdr\n";
$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?x='.time().'"');
echo "len=".strlen($page)." has_desc=".(strpos($page,'sales-inactive-item__desc')!==false?'1':'0')."\n";
// show one inactive item html
if (preg_match('/sales-inactive-item".{0,500}/s',$page,$m)) echo "item_html=\n".$m[0]."\n";

// Direct DB sample of desc for sales circulars - find DB creds
$my=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/libs/myconf.php');
if (preg_match_all('/define\(\s*[\'"]DB_[A-Z]+[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/',$my,$m,PREG_SET_ORDER)) {
  foreach ($m as $x) echo "def ".$x[0]."\n";
}
// try parse host user pass name
preg_match('/DB_HOST[\'"]\s*,\s*[\'"]([^\'"]+)/',$my,$h);
preg_match('/DB_USER[\'"]\s*,\s*[\'"]([^\'"]+)/',$my,$u);
preg_match('/DB_PASS[\'"]\s*,\s*[\'"]([^\'"]+)/',$my,$p);
preg_match('/DB_NAME[\'"]\s*,\s*[\'"]([^\'"]+)/',$my,$n);
if (!$h) {
  // alternate config style
  echo "myconf_snip=\n".substr($my,0,800)."\n";
}
echo "DONE\n";
