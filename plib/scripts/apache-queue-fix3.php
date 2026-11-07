<?php
@unlink('/tmp/apache_queue_fix.log');
copy('/opt/psa/admin/plib/modules/bahmantoggle/resources/apache_queue_fix.sh','/tmp/apache_queue_fix.sh');
chmod('/tmp/apache_queue_fix.sh',0755);

// Delete ALL physical hosting update handlers to avoid stale blockers
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
echo "before:\n$list\n";
foreach (preg_split('/\n/',$list) as $line) {
  if (stripos($line,'Physical hosting updated')!==false) {
    // look ahead? list format is multi-line; parse by Id blocks
  }
}
// parse ids from "Id               N"
preg_match_all('/Id\s+(\d+)/',$list,$m);
foreach ($m[1] as $id) {
  echo "delete $id: ".trim(shell_exec('plesk bin event_handler --delete '.escapeshellarg($id).' 2>&1'))."\n";
}
echo "after_delete:\n".trim(shell_exec('plesk bin event_handler --list 2>&1'))."\n";

$cmd='/bin/bash /tmp/apache_queue_fix.sh';
echo "create: ".trim(shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 50 2>&1'))."\n";
echo "list:\n".trim(shell_exec('plesk bin event_handler --list 2>&1'))."\n";
echo "trigger1: ".trim(shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
// also try subscription update
echo "trigger2: ".trim(shell_exec('plesk bin subscription --update bahman.ir -description "bahman-queue-fix-'.time().'" 2>&1'))."\n";
for ($i=0;$i<20;$i++) {
  usleep(500000);
  if (is_file('/tmp/apache_queue_fix.log') && strpos((string)@file_get_contents('/tmp/apache_queue_fix.log'),'DONE')!==false) break;
}
echo "log:\n".@file_get_contents('/tmp/apache_queue_fix.log')."\n";
echo "mpm:\n".@file_get_contents('/etc/apache2/mods-enabled/mpm_event.conf')."\n";
echo "somax=".trim(@file_get_contents('/proc/sys/net/core/somaxconn'))." backlog=".trim(@file_get_contents('/proc/sys/net/ipv4/tcp_max_syn_backlog'))."\n";
echo "listen=\n".trim(shell_exec("ss -lnt '( sport = :80 or sport = :443 )'"))."\n";
echo "syn=".trim(shell_exec('ss -ant state syn-recv | wc -l'))." httpd=".trim(shell_exec("ps -eo cmd | grep '[a]pache2' | wc -l"))."\n";

// cleanup our handler
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
preg_match_all('/Id\s+(\d+)/',$list,$m);
foreach ($m[1] as $id) {
  shell_exec('plesk bin event_handler --delete '.escapeshellarg($id).' 2>&1');
}
echo "handlers_cleared\nDONE\n";
