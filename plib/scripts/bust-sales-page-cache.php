<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
// Discover PHP temp dir as the vhost user / php-fpm
$probe='/tmp/_bahman_tmpdir_probe.php';
file_put_contents($probe, "<?php echo sys_get_temp_dir().\"\\n\".(is_file(sys_get_temp_dir().'/bahman_sales_page_v4.html')?'HAS':'NO').\"\\n\".@filesize(sys_get_temp_dir().'/bahman_sales_page_v4.html');");
$dest='/var/www/vhosts/bahman.ir/httpdocs/_tmpdir_probe.php';
echo "cp=".sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($probe).' '.escapeshellarg($dest))."\n";
echo "probe=".sh('curl -s --max-time 10 -H "Host: bahman.ir" http://172.20.14.76/_tmpdir_probe.php')."\n";
// also search common private tmp paths
echo "FIND:\n".sh('find /tmp /var/tmp /var/www/vhosts/bahman.ir -name "bahman_sales_page_v4.html" 2>/dev/null | head -20')."\n";
// delete whatever we find via root
foreach (explode("\n", sh('find /tmp /var/tmp /var/www/vhosts/bahman.ir -name "bahman_sales_page_v4.html" 2>/dev/null')) as $f) {
  if ($f===''||!is_file($f)) continue;
  @unlink($f);
  echo "rm $f\n";
}
// also force regenerate sales compile
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) { @unlink($f); echo "rmc $f\n"; }
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*.php') ?: [] as $f) {
  $t=@file_get_contents($f);
  if ($t && strpos($t,'sales.tpl')!==false) { @unlink($f); echo "rmc2 ".basename($f)."\n"; }
}
sleep(1);
$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?_bust='.time().'"');
echo "len=".strlen($page)."\n";
echo "cache_hdr=".sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/sales?_bust2='.time().'" | tr -d "\\r" | grep -iE "HTTP/|X-Bahman|Cache-Control"')."\n";
echo "onerror=".(strpos($page,'onerror=')!==false?'1':'0')."\n";
$p=strpos($page,'2b8db709');
echo "snip=".str_replace("\n"," ",substr($page,max(0,$p-20),260))."\n";
@unlink($dest);
echo "DONE\n";
