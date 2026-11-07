<?php
function sh($c){return trim(shell_exec($c)."\n");}
echo "=== LOAD/MEM/DISK ===\n";
echo sh('uptime');
echo sh('free -h | head -2');
echo sh('df -h / /var | tail -2');
echo "\n=== TOP CPU ===\n";
echo sh('ps -eo pid,pcpu,pmem,rss,cmd --sort=-pcpu | head -12');
echo "\n=== APACHE/FPM ===\n";
echo sh('ps -C apache2 --no-headers | wc -l');
echo sh('ps -C php-fpm --no-headers | wc -l; ps -C php-fpm8.2 --no-headers 2>/dev/null | wc -l');
echo "\n=== PORT 80 QUEUE (LB->origin) ===\n";
echo sh('ss -ltn sport = :80 | head -5');
echo sh('ss -tan state syn-recv sport = :80 | wc -l');
echo sh('ss -tan state established sport = :80 | wc -l');
echo sh('ss -s | head -8');
echo "\n=== LOG SIZES ===\n";
echo sh('ls -lh /var/www/vhosts/system/bahman.ir/logs/access_log /var/www/vhosts/system/bahman.ir/logs/access_ssl_log /var/www/vhosts/system/sitefile.bahman.ir/logs/access_log 2>&1');
echo "\n=== LOCAL HOME TTFB ===\n";
echo sh('curl -s -o /dev/null -w "local_home=%{http_code} ttfb=%{time_starttransfer}s total=%{time_total}s size=%{size_download}\n" -H "Host: bahman.ir" http://127.0.0.1/');
echo sh('curl -s -o /dev/null -w "local_https=%{http_code} ttfb=%{time_starttransfer}s total=%{time_total}s\n" -k --resolve bahman.ir:443:127.0.0.1 https://bahman.ir/');
echo "DONE\n";
