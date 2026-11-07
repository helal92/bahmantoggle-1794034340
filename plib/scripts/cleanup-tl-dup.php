<?php
$fm='/opt/psa/admin/bin/filemng';
$cmd=$fm.' root exec / bash -c '.escapeshellarg('rm -f /etc/apache2/plesk.conf.d/vhosts/tl.bahman.ir-ttyd.conf; apache2ctl configtest && systemctl reload apache2; apache2ctl -S 2>&1 | grep tl.bahman; systemctl is-active ttyd').' --allow-root 2>&1';
echo trim(shell_exec($cmd));
echo "\nDONE\n";
