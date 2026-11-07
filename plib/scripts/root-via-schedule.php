<?php
echo "schedule help:\n".trim(shell_exec('plesk bin schedule --help 2>&1 | head -80'))."\n";
echo "tasks:\n".trim(shell_exec('plesk bin schedule --list 2>&1 | head -40'))."\n";
# try at
echo "at:\n".trim(shell_exec('echo "/bin/bash /tmp/apache_queue_fix.sh" | at now 2>&1'))."\n";
echo "crontab root readable?\n".trim(shell_exec('ls -la /etc/cron.d /var/spool/cron 2>&1 | head'))."\n";
# setuid wrappers
echo "setuid:\n".trim(shell_exec('find /usr/local/psa/admin/sbin /opt/psa/admin/sbin -perm -4000 2>/dev/null | head -40'))."\n";
# try plesk daily
echo "wrapper:\n".trim(shell_exec('ls /usr/local/psa/admin/sbin/wrapper 2>&1; /usr/local/psa/admin/sbin/wrapper --help 2>&1 | head -20'))."\n";
