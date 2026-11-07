<?php
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
copy(dirname(__DIR__).'/resources/_cheval_fix.php','/tmp/_cheval_fix.php');
$dst='/var/www/vhosts/bahman.ir/httpdocs/_cheval_fix.php';
@shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg('/tmp/_cheval_fix.php').' '.escapeshellarg($dst).' 2>&1');
echo trim((string)shell_exec('curl -sk --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php"'))."\n";
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
sleep(1);
$hdr = (string)shell_exec('curl -skI --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?nocache='.time().'"');
echo "HEADERS1:\n$hdr\n";
$home = (string)shell_exec('curl -sk --max-time 45 -H "Host: bahman.ir" "https://172.20.14.76/?nocache='.time().'"');
echo "home_len=".strlen($home)." still_has=".((stripos($home,'CHEVAL 6 HEV')!==false)?'YES':'NO')." cache_hdr=".(preg_match('/X-Bahman-Cache:\s*(\S+)/i',$hdr,$m)?$m[1]:'?')."\n";
// second request should be HIT of new cache
$hdr2 = (string)shell_exec('curl -skI --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/"');
$home2 = (string)shell_exec('curl -sk --max-time 45 -H "Host: bahman.ir" "https://172.20.14.76/"');
echo "second still_has=".((stripos($home2,'CHEVAL 6 HEV')!==false)?'YES':'NO')." hdr=".trim($hdr2)."\n";
