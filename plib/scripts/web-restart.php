<?php
echo "euid=".posix_geteuid()."\n";
// reload FPM masters by PID
foreach ([1789, 1790] as $pid) {
  // refresh pid from ps
}
$out = trim((string)shell_exec('ps -eo pid,cmd | grep "[p]hp-fpm: master"'));
echo "masters=\n$out\n";
preg_match_all('/^\s*(\d+)\s+php-fpm: master/m', $out, $m);
foreach ($m[1] as $pid) {
  $r = posix_kill((int)$pid, SIGUSR2);
  echo "USR2 pid=$pid ok=".($r?'1':'0')." errno=".posix_get_last_error()."\n";
}
// also SIGUSR2 via kill binary
echo trim((string)shell_exec('ps -eo pid,cmd | grep "[p]hp-fpm: master" | awk \'{print $1}\' | while read p; do kill -USR2 $p && echo killed:$p; done 2>&1'))."\n";

sleep(1);
echo "fpm_sitefile_after=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman_after=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";

// home timing
for ($i=0;$i<3;$i++) {
  echo trim((string)shell_exec('curl -sk -o /dev/null -w "home ttfb:%{time_starttransfer} code:%{http_code}\n" --max-time 20 -H "Host: bahman.ir" https://172.20.14.76/'));
  echo "\n";
}
