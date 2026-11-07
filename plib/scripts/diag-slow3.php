<?php
function sh($c){return trim((string)shell_exec($c));}
echo "ips=".sh('hostname -I')."\n";
echo "nginx=".sh('ss -ltnp | grep -E \':80|:443\' | head -20')."\n";
echo "--- try IPs ---\n";
foreach (explode(' ', sh('hostname -I')) as $ip) {
  if ($ip==='') continue;
  echo "IP $ip http: ".sh('curl -s -o /tmp/t.html -w "code=%{http_code} ttfb=%{time_starttransfer} size=%{size_download}" --max-time 8 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://'.$ip.'/')." title=".sh('grep -o "<title>[^<]*" /tmp/t.html | head -1')."\n";
}
echo "--- apache vhost listen ---\n";
echo sh('grep -R "VirtualHost\|ServerName bahman" /etc/apache2/sites-enabled/ 2>/dev/null | head -30');
echo "\n".sh('grep -R "ServerName bahman\|listen\|proxy_pass" /etc/nginx/ 2>/dev/null | head -40');
echo "\n--- conn from LB ---\n";
echo sh('ss -tan sport = :80 2>/dev/null | awk \'{print $5}\' | sed "s/\\[::ffff://;s/\\]$//;s/:.*//" | sort | uniq -c | sort -rn | head -15');
echo "\n--- recent access sample ---\n";
echo sh('tail -c 2000 /var/www/vhosts/system/bahman.ir/logs/access_log 2>/dev/null | tr "\\n" "\\n" | tail -8');
echo "\nDONE\n";
