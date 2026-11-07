<?php
$iniFile='/tmp/sitefile_opcache_settings.ini';
file_put_contents($iniFile, "opcache.enable=1\nopcache.memory_consumption=256\nopcache.max_accelerated_files=60000\nopcache.revalidate_freq=60\nopcache.validate_timestamps=1\n");
echo "update: ".trim((string)shell_exec('plesk bin site --update-php-settings sitefile.bahman.ir -settings '.escapeshellarg($iniFile).' 2>&1'))."\n";
echo "show:\n".trim((string)shell_exec('plesk bin site --show-php-settings sitefile.bahman.ir 2>&1 | grep -i opcache'))."\n";
echo "ini:\n".trim((string)shell_exec('grep -E "opcache" /var/www/vhosts/system/sitefile.bahman.ir/etc/php.ini | head -20'))."\n";
