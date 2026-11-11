<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "tpl product filter:\n".sh('grep -n -A25 "filter-product\|نام محصول\|product" '.$root.'/agency.tpl | head -80')."\n";
echo "---js agents---\n";
echo sh('grep -n -E "filter-company|filter-product|company|product" '.$root.'/assets/agents_bahman.js | head -80')."\n";
echo "---config agency product---\n";
echo sh('sed -n "3210,3230p" '.$root.'/config.php')."\n";
echo "---js file len---\n";
echo "agents_js=".filesize($root.'/assets/agents_bahman.js')."\n";
// dump relevant filter logic from agents_bahman.js
$js=file_get_contents($root.'/assets/agents_bahman.js');
foreach (['filter-product','filter-company','product','initAgency'] as $k) {
  $p=strpos($js,$k);
  if ($p!==false) echo "hit $k @ $p\n";
}
echo "DONE\n";
