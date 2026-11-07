<?php
$fm='/usr/local/psa/admin/bin/filemng';
$apiUser='bmcars-api.bahman.ir_ghgap00458p';
$flag='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE';
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($apiUser).' rm '.escapeshellarg($flag).' 2>&1');
$bahmanUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php 2>/dev/null"));
$bahmanFlag='/var/www/vhosts/bahman.ir/httpdocs/.FAST_MODE';
if ($bahmanUser !== '' && is_file($bahmanFlag)) {
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bahmanUser).' rm '.escapeshellarg($bahmanFlag).' 2>&1');
}
@unlink('/tmp/bahman_sales_page_data.cache');
@unlink('/tmp/bahman_sales_page.html');
echo "\nsitefile_FAST=".(is_file($flag)?'ON':'OFF')."\n";
echo "bahman_FAST=".(is_file($bahmanFlag)?'ON':'OFF')."\n";
