<?php
@copy('/opt/psa/admin/plib/modules/bahmantoggle/resources/apache_queue_fix.sh','/tmp/apache_queue_fix.sh');
@chmod('/tmp/apache_queue_fix.sh',0755);
echo "exists=".is_file('/tmp/apache_queue_fix.sh')."\n";
echo "list_before:\n".trim(shell_exec('plesk bin event_handler --list 2>&1'))."\n";

// clean all custom root handlers that look temporary
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/',$list) as $line) {
  if (preg_match('/^\s*(\d+)\s+/',$line,$m) && (stripos($line,'/tmp/')!==false || stripos($line,'apache_queue')!==false || stripos($line,'speed_')!==false || stripos($line,'cheval')!==false || stripos($line,'f2b')!==false)) {
    echo "delete {$m[1]} :: $line\n";
    echo trim(shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1'))."\n";
  }
}

$cmd='/bin/bash /tmp/apache_queue_fix.sh';
$create='plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 90 2>&1';
echo "create_cmd=$create\n";
echo "create:\n".trim(shell_exec($create))."\n";
echo "list_mid:\n".trim(shell_exec('plesk bin event_handler --list 2>&1'))."\n";
echo "trigger:\n".trim(shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
for ($i=0;$i<10;$i++) {
  sleep(1);
  if (is_file('/tmp/apache_queue_fix.log') && filesize('/tmp/apache_queue_fix.log')>0) break;
}
echo "log_size=".(is_file('/tmp/apache_queue_fix.log')?filesize('/tmp/apache_queue_fix.log'):-1)."\n";
echo "log:\n".@file_get_contents('/tmp/apache_queue_fix.log')."\n";
echo "mpm:\n".@file_get_contents('/etc/apache2/mods-enabled/mpm_event.conf')."\n";
echo "somax=".trim(@file_get_contents('/proc/sys/net/core/somaxconn'))." backlog=".trim(@file_get_contents('/proc/sys/net/ipv4/tcp_max_syn_backlog'))."\n";
echo "listen=\n".trim(shell_exec("ss -lnt '( sport = :80 or sport = :443 )'"))."\n";
echo "syn=".trim(shell_exec('ss -ant state syn-recv | wc -l'))." httpd=".trim(shell_exec("ps -eo cmd | grep '[a]pache2' | wc -l"))."\n";

$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/',$list) as $line) {
  if (stripos($line,'apache_queue')!==false && preg_match('/^\s*(\d+)\s+/',$line,$m)) {
    echo trim(shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1'))."\n";
  }
}
echo "DONE\n";
