<?php
function sh($c){return trim((string)shell_exec($c));}
$cfg=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php');
$p=strpos($cfg,"case 'product_menu_json':");
echo substr($cfg,$p,2200)."\n---\n";
$idx=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/index.php');
echo "index after config template checks:\n";
foreach (explode("\n",$idx) as $i=>$l) {
  if (stripos($l,'product_menu')!==false || stripos($l,'exit')!==false || stripos($l,'template')!==false && $i>180) {
    if ($i>180 && $i<280) echo ($i+1).": ".rtrim($l)."\n";
  }
}
echo sh('sed -n "200,280p" /var/www/vhosts/bahman.ir/httpdocs/index.php')."\n";
echo "DONE\n";
