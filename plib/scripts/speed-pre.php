<?php
echo trim(shell_exec('grep -R "pm.max_children\|pm.start_servers\|\[sitefile\|pool sitefile" /opt/plesk/php /usr/local/psa /var/www/vhosts/system --include="*.conf" 2>/dev/null | grep -i sitefile | head -40'))."\n";
echo "----\n";
echo trim(shell_exec('find /opt/plesk/php /var/www/vhosts/system -name "*.conf" 2>/dev/null | xargs grep -l "sitefile" 2>/dev/null | head -20'))."\n";
echo "----\n";
echo trim(shell_exec('plesk bin domain --show-php-settings sitefile.bahman.ir 2>&1 | head -80'))."\n";
echo "----\n";
echo trim(shell_exec('ls -la /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/ | head -30'))."\n";
echo trim(shell_exec('find /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager -maxdepth 2 -type d 2>/dev/null | head -30'))."\n";
# how thumbs stored
echo trim(shell_exec('ls /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/files 2>&1 | head; ls /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/thumbs 2>&1 | head; ls /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/cache 2>&1 | head'))."\n";
echo "mpm:\n".trim(shell_exec('cat /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null'))."\n";
echo "php handlers:\n".trim(shell_exec('plesk bin php_handler --list 2>&1 | head -40'))."\n";
