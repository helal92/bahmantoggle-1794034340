#!/bin/bash
exec > /tmp/f2b_fix_run.log 2>&1
set -x
date
id
systemctl status fail2ban --no-pager | head -50 || true
echo '--- client -t ---'
fail2ban-client -t || true
echo '--- journal ---'
journalctl -u fail2ban -n 80 --no-pager || true
echo '--- jail.local ---'
cat /etc/fail2ban/jail.local || true
echo '--- plesk.conf head ---'
head -80 /etc/fail2ban/jail.d/plesk.conf || true

touch /var/log/fail2ban.log
chmod 640 /var/log/fail2ban.log
chown root:adm /var/log/fail2ban.log
mkdir -p /var/run/fail2ban

cp -a /etc/fail2ban/jail.local /etc/fail2ban/jail.local.bak.f2bfix || true
# Fix broken nftables placeholder
sed -i 's/chain="<known\/chain>"/chain="INPUT"/g' /etc/fail2ban/jail.local || true
# If still broken, switch sshd action to iptables
if ! fail2ban-client -t >/tmp/f2b_test1.txt 2>&1; then
  cat /tmp/f2b_test1.txt
  sed -i 's/^action = nftables.*/action = iptables-multiport[name=sshd, port="ssh", protocol=tcp]/' /etc/fail2ban/jail.local || true
  fail2ban-client -t >/tmp/f2b_test2.txt 2>&1 || cat /tmp/f2b_test2.txt
fi

systemctl reset-failed fail2ban || true
systemctl restart fail2ban
sleep 2
systemctl is-active fail2ban | tee /tmp/f2b_status.txt
fail2ban-client status | tee /tmp/f2b_client.txt || true
date
