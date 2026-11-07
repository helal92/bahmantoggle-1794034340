<?php
function sh($c){echo ">> $c\n"; echo trim((string)shell_exec($c))."\n\n";}
$fm='/opt/psa/admin/bin/filemng';
sh('plesk bin subdomain --help 2>&1 | head -35');
sh('plesk bin site --list 2>&1 | head -40');
sh('apache2ctl -M 2>&1 | grep -iE "proxy|rewrite|headers|remoteip"');
sh("$fm root exec / bash -c 'apache2ctl -M 2>&1 | grep -iE \"proxy|rewrite|headers|remoteip\"; echo ---; ls /etc/apache2/mods-enabled/ | grep -iE \"proxy|remote|header|rewrite\"; echo ---; grep -R \"X-Forwarded-For\\|RemoteIP\\|real_ip\" /etc/apache2/ 2>/dev/null | head -20; echo ---; grep -R \"X-Forwarded-For\\|RemoteIP\" /var/www/vhosts/system/bahman.ir/conf/ 2>/dev/null | head -20' --allow-root");
echo "DONE\n";
