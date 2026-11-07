<?php
echo "=== f2bmng ===\n";
$f2b='/usr/local/psa/admin/sbin/f2bmng';
echo "ls: ".trim((string)shell_exec('ls -la '.escapeshellarg($f2b).' 2>&1'))."\n";
echo "help:\n".trim((string)shell_exec(escapeshellarg($f2b).' --help 2>&1'))."\n";
echo "help2:\n".trim((string)shell_exec(escapeshellarg($f2b).' -h 2>&1'))."\n";
echo "noargs:\n".trim((string)shell_exec(escapeshellarg($f2b).' 2>&1 | head -80'))."\n";

# wrapper approach
$w='/usr/local/psa/admin/sbin/wrapper';
echo "wrapper ls: ".trim((string)shell_exec('ls -la '.escapeshellarg($w)))."\n";
echo "wrapper help:\n".trim((string)shell_exec(escapeshellarg($w).' 2>&1 | head -40'))."\n";

# try common f2bmng commands
foreach ([
  '--status','status','--info','info','--enable','enable','--start','start',
  '--restart','restart','--reconfigure','reconfigure','--apply','apply',
  'get-status','is-active','--get-settings','--set-active','true',
] as $arg) {
  $out=trim((string)@shell_exec(escapeshellarg($f2b).' '.escapeshellarg($arg).' 2>&1'));
  if ($out !== '') echo "ARG[$arg]:\n$out\n----\n";
}

# crontabmng can run as root sometimes - try adding oneshot
echo "crontabmng:\n".trim((string)shell_exec('/usr/local/psa/admin/sbin/crontabmng --help 2>&1 | head -30'))."\n";
