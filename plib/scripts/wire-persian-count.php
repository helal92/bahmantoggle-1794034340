<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';

echo "date_only defs:\n".sh('grep -Rn --include="*.php" -e "function.*date_only" -e "smarty_modifier" -e "persian_digits" -e "bahman_cms_date" /var/www/vhosts/bahman.ir/httpdocs --exclude-dir=templates_c 2>/dev/null | head -40')."\n";

// Read relevant portion of config around date display helpers and smarty setup
$cfg='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php';
$c=file_get_contents($cfg);
// find function bahman_cms_date_only or similar and smarty plugins path
if (preg_match('/function\s+\w*date_only\w*\s*\(.*?^\}/ms',$c,$m)) echo "DATE_FN:\n".$m[0]."\n---\n";
// index.php smarty
$idx=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/index.php');
if (preg_match_all('/.{0,80}(Smarty|registerPlugin|plugins_dir|modifier|date_only).{0,120}/',$idx,$m)) {
  echo "index hits:\n";
  foreach (array_slice($m[0],0,40) as $x) echo "  ".preg_replace('/\s+/',' ',$x)."\n";
}
$mf=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/libs/myfunc.php');
if (preg_match_all('/.{0,40}(date_only|persian|modifier|Smarty).{0,100}/i',$mf,$m)) {
  echo "myfunc hits:\n";
  foreach (array_slice($m[0],0,40) as $x) echo "  ".preg_replace('/\s+/',' ',$x)."\n";
}

// Alternative: convert inactive_count when building sales data in config.php
// Find where inactive_count is set
echo "inactive_count set:\n".sh('grep -Rn --include="*.php" --include="*.tpl" -e "inactive_count" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman 2>/dev/null | head -30')."\n";
echo "DONE\n";
