<?php
echo "flag=".(is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE')?'ON':'OFF')."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "opcache=".trim(shell_exec('plesk bin site --show-php-settings sitefile.bahman.ir 2>/dev/null | grep -i opcache.enable'))."\n";
echo trim(shell_exec('curl -sk -o /dev/null -w "home ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code}\n" --max-time 30 -H "Host: bahman.ir" https://172.20.14.76/'))."\n";
echo trim(shell_exec('curl -skI --max-time 15 -H "Host: sitefile.bahman.ir" "https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300" 2>&1 | grep -iE "HTTP/|Cache-Control|Set-Cookie|Pragma"'))."\n";
echo trim(shell_exec('curl -skI --max-time 15 -H "Host: sitefile.bahman.ir" "https://172.20.14.76/9db74acc-a06f-11f1-9b58-005056b972d8" 2>&1 | grep -iE "HTTP/|Cache-Control|Set-Cookie|Pragma"'))."\n";
