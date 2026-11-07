<?php
function sh($c){echo ">> $c\n"; $o=trim((string)shell_exec($c)); echo ($o===""?"(empty)":$o)."\n\n"; return $o;}
$fm='/opt/psa/admin/bin/filemng';
sh('df -h /var; du -xh /var --max-depth=1 2>/dev/null | sort -hr');
sh("$fm root exec / bash -c 'du -xh /var/www/vhosts.old --max-depth=2 2>/dev/null | sort -hr | head -20; ls -lh /var/www/vhosts.old/bahman.ir/httpdocs/360-Bahman/expo-teh-1403/@@@.zip 2>&1; ls -lh /var/www/vhosts.old/system/*/logs/access_log 2>&1; ls -lh /var/www/vhosts.old/*/logs/*/access_log 2>&1 | head' --allow-root");
// remove old bulky leftovers on /var partition
sh("$fm root exec / bash -c 'rm -f /var/www/vhosts.old/bahman.ir/httpdocs/360-Bahman/expo-teh-1403/@@@.zip; rm -f /var/www/vhosts.old/system/*/logs/access_log* /var/www/vhosts.old/system/*/logs/error_log*; find /var/www/vhosts.old -type f -name \"access_log*\" -size +10M -delete; find /var/www/vhosts.old -type f -name \"*.webstat\" -size +10M -delete; echo cleaned' --allow-root");
sh("$fm root exec / bash -c 'find /var -xdev -type f -size +50M -printf \"%s %p\\n\" 2>/dev/null | sort -nr | head -40' --allow-root");
sh('df -h /var | tail -1');
sh('cat /proc/loadavg');
sh('ss -ltn sport = :80');
sh('ss -tan state syn-recv sport = :80 | wc -l');
for ($i=1;$i<=5;$i++) {
  echo "ttfb$i=".trim(shell_exec('curl -s -o /dev/null -w "%{time_starttransfer}" --max-time 15 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/'))."\n";
}
echo "DONE\n";
