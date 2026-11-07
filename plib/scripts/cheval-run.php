<?php
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
copy(dirname(__DIR__).'/resources/_cheval_fix.php','/tmp/_cheval_fix.php');
$dst='/var/www/vhosts/bahman.ir/httpdocs/_cheval_fix.php';
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg('/tmp/_cheval_fix.php').' '.escapeshellarg($dst).' 2>&1');
echo trim((string)shell_exec('curl -sk --max-time 45 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php"'))."\n";
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
