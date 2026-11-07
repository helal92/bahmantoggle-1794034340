<?php
echo "help logrotate:\n".trim(shell_exec('plesk bin domain_log --help 2>&1 | head -40'))."\n";
echo "---\n";
echo trim(shell_exec('plesk bin site --help 2>&1 | grep -i log | head'))."\n";
echo "---\n";
echo trim(shell_exec('ls /usr/local/psa/admin/sbin/*log* /opt/psa/admin/sbin/*log* 2>/dev/null'))."\n";
// try statistics or rotate
foreach ([
  'plesk bin domain_log --rotate bahman.ir 2>&1',
  'plesk bin domain_log --rotate sitefile.bahman.ir 2>&1',
  '/usr/local/psa/admin/sbin/statistics --calculate-one --domain-name=bahman.ir 2>&1 | tail -5',
  'logrotate -f /etc/logrotate.d/apache2 2>&1 | tail -10',
  'logrotate -f /usr/local/psa/etc/logrotate.conf 2>&1 | tail -20',
] as $cmd) {
  echo "CMD $cmd\n";
  echo trim(shell_exec($cmd))."\n\n";
}
echo "sizes after:\n";
echo trim(shell_exec('ls -lh /var/www/vhosts/system/bahman.ir/logs/access_log /var/www/vhosts/system/sitefile.bahman.ir/logs/access_log 2>&1'))."\n";
echo "df=".trim(shell_exec('df -h /var | tail -1'))."\n";
echo "DONE\n";
