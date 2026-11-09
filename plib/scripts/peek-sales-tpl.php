<?php
$f='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$c=file_get_contents($f);
echo "len=".strlen($c)."\n";
// find img.image blocks
if (preg_match_all('#.{0,80}<img[^>]*class=["\']image["\'][^>]*>.{0,80}#us',$c,$m)) {
  foreach ($m[0] as $i=>$x) echo "---IMG$i---\n$x\n";
}
// also search sales-img occurrences
preg_match_all('#.{0,60}sales-img.{0,120}#us',$c,$m2);
foreach (array_slice($m2[0],0,20) as $i=>$x) echo "---REF$i---\n$x\n";
echo "DONE\n";
