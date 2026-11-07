<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
copy("$res/hero-media-fix.js",'/tmp/hero-media-fix.js');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/hero-media-fix.js').' '.escapeshellarg("$bh/templates/bahman/assets/hero-media-fix.js").' 2>&1');
// purge caches
file_put_contents('/tmp/_purge2.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); header("Content-Type:text/plain"); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_purge2.php').' '.escapeshellarg("$bh/_purge2.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_purge2.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_purge2.php").' 2>&1');
foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p)) @unlink($p);
echo "js=".filesize("$bh/templates/bahman/assets/hero-media-fix.js")."\n";
echo "syn=".trim(shell_exec("ss -tn state syn-recv | wc -l"))." load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo shell_exec("curl -sk --max-time 8 -o /dev/null -w 'v %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4'");
echo "DONE\n";
