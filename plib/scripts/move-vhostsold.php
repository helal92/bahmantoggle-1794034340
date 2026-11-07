<?php
function sh($c){echo ">> $c\n"; $o=trim((string)shell_exec($c)); echo ($o===""?"(empty)":$o)."\n\n"; return $o;}
$fm='/opt/psa/admin/bin/filemng';
// move old leftover tree onto the big vhosts disk to free /var
sh("$fm root exec / bash -c 'if [ -d /var/www/vhosts.old ] && [ ! -e /var/www/vhosts/_vhosts.old.migrated ]; then mv /var/www/vhosts.old /var/www/vhosts/_vhosts.old.migrated && echo MOVED; else ls -la /var/www/ /var/www/vhosts/_vhosts.old.migrated 2>&1 | head; fi' --allow-root");
sh('df -h /var /var/www/vhosts | tail -3');
sh('du -xh /var --max-depth=1 2>/dev/null | sort -hr | head');
sh('cat /proc/loadavg');
sh('ps -eo pcpu,pmem,cmd --sort=-pcpu | head -12');
// top URLs last minute from access log
sh('tail -n 5000 /var/www/vhosts/system/bahman.ir/logs/access_log 2>/dev/null | awk \'{print $7}\' | sed "s/?.*//" | sort | uniq -c | sort -rn | head -20');
sh('tail -n 3000 /var/www/vhosts/system/sitefile.bahman.ir/logs/access_log 2>/dev/null | awk \'{print $7}\' | sed "s/?.*//" | sort | uniq -c | sort -rn | head -15');
for ($i=1;$i<=4;$i++) {
  echo "ttfb$i=".trim(shell_exec('curl -s -o /dev/null -w "%{time_starttransfer}" --max-time 15 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/'))."\n";
}
echo "queue:\n".trim(shell_exec('ss -ltn sport = :80'))."\n";
echo "DONE\n";
