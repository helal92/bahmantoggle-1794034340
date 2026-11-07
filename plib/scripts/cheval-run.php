<?php
echo shell_exec('sudo cat /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf 2>&1 | head -120');
echo "\n====\n";
echo shell_exec('sudo grep -nE "RewriteRule|file\\.php|DocumentRoot|Alias|Proxy" /var/www/vhosts/system/sitefile.bahman.ir/conf/httpd.conf 2>&1 | head -50');
echo "\n==== additional nginx via plesk ====\n";
echo shell_exec('sudo plesk bin site --show-web-server-settings sitefile.bahman.ir 2>&1 | head -80');
echo "\n==== domain docroot ====\n";
echo shell_exec('sudo plesk db -Ne "SELECT www_root FROM hosting WHERE dom_id=7" 2>&1');
echo shell_exec('sudo ls -la /var/www/vhosts/siteapi.bahman.ir/httpdocs/ | head -20');
echo shell_exec('sudo readlink -f /var/www/vhosts/system/sitefile.bahman.ir/conf/../..');
// sitefile is alias domain?
echo shell_exec('sudo plesk db -Ne "SELECT d.name, h.www_root FROM domains d JOIN hosting h ON h.dom_id=d.id WHERE d.name LIKE \"%sitefile%\" OR d.name LIKE \"%siteapi%\"" 2>&1');
echo shell_exec('sudo plesk db -Ne "SELECT name, webspace_id FROM domains WHERE name LIKE \"%bahman%\"" 2>&1');
