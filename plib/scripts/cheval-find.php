<?php
copy(dirname(__DIR__).'/resources/cheval_disable.sh', '/tmp/cheval_disable.sh');
copy(dirname(__DIR__).'/resources/cheval_disable.php', '/tmp/cheval_disable_root.php');
chmod('/tmp/cheval_disable.sh', 0755);
@unlink('/tmp/cheval_disable.log');
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'cheval_disable') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
}
$cmd = '/usr/bin/php /tmp/cheval_disable_root.php; /bin/bash /tmp/cheval_disable.sh';
shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 90 2>&1');
echo trim((string)shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
for ($i=0;$i<40;$i++) {
  clearstatcache(true, '/tmp/cheval_disable.log');
  $log = (string)@file_get_contents('/tmp/cheval_disable.log');
  if (strpos($log, 'DONE_FIND') !== false || strpos($log, 'connected') !== false || strlen($log) > 200) break;
  usleep(400000);
}
echo (string)@file_get_contents('/tmp/cheval_disable.log');
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'cheval_disable') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
}
