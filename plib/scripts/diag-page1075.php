<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; echo trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'))."\n\n";}
function sh($cmd){echo trim((string)shell_exec($cmd))."\n\n";}

echo "=== curl origin ===\n";
sh('curl -s -o /tmp/p1075.body -w "code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer}\n" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/page/1075');
sh('head -c 400 /tmp/p1075.body | tr "\\n" " "; echo');
sh('grep -oE "<title>[^<]+" /tmp/p1075.body | head -3');

echo "=== other pages ===\n";
foreach ([1075, 1, 100, 4200, 559] as $id) {
  $c = trim(shell_exec('curl -s -o /tmp/px.body -w "%{http_code}" --max-time 15 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/page/'.$id));
  $t = trim(shell_exec('grep -oE "<title>[^<]+" /tmp/px.body | head -1'));
  echo "page/$id => $c $t\n";
}

echo "\n=== apache/php error ===\n";
sh('tail -n 30 /var/www/vhosts/system/bahman.ir/logs/error_log 2>/dev/null | tail -20');
sh('tail -n 20 /var/log/apache2/error.log 2>/dev/null');

echo "=== rewrite / routing check via php cli include ===\n";
// quick DB check if content 1075 exists
root('cd /var/www/vhosts/bahman.ir/httpdocs && php -r \'$c=@include "libs/myconf.php"; echo "skip\n";\' 2>&1 | tail -5');

echo "DONE\n";
