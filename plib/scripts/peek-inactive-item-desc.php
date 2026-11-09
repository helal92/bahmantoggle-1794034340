<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
$tpl=file_get_contents($root.'/sales.tpl');
// show inactive item block
if (preg_match('/sales-inactive-item.{0,1200}/s',$tpl,$m)) echo "ITEM:\n".$m[0]."\n---\n";
// active circular item for comparison
if (preg_match_all('/values\.desc.{0,80}/',$tpl,$m)) {
  echo "desc refs:\n";
  foreach ($m[0] as $x) echo "  $x\n";
}
// sample data: does inactive circulars have desc?
echo sh('grep -n "desc\|attachemnt\|inactive" '.$root.'/sales.tpl | head -60')."\n";
// CSS for inactive item
$css=file_get_contents($root.'/assets/circular.css');
if (preg_match_all('/\.accordion-section \.sales-inactive-item[^{]*\{[^}]*\}/',$css,$m)) {
  echo "CSS rules:\n";
  foreach ($m[0] as $r) echo $r."\n";
}
echo "DONE\n";
