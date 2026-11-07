<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$bhUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg("$bh/index.php")));
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources';
foreach ([
  'assets/home-product-slider.js'=>"$bh/templates/bahman/assets/home-product-slider.js",
  'assets/slider_car-bahman.js'=>"$bh/templates/bahman/assets/slider_car-bahman.js",
  'assets/product-tabs.css'=>"$bh/templates/bahman/assets/product-tabs.css",
] as $src=>$dst) {
  $from="$res/$src";
  if (!is_file($from) && basename($src)==='home-product-slider.js') $from="$res/home-product-slider.js";
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($from).' '.escapeshellarg($dst).' 2>&1');
  echo basename($dst)." ".(is_file($dst)?filesize($dst):0)."\n";
}
foreach (glob('/tmp/bahman_*')?:[] as $p) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');
$js=file_get_contents("$bh/templates/bahman/assets/home-product-slider.js");
echo "has_repair=".(strpos($js,'repairSliders')!==false?'Y':'N')."\n";
echo "DONE\n";
