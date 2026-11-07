<?php
echo "NGINX.CONF:\n";
echo file_get_contents('/var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf');
echo "\n\nVHOST_SSL:\n";
echo file_get_contents('/var/www/vhosts/system/sitefile.bahman.ir/conf/vhost_ssl.conf');
echo "\n\nVHOST:\n";
echo file_get_contents('/var/www/vhosts/system/sitefile.bahman.ir/conf/vhost.conf');
echo "\n\nHTTPD rewrite snippet:\n";
echo shell_exec("grep -nE 'RewriteRule|file\\.php|ProxyPass|DocumentRoot|Alias' /var/www/vhosts/system/sitefile.bahman.ir/conf/httpd.conf | head -40");
echo "\nuploads path:\n";
echo shell_exec("ls -la /var/www/vhosts/siteapi.bahman.ir/httpdocs/uploads/app_1/bp_companies_1/1791100281-38666780.mp4");
echo shell_exec("ls -la /var/www/vhosts/sitefile.bahman.ir/httpdocs 2>/dev/null | head");
echo shell_exec("readlink -f /var/www/vhosts/sitefile.bahman.ir/httpdocs; ls /var/www/vhosts/sitefile.bahman.ir/");
// additional directives from plesk db?
echo shell_exec("mysql -uadmin -p$(cat /etc/psa/.psa.shadow) -N -e \"SELECT value FROM psa.dom_param WHERE dom_id=7 AND param LIKE '%nginx%'\" 2>/dev/null | head");
echo shell_exec("plesk bin site --show-web-server-settings sitefile.bahman.ir 2>/dev/null | head -60");
