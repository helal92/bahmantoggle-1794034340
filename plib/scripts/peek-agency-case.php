<?php
function sh($c){return trim((string)shell_exec($c));}
$cfg='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php';
$lines=file($cfg);
// print agency case block ~3068-3230
for ($i=3067;$i<3230;$i++) echo ($i+1).':'.$lines[$i];
echo "---TPL---\n";
$tpl=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/agency.tpl');
if (preg_match('/agencyFilterForm.{0,2500}/s',$tpl,$m)) echo $m[0]."\n";
echo "---company option loop---\n";
echo sh('grep -n -E "agency_companies|company_all|همه شرکت|agency-search-select|option" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/agency.tpl | head -60')."\n";
echo "DONE\n";
