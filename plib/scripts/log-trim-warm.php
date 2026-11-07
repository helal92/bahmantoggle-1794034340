<?php
@copy('/opt/psa/admin/plib/modules/bahmantoggle/resources/log_trim_warm.sh','/tmp/log_trim_warm.sh');
@chmod('/tmp/log_trim_warm.sh',0755);
@unlink('/tmp/log_trim_warm.log');

// Try direct first
echo "direct:\n".trim((string)shell_exec('/bin/bash /tmp/log_trim_warm.sh 2>&1 | tail -30'))."\n";
if (is_file('/tmp/log_trim_warm.log') && strpos((string)@file_get_contents('/tmp/log_trim_warm.log'),'DONE')!==false) {
  echo "log:\n".file_get_contents('/tmp/log_trim_warm.log');
  echo "direct_ok\nDONE\n";
  exit;
}

// Fallback: event handler as root
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
preg_match_all('/Id\s+(\d+)/',$list,$m);
foreach ($m[1] as $id) {
  // only delete temporary ones we create - delete all phys hosting if any leftover from before
}
$cmd='/bin/bash /tmp/log_trim_warm.sh';
echo "create: ".trim(shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 50 2>&1'))."\n";
echo "trigger: ".trim(shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
for ($i=0;$i<20;$i++) {
  usleep(500000);
  if (is_file('/tmp/log_trim_warm.log') && strpos((string)@file_get_contents('/tmp/log_trim_warm.log'),'DONE')!==false) break;
}
echo "log:\n".@file_get_contents('/tmp/log_trim_warm.log')."\n";
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
preg_match_all('/Id\s+(\d+)/',$list,$m);
foreach ($m[1] as $id) {
  $block='';
  // parse - simpler: delete handlers whose command contains log_trim
}
foreach (preg_split('/\n\n+/',$list) as $block) {
  if (stripos($block,'log_trim_warm')!==false && preg_match('/Id\s+(\d+)/',$block,$mm)) {
    echo "del ".$mm[1]."\n";
    shell_exec('plesk bin event_handler --delete '.escapeshellarg($mm[1]).' 2>&1');
  }
}
echo "df:\n".trim(shell_exec('df -h /var'))."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "DONE\n";
