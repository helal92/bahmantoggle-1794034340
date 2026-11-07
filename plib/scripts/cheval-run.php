<?php
$root='/var/www/vhosts/bahman.ir/httpdocs';
$sitefile='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';

echo "=== hero template/js ===\n";
echo shell_exec("grep -RniE 'hero-media|data-src|preload|autoplay|item_slider|swiper-slide' $root/templates/bahman --include='*.tpl' --include='*.js' --include='*.php' 2>/dev/null | head -80");

echo "\n=== section1 / links render ===\n";
echo shell_exec("grep -RniE 'hero-media|section-1|links' $root/templates/bahman --include='*.tpl' 2>/dev/null | head -40");

echo "\n=== nginx sitefile ===\n";
echo shell_exec("cat /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf 2>/dev/null | head -120");
echo "\n--- vhost_ssl ---\n";
echo shell_exec("grep -nE 'location|sendfile|X-Accel|proxy|alias|root|mp4|Range' /var/www/vhosts/system/sitefile.bahman.ir/conf/vhost_ssl.conf /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf 2>/dev/null | head -80");

echo "\n=== file path on disk for empower ===\n";
echo shell_exec("ls -la $sitefile/.FAST_MODE 2>/dev/null; ls $sitefile/file.php 2>/dev/null");
// quick php one-liner via curl to resolve path would need oneshot; instead find BD460
echo shell_exec("find /var/www/vhosts/siteapi.bahman.ir -name 'BD460.mp4' 2>/dev/null | head -5");
echo shell_exec("du -h \$(find /var/www/vhosts/siteapi.bahman.ir -name 'BD460.mp4' 2>/dev/null | head -1) 2>/dev/null");

echo "\n=== home-product / hero js files ===\n";
echo shell_exec("ls -la $root/templates/bahman/assets/*hero* $root/templates/bahman/assets/*slider* $root/templates/bahman/assets/*video* 2>/dev/null");
