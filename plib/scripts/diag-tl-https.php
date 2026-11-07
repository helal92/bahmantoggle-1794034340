<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; echo ">> ".substr($cmd,0,100)."\n"; echo trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'))."\n\n";}
root('curl -sI --max-time 10 -H "Host: bahman.ir" http://172.20.14.76/ | grep -iE "HTTP/|strict|location|x-tl"');
root('curl -skI --max-time 10 -H "Host: bahman.ir" https://172.20.14.76/ | grep -iE "HTTP/|strict|location|x-tl"');
root('apache2ctl -S 2>&1 | grep -E "tl.bahman|172.20.14.76:443" | head -30');
root('ls -la /etc/apache2/plesk.conf.d/vhosts/tl* /var/www/vhosts/system/tl.bahman.ir/conf/ 2>&1');
root('tail -n 40 /var/log/apache2/tl.bahman.ir-access.log 2>/dev/null; tail -n 20 /var/log/apache2/tl.bahman.ir-error.log 2>/dev/null; ls /var/www/vhosts/system/tl.bahman.ir/logs/ 2>&1; tail -n 30 /var/www/vhosts/system/tl.bahman.ir/logs/*access* 2>/dev/null');
root('grep -R "Strict-Transport\\|Header.*[Hh]STS\\|rewrite.*https" /var/www/vhosts/system/bahman.ir/conf/ /etc/apache2/plesk.conf.d/vhosts/bahman.ir.conf 2>/dev/null | head -30');
root('cat /etc/apache2/plesk.conf.d/vhosts/tl.bahman.ir.conf');
echo "DONE\n";
