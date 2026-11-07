<?php
echo trim((string)shell_exec('plesk bin domain --info bahman.ir 2>&1'))."\n";
echo "---\n";
echo trim((string)shell_exec('stat -c "%U %G %n" /var/www/vhosts/bahman.ir/httpdocs/index.php 2>&1'))."\n";
echo trim((string)shell_exec('ls -la /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl 2>&1'))."\n";
