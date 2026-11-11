<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "files=\n".sh('find '.$root.' -iname "*agen*" 2>/dev/null')."\n";
echo "grep=\n".sh('grep -Rnl --include="*.tpl" --include="*.php" --include="*.js" -e "agencyFilterForm" -e "agency-search-select" -e "همه شرکت" -e "company_all" -e "agents" '.$root.' 2>/dev/null | head -40')."\n";

// tpl filter section
foreach (['agents.tpl','agency.tpl','agencies.tpl','representatives.tpl'] as $f) {
  $p=$root.'/'.$f;
  if (!is_file($p)) continue;
  echo "FOUND $f\n";
}
echo sh('ls '.$root.'/*.tpl | xargs -n1 basename | grep -iE "agen|repres|agent" ')."\n";

// config.php agency/company related
echo "config hits:\n".sh('grep -n -E "agency|company_all|agents|represent" '.$root.'/config.php | head -80')."\n";
echo "DONE\n";
