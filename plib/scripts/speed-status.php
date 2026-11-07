<?php
echo "=== FINAL ===\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "sitefile:\n".trim(shell_exec('grep -E "pm\\.(max_children|start|min_spare|max_spare)" /opt/plesk/php/7.4/etc/php-fpm.d/sitefile.bahman.ir.conf'))."\n";
echo "bahman:\n".trim(shell_exec('grep -E "pm\\.(max_children|start|min_spare|max_spare)" /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null'))."\n";
echo "hotpath=".(strpos((string)@file_get_contents('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php'),'Hot path')!==false?'yes':'no')."\n";
echo "fast=".(is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE')?'ON':'OFF')."\n";
echo "finish:\n".substr((string)@file_get_contents('/tmp/speed_finish.log'),-2500)."\n";
echo "bahman_log:\n".substr((string)@file_get_contents('/tmp/speed_bahman.log'),-2000)."\n";
for($i=0;$i<2;$i++) echo trim(shell_exec('curl -sk -o /dev/null -w "home %{time_starttransfer}\n" --max-time 12 -H "Host: bahman.ir" https://172.20.14.76/'))."\n";
echo trim(shell_exec('curl -sk -o /dev/null -w "css %{time_starttransfer}\n" --max-time 10 -H "Host: bahman.ir" https://172.20.14.76/templates/bahman/assets/index.css'))."\n";
echo trim(shell_exec('curl -sk -o /dev/null -w "thumb %{time_starttransfer}\n" --max-time 10 -H "Host: sitefile.bahman.ir" "https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300"'))."\n";
