<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$bhUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg("$bh/index.php")));
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources';
$from="$res/assets/home-product-slider.js";
if(!is_file($from))$from="$res/home-product-slider.js";
$dst="$bh/templates/bahman/assets/home-product-slider.js";
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($from).' '.escapeshellarg($dst).' 2>&1');
$cssFrom="$res/assets/product-tabs.css"; $cssDst="$bh/templates/bahman/assets/product-tabs.css";
if(is_file($cssFrom)) echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($cssFrom).' '.escapeshellarg($cssDst).' 2>&1');
echo "js=".filesize($dst)." insert=".(strpos(file_get_contents($dst),'insertCarousels')!==false?'Y':'N')."\n";
foreach (glob('/tmp/bahman_*')?:[] as $p) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');
echo "DONE\n";
