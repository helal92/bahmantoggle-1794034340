<?php
echo "logrot_mng help:\n".trim(shell_exec('/usr/local/psa/admin/sbin/logrot_mng --help 2>&1 | head -60'))."\n---\n";
echo "logmng help:\n".trim(shell_exec('/usr/local/psa/admin/sbin/logmng --help 2>&1 | head -40'))."\n---\n";
// force aggressive rotation settings then trigger
foreach ([
  'plesk bin site --update bahman.ir -log-rotate true -log-bysize 100M -log-max-num-files 3 -log-compress true 2>&1',
  'plesk bin site --update sitefile.bahman.ir -log-rotate true -log-bysize 100M -log-max-num-files 3 -log-compress true 2>&1',
  '/usr/local/psa/admin/sbin/logrot_mng --force 2>&1 | tail -30',
  '/usr/local/psa/admin/sbin/logrot_mng --rotate 2>&1 | tail -30',
  '/usr/local/psa/admin/sbin/logrot_mng bahman.ir 2>&1 | tail -30',
] as $cmd) {
  echo "CMD $cmd\n";
  echo trim(shell_exec($cmd))."\n\n";
}
echo "sizes:\n".trim(shell_exec('ls -lh /var/www/vhosts/system/bahman.ir/logs/access_log /var/www/vhosts/system/sitefile.bahman.ir/logs/access_log 2>&1'))."\n";
echo "df=".trim(shell_exec('df -h /var | tail -1'))."\nDONE\n";
