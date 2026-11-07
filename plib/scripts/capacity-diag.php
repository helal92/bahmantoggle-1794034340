<?php
echo "=== CAPACITY ".date('c')." ===\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "nproc=".trim((string)shell_exec('nproc 2>/dev/null'))."\n";
echo "mem:\n".trim((string)shell_exec('free -h 2>/dev/null | head -5'))."\n";
echo "swap:\n".trim((string)shell_exec("swapon --show 2>/dev/null; free -h | grep -i swap"))."\n";
echo "disk:\n".trim((string)shell_exec('df -h / /var/www 2>/dev/null | head -5'))."\n";
echo "cpu model:\n".trim((string)shell_exec("grep -m1 'model name' /proc/cpuinfo"))."\n";
echo "fpm_sitefile=".trim((string)shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim((string)shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "fpm_all=".trim((string)shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool" | wc -l'))."\n";
echo "apache_workers=".trim((string)shell_exec('ps -eo cmd | grep -E "[a]pache2|[h]ttpd" | wc -l'))."\n";
echo "nginx=".trim((string)shell_exec('ps -eo cmd | grep "[n]ginx: worker" | wc -l'))."\n";
echo "top cpu:\n".trim((string)shell_exec('ps -eo pcpu,pmem,rss,cmd --sort=-pcpu | head -15'))."\n";
echo "top mem:\n".trim((string)shell_exec('ps -eo pmem,rss,pcpu,cmd --sort=-rss | head -12'))."\n";
echo "iowait snapshot:\n".trim((string)shell_exec("vmstat 1 3 2>/dev/null | tail -4"))."\n";
echo "fail2ban=".trim((string)shell_exec('systemctl is-active fail2ban 2>&1'))."\n";
echo "fast_flag=".(is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE')?'ON':'OFF')."\n";
echo "home_local:\n".trim((string)shell_exec('curl -sk -o /dev/null -w "ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code}\n" --max-time 20 -H "Host: bahman.ir" https://172.20.14.76/'))."\n";
echo "css_local:\n".trim((string)shell_exec('curl -sk -o /dev/null -w "ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code}\n" --max-time 20 -H "Host: bahman.ir" https://172.20.14.76/templates/bahman/assets/index.css'))."\n";
echo "thumb_local:\n".trim((string)shell_exec('curl -sk -o /dev/null -w "ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code}\n" --max-time 20 -H "Host: sitefile.bahman.ir" "https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300"'))."\n";
# fpm pool configs
echo "sitefile pool:\n".trim((string)shell_exec('grep -E "pm\\.|=|listen" /var/www/vhosts/system/sitefile.bahman.ir/etc/php-fpm.d/www.conf 2>/dev/null | head -30; grep -RE "pm\\.|max_children" /opt/plesk/php/*/etc/php-fpm.d/*sitefile* 2>/dev/null | head -20; ls /var/www/vhosts/system/sitefile.bahman.ir/etc/ 2>/dev/null'))."\n";
echo "bahman pool:\n".trim((string)shell_exec('grep -RE "pm\\.|max_children|pm.max" /var/www/vhosts/system/bahman.ir/etc/php-fpm.conf /var/www/vhosts/system/bahman.ir/etc/php-fpm.d/* 2>/dev/null | head -30'))."\n";
echo "apache mpm:\n".trim((string)shell_exec('apache2ctl -M 2>/dev/null | grep mpm; grep -RE "MaxRequestWorkers|ServerLimit|MaxClients|ThreadsPerChild|StartServers" /etc/apache2/mods-enabled/*mpm* /etc/httpd/conf.modules.d/* 2>/dev/null | head -30'))."\n";
