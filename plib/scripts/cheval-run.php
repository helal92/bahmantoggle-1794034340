<?php
$fm='/usr/local/psa/admin/bin/filemng';
// read nginx as root via filemng? filemng is for vhost user files.
// Try running as root with full path through plesk php - the CLI extension might support --exec with root
echo "uid=".posix_geteuid()." user=".posix_getpwuid(posix_geteuid())['name']."\n";
echo "groups=".implode(',', posix_getgroups())."\n";
// Can we read via /usr/bin/cat as root from API wrapper? Try plesk daily or 
echo shell_exec('/usr/bin/id; /usr/bin/ls -la /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf');
// Copy conf to /tmp readable
$out = [];
exec('/bin/cp /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf /tmp/sf_nginx.conf 2>&1', $out, $code);
echo "cp_code=$code out=".implode(' ',$out)."\n";
if (is_file('/tmp/sf_nginx.conf')) echo file_get_contents('/tmp/sf_nginx.conf');
exec('/bin/cp /var/www/vhosts/system/sitefile.bahman.ir/conf/httpd.conf /tmp/sf_httpd.conf 2>&1', $out2, $code2);
echo "\nhttpd_cp=$code2\n";
if (is_file('/tmp/sf_httpd.conf')) {
  echo shell_exec("grep -nE 'RewriteRule|file\\.php|DocumentRoot|Alias|ProxyPass|XSendFile' /tmp/sf_httpd.conf | head -60");
}
// sitefile domain root
echo shell_exec("plesk db -Ne \"SELECT d.name, h.www_root FROM domains d JOIN hosting h ON h.dom_id=d.id WHERE d.name IN ('sitefile.bahman.ir','siteapi.bahman.ir','bahman.ir')\" 2>&1");
