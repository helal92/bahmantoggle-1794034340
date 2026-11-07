<?php
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$id='61491e0c-bfc8-11f1-9600-005056b972d8';

echo "=== video headers no follow ===\n";
echo shell_exec("curl -skI --max-time 15 -H 'Host: sitefile.bahman.ir' -H 'Referer: https://bahman.ir/' 'https://172.20.14.76/$id'");
echo "\n=== with Range ===\n";
echo shell_exec("curl -skI --max-time 15 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-' 'https://172.20.14.76/$id'");
echo "\n=== static /v ===\n";
echo shell_exec("curl -skI --max-time 15 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/$id.mp4'");

echo "\nfile.php has Location redirect? ";
echo (strpos(file_get_contents("$sf/file.php"), 'Location: /v/') !== false) ? "YES\n" : "NO\n";
echo "symlink:\n";
echo shell_exec("ls -la $sf/v/$id.mp4 2>&1");

// what are sitefile workers doing - strace one briefly?
$pids=trim((string)shell_exec("pgrep -f 'php-fpm: pool sitefile' | head -3"));
echo "sitefile pids: $pids\n";
echo "open files sample:\n";
foreach (preg_split('/\s+/',$pids) as $pid) {
  if (!$pid) continue;
  echo "PID $pid\n";
  echo shell_exec("ls -l /proc/$pid/fd 2>/dev/null | awk '{print \$NF}' | sed 's/.*\\///' | sort | uniq -c | sort -rn | head -15");
  echo shell_exec("tr '\\0' ' ' </proc/$pid/cmdline; echo");
  echo shell_exec("timeout 1 strace -p $pid -e trace=openat,read,write,sendfile 2>&1 | head -20");
}

// thumb meta/hotpath
echo "\nthumb timing parallel 8:\n";
$cmds=[];
$thumbs=['85f7b2b5-d753-11f1-b563-005056b972d8','bd6c6851-d6b9-11f1-b563-005056b972d8','d086df56-d4e5-11f1-b563-005056b972d8','5e7b9afa-d1c6-11f1-b563-005056b972d8'];
foreach ($thumbs as $i=>$t) {
  $cmds[]="curl -sk --max-time 20 -o /dev/null -w '$t %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/thumb/$t/960'";
}
echo shell_exec(implode(' & ',$cmds).' & wait');

// FPM sitefile conf
echo "\nsitefile FPM:\n";
echo shell_exec("grep -E 'pm\\.|request_terminate' /opt/plesk/php/7.4/etc/php-fpm.d/sitefile.bahman.ir.conf");

// apache mpm limits
echo "\nmpm:\n";
echo shell_exec("grep -rE 'MaxRequestWorkers|ServerLimit|ThreadsPerChild|MaxClients' /etc/httpd/conf.modules.d /etc/httpd/conf.d 2>/dev/null | head -20");
