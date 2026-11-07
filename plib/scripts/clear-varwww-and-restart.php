<?php
function sh($c){echo ">> $c\n"; $o=trim((string)shell_exec($c)); echo ($o===""?"(empty)":$o)."\n\n"; return $o;}
$fm='/opt/psa/admin/bin/filemng';
sh('du -xh /var/www --max-depth=3 2>/dev/null | sort -hr | head -25');
sh('du -xh /var/www/vhosts/system --max-depth=3 2>/dev/null | sort -hr | head -25');
sh('ls -la /var/www/');
sh('mount | grep var');
// find large files on /var excluding vhosts mount
sh("$fm root exec / bash -c 'find /var -xdev -type f -size +100M -printf \"%s %p\\n\" 2>/dev/null | sort -nr | head -30' --allow-root");
// full apache restart to pick up somaxconn and clear queue
sh("$fm root exec / /bin/systemctl restart apache2 --allow-root");
sleep(3);
sh('ss -ltn sport = :80');
sh('cat /proc/loadavg');
sh('curl -s -o /dev/null -w "t1=%{time_starttransfer} s=%{size_download}\\n" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/');
sh('curl -s -o /dev/null -w "t2=%{time_starttransfer} s=%{size_download}\\n" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/');
sh('curl -s -o /dev/null -w "t3=%{time_starttransfer} s=%{size_download}\\n" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/');
sh('df -h /var | tail -1');
echo "DONE\n";
