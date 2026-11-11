<?php
function sh($c){return trim((string)shell_exec($c));}
$cfg='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php';
$c=file_get_contents($cfg);
// path map
if (preg_match_all('/[\'\"][^\'\"]*agency[^\'\"]*[\'\"]\s*=>\s*[\'\"][^\'\"]+/i',$c,$m)) {
  foreach ($m[0] as $x) echo "MAP $x\n";
}
echo sh('grep -n "agency" '.$cfg.' | grep -iE "path|route|include|=>" | head -40')."\n";

// how company multi values parsed elsewhere
echo "company field parse patterns:\n";
echo sh('grep -n -E "values\[.company.\]|explode.*,.*company|companyIds" '.$cfg.' | head -40')."\n";

// bahman_cms_sort_by_list_priority
if (preg_match('/function bahman_cms_sort_by_list_priority.*?^\}/ms',$c,$m)) echo "SORT_FN:\n".$m[0]."\n";

// Compare option count vs agency_companies from page include
$page=sh('curl -s --max-time 40 -H "Host: bahman.ir" "http://172.20.14.76/?include=agency&dbg='.time().'"');
// count company cards / headings using agency_companies
preg_match_all('/agency-company-title|company-group|data-company=/',$page,$m);
echo "markers=".json_encode($m[0])."\n";
// from tpl structure around line 237
if (preg_match_all('/class="[^"]*company[^"]*"[^>]*>\s*([^<]{2,80})/u',$page,$m)) {
  echo "company class texts sample:\n";
  foreach (array_slice(array_unique($m[1]),0,20) as $t) echo "  $t\n";
}
// Look in agency.tpl for listing structure
$tpl=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/agency.tpl');
echo "--- listing ---\n";
echo sh('sed -n "230,300p" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/agency.tpl')."\n";
echo "DONE\n";
