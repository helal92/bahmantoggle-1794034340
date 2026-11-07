<?php
echo "=== PRE ===\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "wrappers=".trim(shell_exec('ps -eo pid,cmd | grep "[w]rapper" | wc -l'))."\n";
echo "fast=".(is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE')?'ON':'OFF')."\n";
echo "sitefile php-fpm confs:\n";
echo trim(shell_exec('ls -la /var/www/vhosts/system/sitefile.bahman.ir/etc/ 2>&1'))."\n";
echo trim(shell_exec('find /var/www/vhosts/system/sitefile.bahman.ir /opt/plesk/php -name "*fpm*" 2>/dev/null | head -40'))."\n";
echo "pool grep:\n".trim(shell_exec('grep -R "pm.max_children\|pm.start\|pm.min\|pm.max_spare\|\[sitefile\|\[bahman" /var/www/vhosts/system/sitefile.bahman.ir/etc /var/www/vhosts/system/bahman.ir/etc 2>/dev/null | head -60'))."\n";
echo "htaccess sitefile:\n".trim(shell_exec('ls -la /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.htaccess /var/www/vhosts/sitefile.bahman.ir/httpdocs/.htaccess 2>&1; head -80 /var/www/vhosts/sitefile.bahman.ir/httpdocs/.htaccess 2>&1; head -40 /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.htaccess 2>&1'))."\n";
echo "vhost sitefile:\n".trim(shell_exec('ls /var/www/vhosts/system/sitefile.bahman.ir/conf/ 2>&1; head -5 /var/www/vhosts/system/sitefile.bahman.ir/conf/httpd.conf 2>&1'))."\n";
echo "file.php head:\n".trim(shell_exec('head -5 /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php 2>&1; wc -l /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php 2>&1'))."\n";
echo "docroots:\n".trim(shell_exec('plesk bin domain --info sitefile.bahman.ir 2>&1 | grep -iE "root|FTP|Login|PHP" | head -20'))."\n";
