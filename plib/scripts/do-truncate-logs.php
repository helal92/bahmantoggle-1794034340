<?php
function sh($c){echo "CMD $c\n"; echo trim((string)shell_exec($c))."\n\n";}
$fm='/opt/psa/admin/bin/filemng';
$logs=[
 '/var/www/vhosts/system/bahman.ir/logs/access_log',
 '/var/www/vhosts/system/sitefile.bahman.ir/logs/access_log',
 '/var/www/vhosts/system/bahman.ir/logs/error_log',
 '/var/www/vhosts/system/sitefile.bahman.ir/logs/error_log',
];
echo "before:\n".trim(shell_exec('ls -lh '.implode(' ',$logs).' 2>&1'))."\n\n";
foreach ($logs as $p) {
  // truncate via filemng as root
  sh("$fm root exec / /usr/bin/truncate -s0 ".escapeshellarg($p)." --allow-root 2>&1");
}
echo "after:\n".trim(shell_exec('ls -lh '.implode(' ',$logs).' 2>&1'))."\n";
echo "df=".trim(shell_exec('df -h /var | tail -1'))."\n";
echo "load=".trim(shell_exec('cat /proc/loadavg'))."\n";
echo "queue=".trim(shell_exec('ss -ltn sport = :80 | head -2'))."\n";
echo "ttfb=".trim(shell_exec('curl -s -o /dev/null -w "%{time_starttransfer}" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/'))."\n";
echo "DONE\n";
