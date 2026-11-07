<?php
echo "=== TIME ".date('c')." ===\n";
echo "load: ".@file_get_contents('/proc/loadavg')."\n";
echo "mem:\n".shell_exec("free -m | head -3");
echo "\nFPM pools:\n";
echo shell_exec("ps -eo pid,pcpu,pmem,rss,args | grep 'php-fpm: pool' | grep -v grep | awk '{print \$NF}' | sort | uniq -c | sort -rn");
echo "\nbahman.ir workers detail:\n";
echo shell_exec("ps -eo pid,pcpu,pmem,rss,stat,args | grep 'php-fpm: pool bahman.ir' | grep -v grep | head -40");
echo "\nsitefile/siteapi workers:\n";
echo shell_exec("ps -eo pid,pcpu,pmem,rss,stat,args | grep -E 'php-fpm: pool (sitefile|siteapi|bmcars)' | grep -v grep | head -40");
echo "\nApache busy:\n";
echo shell_exec("ps -eo pid,pcpu,stat,args | grep -E 'httpd|apache' | grep -v grep | wc -l");
echo shell_exec("curl -sk --max-time 2 http://127.0.0.1/server-status?auto 2>/dev/null | head -20");

// FPM conf bahman
echo "\nbahman FPM:\n";
echo shell_exec("grep -E 'pm\\.|listen' /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null | head -20");

// quick timings
foreach ([
  ['bahman.ir','https://172.20.14.76/'],
  ['bahman css','https://172.20.14.76/templates/bahman/assets/index.css'],
  ['bahman nav.css','https://172.20.14.76/templates/bahman/assets/navigation.css'],
  ['sitefile video','https://172.20.14.76/61491e0c-bfc8-11f1-9600-005056b972d8'],
  ['sitefile thumb','https://172.20.14.76/thumb/85f7b2b5-d753-11f1-b563-005056b972d8/960'],
] as $t) {
  [$name,$url]=$t;
  $host = strpos($name,'sitefile')===0 ? 'sitefile.bahman.ir' : 'bahman.ir';
  $cmd = "curl -sk --max-time 15 -o /dev/null -w '%{http_code} ttfb=%{time_starttransfer} total=%{time_total} size=%{size_download}' -H 'Host: $host' '$url'";
  echo "$name: ".trim((string)shell_exec($cmd))."\n";
}

// concurrent css test
echo "\n10 parallel CSS:\n";
$cmds=[];
for($i=0;$i<10;$i++){
  $cmds[]="curl -sk --max-time 20 -o /dev/null -w '%{http_code}:%{time_total} ' -H 'Host: bahman.ir' 'https://172.20.14.76/templates/bahman/assets/navigation.css?n=$i'";
}
echo shell_exec(implode(' & ',$cmds).' & wait');
echo "\n";

// open connections
echo "ss syn/est:\n";
echo shell_exec("ss -s 2>/dev/null | head -15");
echo shell_exec("ss -tn state established '( dport = :443 or sport = :443 )' 2>/dev/null | wc -l");
echo shell_exec("ss -tn state time-wait 2>/dev/null | wc -l");

// recent apache/nginx errors
echo "\nerrors:\n";
echo shell_exec("tail -30 /var/www/vhosts/bahman.ir/logs/error_log 2>/dev/null");
echo shell_exec("tail -20 /var/www/vhosts/bahman.ir/logs/proxy_error_log 2>/dev/null");
echo shell_exec("tail -20 /var/log/httpd/error_log 2>/dev/null");
