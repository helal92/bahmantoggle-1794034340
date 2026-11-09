<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "count_css=\n".sh('grep -Rn --include="*.css" -e "sales-inactive-company__count" -e "sales-inactive-company" '.$root.' 2>/dev/null')."\n";
echo "---typography---\n";
echo file_get_contents($root.'/assets/typography.css');
echo "\n---avenir/num rules---\n";
echo sh('grep -Rn --include="*.css" -E "font-variant-numeric|avenir|fa-num|persian.?num|\\[lang|numbers" '.$root.'/assets 2>/dev/null | head -40')."\n";
// extract sales-related css file(s)
foreach (['assets/circular.css','assets/sales.css','assets/sales-page.css','assets/main.css'] as $rel) {
  $f=$root.'/'.$rel;
  if (!is_file($f)) continue;
  $c=file_get_contents($f);
  if (strpos($c,'sales-inactive')!==false || strpos($c,'__count')!==false) {
    echo "FOUND in $rel\n";
    if (preg_match_all('/\.sales-inactive[^{]*\{[^}]*\}/s',$c,$m)) {
      foreach ($m[0] as $b) echo $b."\n\n";
    }
  }
}
// also search unminified sales page css
echo sh('ls -la '.$root.'/assets/*sales* '.$root.'/assets/*circular* 2>/dev/null')."\n";
// how sales.tpl loads css
$tpl=file_get_contents($root.'/sales.tpl');
if (preg_match_all('/\.css[^"\']*|stylesheet[^>]*>/i',$tpl,$m)) {
  echo "tpl css refs:\n";
  foreach ($m[0] as $x) echo "  $x\n";
}
echo sh('grep -n "css\|stylesheet\|typography\|circular\|sales" '.$root.'/sales.tpl | head -40')."\n";
echo "DONE\n";
