<?php
echo "=== FAIL2BAN FIX ".date('c')." ===\n";

$cmds = [
  'journalctl -u fail2ban -n 80 --no-pager 2>&1',
  'tail -80 /var/log/fail2ban.log 2>&1',
  'ls -la /var/log/fail2ban* /var/run/fail2ban 2>&1',
  'cat /etc/fail2ban/jail.d/plesk.conf 2>&1',
  'python3 -c "import configparser;c=configparser.ConfigParser();c.read([\"/etc/fail2ban/jail.conf\",\"/etc/fail2ban/jail.local\"]);print(\"sections\",len(c.sections()))" 2>&1',
  # dry-run config test
  'fail2ban-client -t 2>&1',
  'fail2ban-client -d 2>&1 | tail -60',
];
foreach ($cmds as $c) {
  echo "\n>> $c\n";
  echo trim((string)@shell_exec($c."\n"))."\n";
}

# Try start methods
$starts = [
  'sudo -n systemctl start fail2ban 2>&1',
  'sudo -n /bin/systemctl restart fail2ban 2>&1',
  '/usr/bin/systemctl start fail2ban 2>&1',
  # plesk may have wrapper
  'plesk sbin fail2banmng --help 2>&1 | head -20',
  'ls /usr/local/psa/admin/sbin/*fail* /usr/local/psa/admin/bin/*fail* 2>&1',
  'ls /opt/psa/admin/sbin/*fail* 2>&1',
];
foreach ($starts as $c) {
  echo "\n>> START: $c\n";
  echo trim((string)@shell_exec($c."\n"))."\n";
}
echo "\nactive=".trim((string)@shell_exec('systemctl is-active fail2ban 2>&1'))."\n";
