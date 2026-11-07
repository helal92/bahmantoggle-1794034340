<?php
$root='/var/www/vhosts/bahman.ir/httpdocs';
echo "=== files with hero-media ===\n";
echo shell_exec("grep -Rnl 'hero-media' $root/templates/bahman 2>/dev/null");
echo "\n=== assets list ===\n";
echo shell_exec("ls -la $root/templates/bahman/assets/ 2>/dev/null | head -60");
echo "\n=== hero-media context in tpl ===\n";
echo shell_exec("grep -Rn 'hero-media' $root/templates/bahman --include='*.tpl' --include='*.js' --include='*.html' 2>/dev/null | head -40");
echo "\n=== index/home tpl names ===\n";
echo shell_exec("ls $root/templates/bahman/*.tpl 2>/dev/null; ls $root/templates/bahman/header 2>/dev/null; ls $root/templates/bahman/index* 2>/dev/null; find $root/templates/bahman -name '*home*' -o -name '*index*' -o -name '*slider*' 2>/dev/null | head -40");
echo "\n=== nginx location php ===\n";
echo shell_exec("grep -nE 'location|fastcgi|sendfile|internal|X-Accel' /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf /var/www/vhosts/system/sitefile.bahman.ir/conf/vhost.conf /var/www/vhosts/system/sitefile.bahman.ir/conf/vhost_ssl.conf 2>/dev/null | head -100");
echo "\n=== BD460 path ===\n";
echo shell_exec("find /var/www/vhosts/siteapi.bahman.ir -name 'BD460*' 2>/dev/null | head -10");
echo shell_exec("ls /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/ 2>/dev/null | head -30");
