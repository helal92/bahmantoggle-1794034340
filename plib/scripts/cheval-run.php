<?php
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
copy(dirname(__DIR__).'/resources/_cheval_fix.php','/tmp/_cheval_fix.php');
$dst='/var/www/vhosts/bahman.ir/httpdocs/_cheval_fix.php';
@shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg('/tmp/_cheval_fix.php').' '.escapeshellarg($dst).' 2>&1');
$out = trim((string)shell_exec('curl -sk --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php"'));
echo $out."\n";
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
// bust opcache/home by requesting with cache bust
$home = (string)shell_exec('curl -sk --max-time 45 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "https://172.20.14.76/?_cb='.time().'"');
$has = (stripos($home,'CHEVAL 6 HEV')!==false);
echo "home_len=".strlen($home)." still_has_cheval_h1=".($has?'YES':'NO')."\n";
// count swiper slides mentioning CHEVAL
echo "cheval_occurrences=".substr_count(strtoupper($home),'CHEVAL')."\n";
