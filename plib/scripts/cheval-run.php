<?php
echo "before load ".trim(@file_get_contents('/proc/loadavg'))." synrecv=".trim(shell_exec("ss -tn state syn-recv | wc -l"))."\n";

// Find apache/httpd control tools
echo shell_exec("ls /usr/local/psa/admin/bin/*http* /usr/local/psa/admin/sbin/*http* 2>/dev/null");
echo shell_exec("ls /usr/local/psa/admin/bin/*nginx* /usr/local/psa/admin/sbin/*nginx* 2>/dev/null");

// Try graceful restart via plesk
foreach ([
  '/usr/local/psa/admin/sbin/httpd-control restart',
  '/usr/local/psa/admin/bin/httpd_control restart',
  '/usr/local/psa/admin/sbin/httpd_control restart',
  'plesk bin webserver --restart',
  'plesk repair web -y -domains-only -n',
] as $c) {
  echo "TRY $c\n";
  echo shell_exec("$c 2>&1 | head -15");
}

sleep(2);
echo "after load ".trim(@file_get_contents('/proc/loadavg'))." synrecv=".trim(shell_exec("ss -tn state syn-recv | wc -l"))."\n";
echo "apache count ".trim(shell_exec("pgrep -c apache2"))." zombies ".trim(shell_exec("ps -eo stat,args | grep apache2 | grep -c 'Z\\|<defunct>'"))."\n";
echo shell_exec("curl -sk --max-time 8 -o /dev/null -w 'v %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4'");
echo shell_exec("curl -sk --max-time 8 -o /dev/null -w 'home %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/'");
echo "DONE\n";
