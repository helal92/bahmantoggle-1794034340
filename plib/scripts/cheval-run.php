<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$hdr="$bh/templates/bahman/includes/header_include.tpl";

echo "HOME BODY:\n";
echo shell_exec('curl -sk --max-time 15 -H "Host: bahman.ir" "https://172.20.14.76/?e='.time().'"');
echo "\n\n=== if/if balance ===\n";
$h=file_get_contents($hdr);
echo "open_if=".preg_match_all('/\{if\b/',$h)." close=".preg_match_all('/\{\/if\}/',$h)." else=".preg_match_all('/\{else/',$h)."\n";

// show lines 75-140
$lines=file($hdr);
for($i=74;$i<min(150,count($lines));$i++) echo ($i+1).': '.$lines[$i];

// Restore from git? check backups
echo "\nbackups:\n".shell_exec("ls -la $bh/templates/bahman/includes/header_include.tpl* 2>&1");
echo shell_exec("ls /tmp/header_include* 2>&1");
