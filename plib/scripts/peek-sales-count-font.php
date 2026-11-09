<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs';
echo "grep=\n".sh('grep -Rn --include="*.tpl" --include="*.css" --include="*.scss" --include="*.js" -e "sales-inactive-company" -e "accordion_header" -e "company-count" -e "sales-count" -e "inactive-company" '.$root.'/templates/bahman '.$root.'/assets 2>/dev/null | head -80')."\n";
echo "---sales.tpl count areas---\n";
$c=file_get_contents($root.'/templates/bahman/sales.tpl');
foreach (['sales-inactive','accordion_header','count','badge','company'] as $k) {
  if (preg_match_all('/^.*'.$k.'.*$/mi',$c,$m)) {
    echo "KEY $k:\n";
    foreach (array_slice(array_unique($m[0]),0,25) as $line) echo "  ".trim($line)."\n";
  }
}
echo "---css fonts---\n";
echo sh('grep -Rn --include="*.css" -e "font-family" -e "IRANSans" -e "IranSans" -e "vazir" -e "Yekan" -e "fa-num" -e "persian" '.$root.'/assets '.$root.'/templates/bahman 2>/dev/null | head -60')."\n";
echo "DONE\n";
