<?php
echo "nginx_control help:\n".shell_exec('/usr/local/psa/admin/bin/nginx_control --help 2>&1 | head -20');
echo "httpdmng help:\n".shell_exec('/usr/local/psa/admin/bin/httpdmng --help 2>&1 | head -30');

echo "\nTRY nginx reload\n";
echo shell_exec('/usr/local/psa/admin/bin/nginx_control reload 2>&1 | head -20');
echo "TRY nginx restart\n";
echo shell_exec('/usr/local/psa/admin/bin/nginx_control restart 2>&1 | head -20');
echo "TRY apache via httpdmng\n";
echo shell_exec('/usr/local/psa/admin/bin/httpdmng --restart 2>&1 | head -20');
echo shell_exec('plesk bin service --restart apache 2>&1 | head -10');
echo shell_exec('plesk bin service --list 2>&1 | head -30');

echo "\nSYN sources top:\n";
echo shell_exec("ss -tn state syn-recv | awk '{print \$4}' | sed 's/\\[::ffff://;s/\\]//' | cut -d: -f1 | sort | uniq -c | sort -rn | head -20");

echo "\nestablished peers top:\n";
echo shell_exec("ss -tn state established '( dport = :443 or sport = :443 )' | awk 'NR>1{print \$4}' | sed 's/\\[::ffff://;s/\\]//' | cut -d: -f1 | sort | uniq -c | sort -rn | head -15");

sleep(1);
echo "synrecv now=".trim(shell_exec("ss -tn state syn-recv | wc -l"))." load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo shell_exec("curl -sk --max-time 8 -o /dev/null -w 'v %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4'");
echo "DONE\n";
