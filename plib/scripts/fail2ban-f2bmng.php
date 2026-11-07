<?php
echo "=== search only ===\n";
echo trim((string)shell_exec('grep -R "f2bmng" /usr/local/psa/admin/plib --include="*.php" -l 2>/dev/null | head -30'))."\n";
echo "---\n";
echo trim((string)shell_exec('grep -R "class.*Fail2Ban\|Fail2ban\|fail2Ban" /usr/local/psa/admin/plib --include="*.php" -l 2>/dev/null | head -30'))."\n";
echo "---\n";
$files=trim((string)shell_exec('grep -R "f2bmng" /usr/local/psa/admin/plib --include="*.php" -l 2>/dev/null | head -5'));
foreach (explode("\n", $files) as $f) {
  if ($f==='') continue;
  echo "FILE $f\n";
  echo trim((string)shell_exec('grep -n "f2bmng\|setActive\|isActive\|enable\|restart\|jail" '.escapeshellarg($f).' 2>/dev/null | head -40'))."\n====\n";
}
# Also look at REST API routes
echo "api:\n".trim((string)shell_exec('grep -R "fail2ban" /usr/local/psa/admin/plib/pm/Api --include="*.php" -l 2>/dev/null | head -20'))."\n";
echo "swagger snippet:\n".trim((string)shell_exec('grep -R "fail2ban" /usr/local/psa/admin/htdocs/api 2>/dev/null | head -20'))."\n";
# event handler create as root
echo "list-users:\n".trim((string)shell_exec('plesk bin event_handler --list-users 2>&1'))."\n";
