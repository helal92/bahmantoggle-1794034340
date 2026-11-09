<?php
function sh($c){return trim((string)shell_exec($c));}
$raw=sh('curl -si --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/sales?e='.time().'"');
echo "RAW:\n$raw\n---\n";
echo "error_log_tail=\n".sh('tail -n 40 /var/www/vhosts/bahman.ir/logs/error_log 2>/dev/null; tail -n 40 /var/www/vhosts/bahman.ir/logs/proxy_error_log 2>/dev/null; ls /var/www/vhosts/bahman.ir/logs 2>/dev/null | head')."\n";
// also php-fpm/system
echo "syslog=\n".sh('grep -i "bahman\|persian_digits\|Fatal\|index.php" /var/log/php*-fpm.log /var/log/apache2/error.log /var/log/httpd/error_log 2>/dev/null | tail -30')."\n";
echo "DONE\n";
