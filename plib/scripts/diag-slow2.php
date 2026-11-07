<?php
function sh($c){return trim((string)shell_exec($c));}
echo "synrecv=".sh('ss -tan state syn-recv sport = :80 2>/dev/null | wc -l')."\n";
echo "estab80=".sh('ss -tan state established sport = :80 2>/dev/null | wc -l')."\n";
echo "timewait=".sh('ss -tan state time-wait 2>/dev/null | wc -l')."\n";
echo "apache_cnt=".sh('pgrep -c apache2')."\n";
echo "fpm_cnt=".sh('pgrep -c php-fpm || true')."\n";
echo "load=".sh('cat /proc/loadavg')."\n";
echo "--- local body ---\n";
echo sh('curl -s -H "Host: bahman.ir" http://127.0.0.1/ | head -c 400')."\n";
echo "--- timing ---\n";
echo sh('curl -s -o /tmp/h.html -w "code=%{http_code} ttfb=%{time_starttransfer} size=%{size_download}\n" -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://127.0.0.1/');
echo "head=".sh('head -c 200 /tmp/h.html')."\n";
echo "--- who hits :80 ---\n";
echo sh('ss -tan sport = :80 2>/dev/null | awk \'{print $5}\' | cut -d: -f1 | sort | uniq -c | sort -rn | head -10');
echo "\nDONE\n";
