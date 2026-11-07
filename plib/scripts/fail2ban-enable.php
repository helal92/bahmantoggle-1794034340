<?php
echo "=== fail2ban-enable ===\n";
$src = dirname(__DIR__) . '/resources/f2b_fix.sh';
$dst = '/tmp/f2b_fix.sh';
if (!copy($src, $dst)) {
  echo "copy failed\n";
  exit(1);
}
chmod(0755, $dst);
echo "script=$dst size=".filesize($dst)."\n";

// delete previous handlers mentioning f2b_fix
$list = (string) shell_exec('plesk bin event_handler --list 2>&1');
echo "handlers_before:\n$list\n";
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'f2b_fix') === false) {
    continue;
  }
  if (preg_match('/\b(\d+)\b/', $line, $m)) {
    echo "del ".$m[1]."\n";
    echo trim((string) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1'))."\n";
  }
}

$cmd = '/bin/bash /tmp/f2b_fix.sh';
$create = 'plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event admin_preferences_update -priority 80';
echo "create_cmd=$create\n";
echo trim((string) shell_exec($create.' 2>&1'))."\n";

// Trigger admin preferences update (no-op-ish)
echo "trigger:\n".trim((string) shell_exec('plesk bin admin --info 2>&1 | head -5'))."\n";
// Try preferences update
echo trim((string) shell_exec('plesk bin admin --update -company "Bahman" 2>&1 | head -20'))."\n";
echo trim((string) shell_exec('plesk bin server_pref -u -hostname "$(hostname -f)" 2>&1 | head -20'))."\n";

sleep(4);
echo "--- log ---\n";
echo (string) @file_get_contents('/tmp/f2b_fix_run.log');
echo "\n--- status files ---\n";
echo "status=".trim((string)@file_get_contents('/tmp/f2b_status.txt'))."\n";
echo "active=".trim((string)shell_exec('systemctl is-active fail2ban 2>&1'))."\n";
echo "client_head=\n".trim((string)@file_get_contents('/tmp/f2b_client.txt'))."\n";

// cleanup handlers
$list = (string) shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'f2b_fix') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) {
    echo "cleanup ".$m[1]."\n";
    echo trim((string) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1'))."\n";
  }
}
echo "DONE\n";
