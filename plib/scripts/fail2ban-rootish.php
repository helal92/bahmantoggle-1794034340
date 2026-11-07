<?php
echo "=== privileged ===\n";
echo trim((string)shell_exec('id'))."\n";
echo "setuid:\n".trim((string)shell_exec('find /usr/local/psa/admin/sbin /opt/psa/admin/sbin -perm -4000 2>/dev/null | head -50'))."\n";
echo "mng:\n".trim((string)shell_exec('ls /usr/local/psa/admin/sbin/*mng* 2>/dev/null | head -40'))."\n";
echo "schedule help:\n".trim((string)shell_exec('plesk bin schedule --help 2>&1 | head -50'))."\n";
echo "event_handler:\n".trim((string)shell_exec('plesk bin event_handler --help 2>&1 | head -40'))."\n";
# Try copying jail configs to /tmp via dd as... won't work
# Use plesk daily or repair
echo "repair:\n".trim((string)shell_exec('plesk bin repair --help 2>&1 | head -30'))."\n";
# Check components for fail2ban installer
echo "installer:\n".trim((string)shell_exec('plesk installer --help 2>&1 | head -20'))."\n";
echo "components:\n".trim((string)shell_exec('plesk installer --select-release-current --show-components 2>&1 | grep -i fail | head'))."\n";
