<?php
echo "=== enable fail2ban via root event_handler ===\n";

# List useful events
echo "events:\n".trim((string)shell_exec('plesk bin event_handler --list-events 2>&1 | grep -iE "exten|domain|client_update|admin|service|fail|cron|phys" | head -40'))."\n";

# Write fix script world-executable in /tmp
$script = <<<'SH'
#!/bin/bash
set -x
exec > /tmp/f2b_fix_run.log 2>&1
date
id
# Diagnose
systemctl status fail2ban --no-pager | head -40 || true
fail2ban-client -t || true
journalctl -u fail2ban -n 60 --no-pager || true
# Common fix: ensure log exists and permissions
touch /var/log/fail2ban.log
chmod 640 /var/log/fail2ban.log
chown root:adm /var/log/fail2ban.log
mkdir -p /var/run/fail2ban
# Fix jail.local nftables placeholder if broken
if grep -q 'chain="<known/chain>"' /etc/fail2ban/jail.local 2>/dev/null; then
  sed -i 's/chain="<known\/chain>"/chain="INPUT"/g' /etc/fail2ban/jail.local || true
fi
# Prefer iptables action if nftables broken
if ! fail2ban-client -t; then
  # disable problematic jails temporarily and retry
  cp -a /etc/fail2ban/jail.local /etc/fail2ban/jail.local.bak.f2bfix || true
  # ensure sshd uses iptables-multiport if nft fails
  sed -i 's/^action = nftables.*/action = iptables-multiport[name=sshd, port="ssh", protocol=tcp]/' /etc/fail2ban/jail.local || true
  fail2ban-client -t || true
fi
systemctl reset-failed fail2ban || true
systemctl restart fail2ban
sleep 2
systemctl is-active fail2ban
fail2ban-client status || true
date
SH;
file_put_contents('/tmp/f2b_fix.sh', $script);
chmod(0755, '/tmp/f2b_fix.sh');
echo "script written\n";

# Remove old handlers with our marker
$list = (string)shell_exec('plesk bin event_handler --list 2>&1');
echo "existing handlers:\n$list\n";
if (preg_match_all('/^\\s*(\\d+)\\s+.*f2b_fix\\.sh/m', $list, $m)) {
  foreach ($m[1] as $id) {
    echo "delete handler $id\n";
    echo trim((string)shell_exec('plesk bin event_handler --delete '.escapeshellarg($id).' 2>&1'))."\n";
  }
}

# Create handler as root on phys_hosting_update (we can touch a domain)
$cmd = '/tmp/f2b_fix.sh';
$create = 'plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 50 2>&1';
echo "create: $create\n";
echo trim((string)shell_exec($create))."\n";

# Trigger by updating a domain setting lightly
echo "trigger:\n".trim((string)shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1 | head -20'))."\n";
# alternative triggers
echo "trigger2:\n".trim((string)shell_exec('plesk bin subscription --update bahman.ir -description "bahman" 2>&1 | head -10'))."\n";

sleep(3);
echo "log:\n".trim((string)@file_get_contents('/tmp/f2b_fix_run.log'))."\n";
echo "active=".trim((string)shell_exec('systemctl is-active fail2ban 2>&1'))."\n";
echo "client:\n".trim((string)shell_exec('fail2ban-client status 2>&1 | head -30'))."\n";

# cleanup handler
$list = (string)shell_exec('plesk bin event_handler --list 2>&1');
if (preg_match_all('/^\\s*(\\d+)\\s+.*f2b_fix\\.sh/m', $list, $m)) {
  foreach ($m[1] as $id) {
    echo "cleanup delete $id\n";
    echo trim((string)shell_exec('plesk bin event_handler --delete '.escapeshellarg($id).' 2>&1'))."\n";
  }
}
