<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "files=\n".sh('ls -la '.$root.'/assets/agents* '.$root.'/base_template/assets/agents* 2>/dev/null')."\n";
// product assign in agency case - current
$cfg=file_get_contents($root.'/config.php');
$p=strpos($cfg,"->setType('product')");
// find within agency case
$agencyPos=strpos($cfg,"case 'agency':");
$productPos=strpos($cfg,"->setType('product')",$agencyPos);
echo "agency product block:\n".substr($cfg,$productPos-120,350)."\n---\n";

// sample product options currently
$page=sh('curl -s --max-time 40 -H "Host: bahman.ir" "http://172.20.14.76/?include=agency&t='.time().'"');
if (preg_match('/id="filter-product"[^>]*>(.*?)<\/select>/s',$page,$m)) {
  preg_match_all('/<option([^>]*)>([^<]*)<\/option>/',$m[1],$opts,PREG_SET_ORDER);
  echo "product_options=".count($opts)."\n";
  foreach (array_slice($opts,0,8) as $o) echo "  attrs=".trim($o[1])." text=".$o[2]."\n";
}

// how footer loads agents js
echo sh('grep -n "agents_bahman\|agency-search" '.$root.'/includes/footer_include.tpl')."\n";
echo "DONE\n";
