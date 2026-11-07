<?php
echo "=== wrapper ===\n";
foreach (['/usr/local/psa/admin/sbin/wrapper','/opt/psa/admin/sbin/wrapper','/usr/local/psa/admin/sbin/mod_wrapper'] as $w) {
  echo "ls $w: ".trim((string)shell_exec('ls -la '.escapeshellarg($w).' 2>&1'))."\n";
}
$w='/usr/local/psa/admin/sbin/wrapper';
# try invocations
$tries=[
  "$w",
  "$w --help",
  "$w help",
  "$w f2bmng",
  "$w f2bmng --help",
  "$w -- f2bmng --help",
  "$w /usr/local/psa/admin/sbin/f2bmng --help",
];
foreach ($tries as $cmd) {
  $out=trim((string)@shell_exec('timeout 6 '.$cmd.' 2>&1'));
  echo "TRY: $cmd\n$out\n----\n";
}

# How does plesk invoke f2bmng? search php code
echo "refs:\n".trim((string)shell_exec('grep -R "f2bmng" /usr/local/psa/admin/plib --include="*.php" -l 2>/dev/null | head -20'))."\n";
echo "refs2:\n".trim((string)shell_exec('grep -R "Fail2Ban\\|fail2ban\\|f2bmng" /usr/local/psa/admin/plib/modules --include="*.php" -l 2>/dev/null | head -20'))."\n";
echo "panel ui:\n".trim((string)shell_exec('ls /usr/local/psa/admin/htdocs/fail2ban* 2>/dev/null; ls /usr/local/psa/admin/htdocs/*/fail2ban* 2>/dev/null | head'))."\n";
