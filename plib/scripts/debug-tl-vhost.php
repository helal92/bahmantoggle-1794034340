<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; echo "====\n$cmd\n"; echo trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'))."\n";}
root('apache2ctl -S 2>&1 | head -80');
root('ls -la /etc/apache2/sites-enabled/ | head -40');
root('curl -sI -H "Host: tl.bahman.ir" http://127.0.0.1/ | head -30');
root('curl -sI -H "Host: tl.bahman.ir" http://172.20.14.76/ | head -30');
root('grep -R "ServerName tl.bahman" /etc/apache2/ /var/www/vhosts/system/*/conf/ 2>/dev/null');
root('plesk sbin httpdmng --reconfigure-all >/tmp/httpdmng.out 2>&1; tail -5 /tmp/httpdmng.out; ls /etc/apache2/sites-enabled/tl* 2>&1');
echo DONE;
