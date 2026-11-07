<?php
// nginx / apache routing for sitefile
echo "=== apache vhost sitefile ===\n";
echo shell_exec("grep -nE 'Rewrite|Proxy|Alias|DocumentRoot|php|file_manager|Location' /var/www/vhosts/system/sitefile.bahman.ir/conf/httpd.conf /var/www/vhosts/system/sitefile.bahman.ir/conf/vhost.conf /var/www/vhosts/system/sitefile.bahman.ir/conf/vhost_ssl.conf 2>/dev/null | head -80");
echo "\n=== nginx files exist? ===\n";
echo shell_exec("ls -la /var/www/vhosts/system/sitefile.bahman.ir/conf/");
echo "\n=== additional nginx directives ===\n";
echo shell_exec("cat /var/www/vhosts/system/sitefile.bahman.ir/conf/vhost_nginx.conf 2>/dev/null; ls /var/www/vhosts/system/sitefile.bahman.ir/conf/*nginx* 2>/dev/null");
echo shell_exec("plesk bin site --info sitefile.bahman.ir 2>/dev/null | head -40");
// where are media files stored?
echo "\n=== resolve BD460 via meta cache ===\n";
$fm='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
echo shell_exec("ls $fm/cache/meta_* 2>/dev/null | wc -l");
$id='61491e0c-bfc8-11f1-9600-005056b972d8';
echo shell_exec("grep -l '$id' $fm/cache/meta_*.json 2>/dev/null | head -3 | while read f; do echo FILE \$f; cat \$f; echo; done");
echo shell_exec("find /var/www/vhosts/siteapi.bahman.ir -name 'BD460.mp4' 2>/dev/null");
// X-Sendfile module?
echo "\n=== apache modules ===\n";
echo shell_exec("httpd -M 2>/dev/null | grep -iE 'sendfile|xsend|headers|proxy'; apachectl -M 2>/dev/null | grep -iE 'sendfile|xsend'");
echo shell_exec("nginx -V 2>&1 | tr ' ' '\\n' | grep -i send");
