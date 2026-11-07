<?php
echo "load ".trim(@file_get_contents('/proc/loadavg'))."\n";
echo shell_exec("ps -eo pcpu,pmem,rss,args --sort=-pcpu | head -25");
echo "\nsitefile workers: ".trim(shell_exec("pgrep -cf 'php-fpm: pool sitefile'"))."\n";
echo "bahman workers: ".trim(shell_exec("pgrep -cf 'php-fpm: pool bahman.ir'"))."\n";
// protect: write a marker and verify file.php still has redirect after a minute - for now just verify
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php';
echo "redirect_ok=".(strpos(file_get_contents($sf),'Location: /v/')!==false?'Y':'N')."\n";
// plesk restart php for sitefile domain
echo shell_exec("plesk bin php_handler --list 2>/dev/null | head -5");
echo shell_exec("plesk bin domain --update-php-settings sitefile.bahman.ir 2>&1 | head -5");
// try service restart as psaadm
echo shell_exec("/usr/local/psa/admin/sbin/php-fpm_manager --restart 7.4 2>&1 | head -10");
echo shell_exec("systemctl try-reload-or-restart plesk-php74-fpm 2>&1 | head -5");
sleep(2);
echo "after reload sitefile workers: ".trim(shell_exec("pgrep -cf 'php-fpm: pool sitefile'"))."\n";
echo "load2 ".trim(@file_get_contents('/proc/loadavg'))."\n";
// verify 302 still
echo shell_exec("curl -skI --max-time 8 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/61491e0c-bfc8-11f1-9600-005056b972d8' | head -15");
