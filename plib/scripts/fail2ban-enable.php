<?php
echo "=== fail2ban-enable ===\n";
$src = dirname(__DIR__) . '/resources/f2b_fix.sh';
$dst = '/tmp/f2b_fix.sh';
copy($src, $dst);
chmod($dst, 0755);

$list = (string) shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'f2b_fix') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) {
    shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
  }
}

$cmd = '/bin/bash /tmp/f2b_fix.sh';
echo trim((string) shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 80 2>&1'))."\n";
echo trim((string) shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
sleep(3);
echo (string) @file_get_contents('/tmp/f2b_fix_run.log');
echo "\nactive=".trim((string)@file_get_contents('/tmp/f2b_status.txt'))."\n";
echo "client:\n".(string)@file_get_contents('/tmp/f2b_client.txt')."\n";

$list = (string) shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'f2b_fix') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) {
    shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
  }
}
echo "DONE\n";
