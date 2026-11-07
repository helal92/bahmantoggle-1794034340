<?php
function sh($c){echo ">> $c\n"; $o=trim((string)shell_exec($c)); echo ($o===""?"(empty)":$o)."\n\n"; return $o;}
$fm='/opt/psa/admin/bin/filemng';
sh('ss -ltnp | grep -E ":80|:443"');
sh('ps -ef | grep -E "nginx|apache|httpd" | grep -v grep | head -30');
sh("$fm root exec / /usr/bin/lsof +L1 2>/dev/null | head -40 --allow-root");
sh("$fm root exec / bash -c 'lsof 2>/dev/null | grep -i deleted | head -40' --allow-root");
sh("$fm root exec / bash -c 'df -h; echo ---; du -xh /var --max-depth=1 2>/dev/null; echo ---; ls -la /proc/*/fd 2>/dev/null | grep deleted | head -20' --allow-root");
// find plesk nginx
sh('ls -la /usr/sbin/nginx /usr/local/psa/admin/sbin/nginx /etc/nginx/nginx.conf 2>&1 | head');
sh('plesk bin nginx -s 2>&1 | head -20');
sh('plesk sbin nginxmng --status 2>&1; /usr/local/psa/admin/sbin/nginxmng --status 2>&1');
echo "DONE\n";
