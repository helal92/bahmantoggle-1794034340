<?php
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources/apache_queue_fix.sh';
@copy($res,'/tmp/apache_queue_fix.sh');
@chmod('/tmp/apache_queue_fix.sh',0755);
echo "script=". (is_file('/tmp/apache_queue_fix.sh')?'Y':'N')."\n";

$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach (explode("\n",$list) as $line) {
  if (stripos($line,'apache_queue_fix')!==false || stripos($line,'speed_apply')!==false || stripos($line,'speed_bahman')!==false || stripos($line,'cheval_work')!==false) {
    if (preg_match('/\b(\d+)\b/',$line,$m)) {
      echo "del ".$m[1]."\n";
      shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
    }
  }
}
$cmd='/bin/bash /tmp/apache_queue_fix.sh';
echo trim((string)shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 90 2>&1'))."\n";
echo trim((string)shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
sleep(4);
echo "log:\n".@file_get_contents('/tmp/apache_queue_fix.log')."\n";
echo "listen=\n".trim(shell_exec("ss -lnt '( sport = :80 or sport = :443 )'"))."\n";
echo "syn=".trim(shell_exec("ss -ant state syn-recv 2>/dev/null | wc -l"))."\n";
echo "httpd=".trim(shell_exec("ps -eo cmd | grep '[a]pache2' | wc -l"))."\n";
echo "somax=".trim(@file_get_contents('/proc/sys/net/core/somaxconn'))." backlog=".trim(@file_get_contents('/proc/sys/net/ipv4/tcp_max_syn_backlog'))."\n";

// cleanup handler
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach (explode("\n",$list) as $line) {
  if (stripos($line,'apache_queue_fix')!==false && preg_match('/\b(\d+)\b/',$line,$m)) {
    shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
  }
}
echo "DONE\n";
