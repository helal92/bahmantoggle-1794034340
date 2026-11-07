<?php
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "wrappers=".trim(shell_exec('ps -eo cmd | grep "[w]rapper" | wc -l'))."\n";
echo "pool:\n".trim(shell_exec('grep -E "pm\\.(max_children|start|min_spare|max_spare)" /opt/plesk/php/7.4/etc/php-fpm.d/sitefile.bahman.ir.conf 2>/dev/null'))."\n";
echo "bahman_pool:\n".trim(shell_exec('grep -E "pm\\.(max_children|start|min_spare|max_spare)" /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null'))."\n";
echo "fast=".(is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE')?'ON':'OFF')."\n";
echo "hotpath=".(strpos((string)@file_get_contents('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php'),'Hot path')!==false?'yes':'no')."\n";
echo "mpm:\n".trim(shell_exec('grep -E "MaxRequestWorkers|MaxConnectionsPerChild|StartServers" /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null'))."\n";
echo "finish_log_exists=".(is_file('/tmp/speed_finish.log')?'yes':'no')." size=".(is_file('/tmp/speed_finish.log')?filesize('/tmp/speed_finish.log'):0)."\n";
echo "apply_log_tail:\n".trim(shell_exec('tail -40 /tmp/speed_apply.log 2>/dev/null'))."\n";
echo "finish_log:\n".trim(shell_exec('tail -80 /tmp/speed_finish.log 2>/dev/null'))."\n";
foreach (['/','/sales','/templates/bahman/assets/index.css'] as $p) {
  echo trim(shell_exec('curl -sk -o /dev/null -w "'.$p.' ttfb:%{time_starttransfer} code:%{http_code}\n" --max-time 12 -H "Host: bahman.ir" https://172.20.14.76'.$p))."\n";
}
for ($i=0;$i<4;$i++) {
  echo trim(shell_exec('curl -sk -o /dev/null -w "thumb ttfb:%{time_starttransfer} code:%{http_code}\n" --max-time 12 -H "Host: sitefile.bahman.ir" "https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300"'))."\n";
}
