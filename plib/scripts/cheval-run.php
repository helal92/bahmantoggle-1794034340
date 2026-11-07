<?php
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$id='a9b88900-aead-11f1-ad46-005056b972d8';
$link="$sf/v/$id.mp4";
echo "link_exists=".(is_link($link)||is_file($link)?'Y':'N')."\n";
if (is_link($link)) {
  echo "link_target=".readlink($link)."\n";
  echo "target_exists=".(is_file(readlink($link))?'Y':'N')."\n";
  echo "target_readable=".(is_readable(readlink($link))?'Y':'N')."\n";
  echo shell_exec('ls -la '.escapeshellarg($link).' '.escapeshellarg(readlink($link)).' 2>&1');
}
echo "v_dir:\n";
echo shell_exec("ls -la $sf/v | head -20");
echo "\nhtaccess first rules:\n";
echo shell_exec("sed -n '1,80p' $sf/.htaccess");

echo "\n=== internal curl UUID ===\n";
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' -H 'Referer: https://bahman.ir/' -H 'Range: bytes=0-' 'https://172.20.14.76/v/$id.mp4'");
echo "\n=== internal curl range small ===\n";
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/$id.mp4'");
echo "\n=== GET first bytes ===\n";
echo shell_exec("curl -sk --max-time 15 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' -o /tmp/vtest.bin -w 'code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer}\\n' 'https://172.20.14.76/v/$id.mp4'");
echo shell_exec('file /tmp/vtest.bin; xxd /tmp/vtest.bin | head -3');

// apache error for this
echo "\napache/proxy errors:\n";
echo shell_exec("tail -50 /var/www/vhosts/siteapi.bahman.ir/logs/error_log 2>/dev/null | tail -30");
echo shell_exec("tail -50 /var/www/vhosts/siteapi.bahman.ir/logs/proxy_error_log 2>/dev/null | tail -20");
// sitefile might log under siteapi
echo shell_exec("ls /var/www/vhosts/system/sitefile.bahman.ir/logs 2>/dev/null");
echo shell_exec("tail -40 /var/www/vhosts/system/sitefile.bahman.ir/logs/error_log 2>/dev/null");
echo shell_exec("tail -40 /var/www/vhosts/system/sitefile.bahman.ir/logs/proxy_error_log 2>/dev/null");

// open_basedir / symlink follow - check apache Options
echo "\nFollowSymLinks test via php open:\n";
$t=readlink($link);
echo "fopen: ";
$fh=@fopen($t,'rb');
echo $fh?'ok':'fail'; echo "\n";

// SELinux?
echo shell_exec("getenforce 2>/dev/null; ls -Z $link 2>/dev/null | head");
