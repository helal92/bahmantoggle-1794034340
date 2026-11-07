<?php
echo "uid=".posix_geteuid()." user=".get_current_user()."\n";
$bins = [
  '/usr/local/psa/admin/sbin/websrvmng --restart',
  '/usr/local/psa/admin/sbin/websrvmng --reconfigure-all',
  'kill -USR2 $(cat /opt/plesk/php/7.4/var/run/php-fpm.pid) 2>&1',
  'kill -USR2 $(cat /opt/plesk/php/8.0/var/run/php-fpm.pid) 2>&1',
  'kill -USR2 $(cat /opt/plesk/php/8.1/var/run/php-fpm.pid) 2>&1',
  'kill -USR2 $(cat /opt/plesk/php/8.2/var/run/php-fpm.pid) 2>&1',
  'kill -USR2 $(cat /opt/plesk/php/8.3/var/run/php-fpm.pid) 2>&1',
  'ls -la /opt/plesk/php/*/var/run/php-fpm.pid 2>&1',
  'ps -eo user,pid,cmd | grep "[p]hp-fpm: master" | head -20',
];
foreach ($bins as $c) {
  echo ">> $c\n";
  echo trim((string)@shell_exec($c.' 2>&1'))."\n";
}
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
