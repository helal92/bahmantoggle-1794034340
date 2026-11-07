<?php
$f='/opt/psa/admin/plib/modules/bahmantoggle/scripts/sales-optimize.php';
echo "exists=". (is_file($f)?'yes':'no') ." size=".(is_file($f)?filesize($f):0)."\n";
echo "mtime=".(is_file($f)?date('c',filemtime($f)):'')."\n";
$lines=is_file($f)?file($f):[];
foreach ([35,36,37,38,39] as $n) {
  echo "L$n:".(isset($lines[$n-1])?rtrim($lines[$n-1]):'(missing)')."\n";
}
echo "--- head ---\n";
echo implode('', array_slice($lines,0,45));
