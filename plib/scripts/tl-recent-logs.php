<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; echo trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'))."\n";}
root('tail -n 60 /var/log/apache2/tl.bahman.ir-access.log');
root('echo ---; cat /var/www/vhosts/system/tl.bahman.ir/conf/httpd.conf');
echo "DONE\n";
