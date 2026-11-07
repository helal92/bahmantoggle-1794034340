<?php
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$src=dirname(__DIR__).'/resources/_cheval_fix.php';
$dst='/var/www/vhosts/bahman.ir/httpdocs/_cheval_fix.php';
$tmp='/tmp/_cheval_fix.php';
copy($src,$tmp);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dst).' 2>&1');
echo "deployed user=$user\n";
echo "=== FIND ===\n";
echo trim((string)shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php?do=find"'))."\n";
echo "=== DISABLE ===\n";
echo trim((string)shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php?do=disable"'))."\n";
echo "=== VERIFY ===\n";
echo trim((string)shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php?do=find"'))."\n";
# remove oneshot
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
# bust home cache files if any in tmp of fpm - request home miss
echo trim((string)shell_exec('curl -sk -o /dev/null -w "home %{time_starttransfer} %{http_code}\n" --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?nocache='.time().'"'))."\n";
