<?php
echo "=== f2bmng quick ===\n";
$f2b='/usr/local/psa/admin/sbin/f2bmng';
echo trim((string)shell_exec('ls -la '.escapeshellarg($f2b)))."\n";
# timeout each call
function run($cmd, $sec=8){
  $full='timeout '.$sec.'s '.$cmd.' 2>&1';
  $out=trim((string)@shell_exec($full));
  echo ">> $cmd\n$out\n----\n";
}
run(escapeshellarg($f2b).' --help', 5);
run(escapeshellarg($f2b), 5);
run(escapeshellarg($f2b).' --status', 8);
run(escapeshellarg($f2b).' status', 8);
run('/usr/local/psa/admin/sbin/wrapper --help', 5);
run('timeout 5 strings '.escapeshellarg($f2b).' | grep -E "enable|status|start|restart|active|jail" | head -40', 6);

# event handler: create root oneshot via a harmless event we can trigger?
echo "users:\n".trim((string)shell_exec('plesk bin event_handler --list-users 2>&1 | head -20'))."\n";
echo "events sample:\n".trim((string)shell_exec('plesk bin event_handler --list-events 2>&1 | head -40'))."\n";

# crontab for root via crontabmng
run('/usr/local/psa/admin/sbin/crontabmng get root', 8);
