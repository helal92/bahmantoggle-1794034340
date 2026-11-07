<?php
$fm='/usr/local/psa/admin/bin/filemng';
$apiUser='bmcars-api.bahman.ir_ghgap00458p';
$flag='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE';
file_put_contents('/tmp/FAST_MODE.flag',"on\n".date('c')."\n");
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($apiUser).' cp '.escapeshellarg('/tmp/FAST_MODE.flag').' '.escapeshellarg($flag).' 2>&1');

$bahmanUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php 2>/dev/null"));
$bahmanFlag='/var/www/vhosts/bahman.ir/httpdocs/.FAST_MODE';
if ($bahmanUser !== '') {
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bahmanUser).' cp '.escapeshellarg('/tmp/FAST_MODE.flag').' '.escapeshellarg($bahmanFlag).' 2>&1');
}
echo "\nsitefile_FAST=".(is_file($flag)?'ON':'OFF')."\n";
echo "bahman_FAST=".(is_file($bahmanFlag)?'ON':'OFF')."\n";
