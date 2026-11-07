<?php
echo "=== speed-apply ===\n";
$res = dirname(__DIR__) . '/resources';
copy($res . '/file.php', '/tmp/file.php.speed');
copy($res . '/speed_apply.sh', '/tmp/speed_apply.sh');
chmod('/tmp/speed_apply.sh', 0755);
echo "files staged\n";

$list = (string) shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'speed_apply') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) {
    shell_exec('plesk bin event_handler --delete ' . escapeshellarg($m[1]) . ' 2>&1');
  }
}
$cmd = '/bin/bash /tmp/speed_apply.sh';
echo trim((string) shell_exec('plesk bin event_handler --create -user root -command ' . escapeshellarg($cmd) . ' -event phys_hosting_update -priority 90 2>&1')) . "\n";
echo trim((string) shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1')) . "\n";
// wait for script
for ($i = 0; $i < 30; $i++) {
  if (is_file('/tmp/speed_apply.log') && strpos((string) @file_get_contents('/tmp/speed_apply.log'), 'DONE') !== false) {
    break;
  }
  usleep(500000);
}
echo (string) @file_get_contents('/tmp/speed_apply.log');

$list = (string) shell_exec('plesk bin event_handler --list 2>&1');
foreach (preg_split('/\n/', $list) as $line) {
  if (strpos($line, 'speed_apply') === false) continue;
  if (preg_match('/\b(\d+)\b/', $line, $m)) {
    shell_exec('plesk bin event_handler --delete ' . escapeshellarg($m[1]) . ' 2>&1');
  }
}
echo "\n=== VERIFY ===\n";
echo "load=" . trim(@file_get_contents('/proc/loadavg')) . "\n";
echo "fpm_sitefile=" . trim((string) shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l')) . "\n";
echo "fpm_bahman=" . trim((string) shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l')) . "\n";
echo "pool=" . trim((string) shell_exec('grep pm.max_children /opt/plesk/php/7.4/etc/php-fpm.d/sitefile.bahman.ir.conf 2>/dev/null')) . "\n";
echo "fast_file=" . (strpos((string) @file_get_contents('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php'), 'Hot path') !== false ? 'yes' : 'no') . "\n";
