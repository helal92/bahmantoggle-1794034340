#!/bin/bash
exec > /tmp/f2b_fix_run.log 2>&1
set -x
date
id

cp -a /etc/fail2ban/jail.local /etc/fail2ban/jail.local.bak.$(date +%s)

# Write a clean working jail.local for Plesk + Ubuntu 24.04
cat > /etc/fail2ban/jail.local <<'EOF'
[DEFAULT]
ignoreip = 127.0.0.1/8 ::1 185.153.209.171 78.157.48.187 10.18.8.2 172.20.14.76 10.32.17.200 10.32.17.201
bantime = 600
maxretry = 5
findtime = 600
destemail = admin@bmcars.online
banaction = iptables-multiport
backend = auto

[sshd]
enabled = true
filter = sshd
port = ssh
logpath = /var/log/auth.log
backend = systemd
bantime = 3600
banaction = iptables-multiport

[recidive]
enabled = true

[plesk-proftpd]
enabled = true

[plesk-postfix]
enabled = true

[plesk-dovecot]
enabled = true

[plesk-roundcube]
enabled = true

[plesk-apache]
enabled = true

[plesk-apache-badbot]
enabled = true

[plesk-panel]
enabled = true

[plesk-modsecurity]
enabled = true

[plesk-wordpress]
enabled = true
EOF

touch /var/log/fail2ban.log
chmod 640 /var/log/fail2ban.log
chown root:adm /var/log/fail2ban.log
mkdir -p /var/run/fail2ban

echo '--- test ---'
fail2ban-client -t
echo "test_exit=$?"

systemctl reset-failed fail2ban || true
systemctl restart fail2ban
sleep 2
systemctl is-active fail2ban | tee /tmp/f2b_status.txt
fail2ban-client status | tee /tmp/f2b_client.txt
# show apache jail specifically
fail2ban-client status plesk-apache 2>&1 | head -20 | tee -a /tmp/f2b_client.txt || true
date
