<?php
function sh($c){return trim((string)shell_exec($c));}
$cfg='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php';
$c=file_get_contents($cfg);
// find product_menu_json case
$p=strpos($c,"product_menu_json");
echo "pos=$p\n";
if ($p!==false) echo substr($c,max(0,$p-200),1800)."\n---\n";
// agency details structure from search indexing around 1279
echo "agency index block:\n".substr($c, strpos($c,"->setType('agency')")-50, 900)."\n---\n";
// agency.tpl fields used
echo sh('grep -n -E "values\\.|agency_details|scores|phone|address|map|lat|lng|company|product" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/agency.tpl | head -80')."\n";
// sample live agency page one item attrs
$page=sh('curl -s --max-time 40 -H "Host: bahman.ir" "http://172.20.14.76/branch?t='.time().'"');
echo "branch_len=".strlen($page)."\n";
if (preg_match('/accordion-item[^>]+data-company="[^"]*"[^>]*>/',$page,$m)) echo "item=".$m[0]."\n";
// look inside accordion for phone/address structure
if (preg_match('/accordion-item[\s\S]{0,2500}/',$page,$m)) {
  echo "chunk=\n".preg_replace('/\s+/',' ',substr($m[0],0,1500))."\n";
}
echo "DONE\n";
