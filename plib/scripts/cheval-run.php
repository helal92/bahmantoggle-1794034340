<?php
echo "load ".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "ss:\n".shell_exec("ss -s | head -12");
echo "listeners 443:\n".shell_exec("ss -lntp | grep -E ':443|:8443' | head");
echo "apache top:\n".shell_exec("ps -eo pcpu,rss,args --sort=-pcpu | grep apache2 | grep -v grep | head -10");
echo "sitefile local curl:\n";
echo shell_exec("curl -sk --max-time 5 -o /dev/null -w 'local %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://127.0.0.1/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4'");
echo shell_exec("curl -sk --max-time 5 -o /dev/null -w 'ip76 %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4'");
echo "established to :443 count: ".trim(shell_exec("ss -tn state established '( dport = :443 or sport = :443 )' | wc -l"))."\n";
echo "syn-recv: ".trim(shell_exec("ss -tn state syn-recv | wc -l"))."\n";
echo "time-wait: ".trim(shell_exec("ss -tn state time-wait | wc -l"))."\n";
// nginx status?
echo shell_exec("curl -s --max-time 2 http://127.0.0.1/nginx_status 2>/dev/null | head");
echo "DONE\n";
