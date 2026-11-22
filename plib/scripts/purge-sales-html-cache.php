<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$patterns=[
  '/var/www/vhosts/bahman.ir/httpdocs/tmp/bahman_sales*',
  '/var/www/vhosts/bahman.ir/httpdocs/tmp/*sales*',
  '/var/www/vhosts/bahman.ir/httpdocs/cache/*sales*',
  '/var/www/vhosts/bahman.ir/httpdocs/templates_c/*',
];
foreach ($patterns as $pat) {
  foreach (glob($pat) ?: [] as $f) {
    if (!is_file($f)) continue;
    if (strpos($pat,'templates_c')!==false) {
      $t=@file_get_contents($f);
      if (!$t || (strpos($t,'sales')===false && strpos($t,'sales-img')===false)) continue;
    }
    @unlink($f);
    echo "rm $f\n";
  }
}
// find cache file names via grep in index.php
$idx='/var/www/vhosts/bahman.ir/httpdocs/index.php';
$c=file_get_contents($idx);
foreach (['bahman_sales','FAST_MODE','sales_page','page_cache','html_cache'] as $k) {
  $p=0; $hits=0;
  while (($p=strpos($c,$k,$p))!==false && $hits<3) {
    echo "IDX $k :: ".str_replace("\n"," ",substr($c,max(0,$p-40),120))."\n";
    $p+=strlen($k); $hits++;
  }
}
echo "tpl_has_onerror=".(strpos(file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl'),'onerror=')!==false?'1':'0')."\n";
// force render bypass: call include directly if possible
$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" -H "Cache-Control: no-cache" -H "Pragma: no-cache" "http://127.0.0.1/index.php?include=sales&_='.time().'"');
if ($page==='') $page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/index.php?include=sales&_='.time().'"');
echo "direct_len=".strlen($page)."\n";
echo "direct_onerror=".(strpos($page,'onerror=')!==false?'1':'0')."\n";
$page2=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/sales?_='.time().'"');
echo "sales_len=".strlen($page2)."\n";
echo "sales_onerror=".(strpos($page2,'onerror=')!==false?'1':'0')."\n";
// locate likely cache by searching file contents for old marker
echo "SEARCH:\n".sh('grep -Rsl --include="*.html" --include="*.cache" --include="*.tmp" --include="*.php" "2b8db709-b4c4-11f1-ad46-005056b972d8_600.png" /var/www/vhosts/bahman.ir/httpdocs/tmp /var/www/vhosts/bahman.ir/httpdocs/cache /var/www/vhosts/bahman.ir/httpdocs/templates_c 2>/dev/null | head -20')."\n";
echo "DONE\n";
