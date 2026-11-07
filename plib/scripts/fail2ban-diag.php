<?php
echo "=== FAIL2BAN DIAG ".date('c')." ===\n";
echo "euid=".posix_geteuid()."\n";

$cmds = [
  'which fail2ban-client 2>&1',
  'dpkg -l | grep -i fail2ban 2>&1 | head',
  'rpm -qa | grep -i fail2ban 2>&1 | head',
  'systemctl is-active fail2ban 2>&1; systemctl is-enabled fail2ban 2>&1',
  'systemctl status fail2ban --no-pager 2>&1 | head -40',
  'ls -la /etc/fail2ban 2>&1 | head -20',
  'ls -la /usr/local/psa/var/modules/fail2ban 2>&1 | head',
  'plesk bin component_list 2>&1 | grep -i fail',
  'plesk bin extension --list 2>&1 | grep -i fail',
  'plesk bin fail2ban --info 2>&1 | head -50',
  'plesk bin fail2ban --status 2>&1 | head -50',
  'cat /etc/fail2ban/jail.local 2>&1 | head -80',
  'ls /etc/fail2ban/jail.d 2>&1',
  'fail2ban-client status 2>&1 | head -40',
  'grep -R "enabled" /etc/fail2ban/jail.d 2>/dev/null | head -40',
  # Plesk panel setting store
  'plesk db "SELECT * FROM misc WHERE param LIKE \"%fail2%\" OR param LIKE \"%ban%\" LIMIT 30" 2>&1 | head -40',
  'ls /usr/local/psa/admin/conf/fail2ban* 2>&1',
  'ls /usr/local/psa/var/modules/ 2>&1 | head -40',
];
foreach ($cmds as $c) {
  echo "\n>> $c\n";
  echo trim((string)@shell_exec($c."\n"))."\n";
}
