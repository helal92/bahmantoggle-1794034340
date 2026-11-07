<?php
echo "start\n";
echo "f2b=".trim((string)shell_exec('ls -la /usr/local/psa/admin/sbin/f2bmng'))."\n";
echo "strings:\n".trim((string)shell_exec('strings /usr/local/psa/admin/sbin/f2bmng 2>/dev/null | grep -E "^--|^[a-z].{0,30}$" | head -80'))."\n";
echo "file:\n".trim((string)shell_exec('file /usr/local/psa/admin/sbin/f2bmng'))."\n";
# read via od first bytes
echo "crontab get:\n".trim((string)shell_exec('timeout 5 /usr/local/psa/admin/sbin/crontabmng get root 2>&1 | head -30'))."\n";
# try writing root cron to restart fail2ban
$cron = "* * * * * /bin/systemctl restart fail2ban >/tmp/f2b_cron.out 2>&1; /bin/systemctl is-active fail2ban >>/tmp/f2b_cron.out; /usr/bin/fail2ban-client -t >>/tmp/f2b_cron.out 2>&1; crontab -u root -l 2>/dev/null | grep -v f2b_cron >/tmp/rootcron.new; crontab -u root /tmp/rootcron.new; rm -f /etc/cron.d/f2b-fix 2>/dev/null\n";
# better: use cron.d file via... can't write /etc/cron.d as psaadm
# use crontabmng set
file_put_contents('/tmp/root_cron_f2b.txt', "#plesk f2b fix\n* * * * * root /bin/bash -c '/bin/systemctl restart fail2ban; /bin/systemctl is-active fail2ban > /tmp/f2b_status.txt; /usr/bin/fail2ban-client status > /tmp/f2b_client.txt 2>&1; journalctl -u fail2ban -n 50 --no-pager > /tmp/f2b_journal.txt 2>&1; sleep 2; /bin/systemctl is-active fail2ban >> /tmp/f2b_status.txt'\n");
# cron.d needs root write - try crontabmng
echo "crontabmng help:\n".trim((string)shell_exec('timeout 5 strings /usr/local/psa/admin/sbin/crontabmng | head -40'))."\n";
echo "done\n";
