<?php
function sh($c){echo ">> $c\n"; echo trim((string)shell_exec($c))."\n\n";}
$fm='/opt/psa/admin/bin/filemng';
echo "deleted-open:\n".trim(shell_exec('lsof +L1 2>/dev/null | grep -E "access_log|deleted" | head -20'))."\n\n";
echo "big /var:\n".trim(shell_exec('du -xh /var --max-depth=2 2>/dev/null | sort -hr | head -20'))."\n\n";
// reload nginx+apache so log fds release; also raise backlog if possible
sh("$fm root exec / /usr/sbin/nginx -s reload --allow-root 2>&1");
sh("$fm root exec / /usr/sbin/apache2ctl graceful --allow-root 2>&1");
sh("$fm root exec / /bin/systemctl reload apache2 --allow-root 2>&1");
sh("$fm root exec / /bin/systemctl reload nginx --allow-root 2>&1");
// try increase somaxconn / backlog
sh("$fm root exec / /sbin/sysctl -w net.core.somaxconn=4096 --allow-root 2>&1");
sh("$fm root exec / /sbin/sysctl -w net.ipv4.tcp_max_syn_backlog=4096 --allow-root 2>&1");
sleep(2);
echo "df=".trim(shell_exec('df -h /var | tail -1'))."\n";
echo "load=".trim(shell_exec('cat /proc/loadavg'))."\n";
echo "queue=\n".trim(shell_exec('ss -ltn sport = :80'))."\n";
echo "syn=".trim(shell_exec('ss -tan state syn-recv sport = :80 | wc -l'))."\n";
echo "ttfb1=".trim(shell_exec('curl -s -o /dev/null -w "%{time_starttransfer}" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/'))."\n";
echo "ttfb2=".trim(shell_exec('curl -s -o /dev/null -w "%{time_starttransfer}" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/'))."\n";
echo "log sizes:\n".trim(shell_exec('ls -lh /var/www/vhosts/system/bahman.ir/logs/access_log /var/www/vhosts/system/sitefile.bahman.ir/logs/access_log'))."\n";
echo "DONE\n";
