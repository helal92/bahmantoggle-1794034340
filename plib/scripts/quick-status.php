<?php
echo "load=".trim(shell_exec('cat /proc/loadavg'))."\n";
echo "var=".trim(shell_exec('df -h /var | tail -1'))."\n";
echo "queue=".trim(shell_exec('ss -ltn sport = :80 | tail -1'))."\n";
echo "ttfb=".trim(shell_exec('curl -s -o /dev/null -w "%{time_starttransfer}" --max-time 10 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/'))."\n";
