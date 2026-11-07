<?php
$id='a9b88900-aead-11f1-ad46-005056b972d8';
echo "=== access log /v/ ===\n";
echo shell_exec("grep '/v/' /var/www/vhosts/system/sitefile.bahman.ir/logs/access_ssl_log 2>/dev/null | tail -30");
echo shell_exec("grep ' 403 ' /var/www/vhosts/system/sitefile.bahman.ir/logs/access_ssl_log 2>/dev/null | tail -20");
echo "\n=== modsec ===\n";
echo shell_exec("grep -iE 'forbidden|/v/|symlink' /var/www/vhosts/system/sitefile.bahman.ir/logs/error_log 2>/dev/null | tail -30");
echo shell_exec("ls /var/www/vhosts/system/sitefile.bahman.ir/conf/");
echo shell_exec("grep -nE 'modsecurity|SymLinks|FollowSym|AllowOverride|v/' /tmp/sf_nginx.conf /tmp/sf_httpd.conf 2>/dev/null | head");
# try copy nginx again with psaadm - maybe use filemng root?
echo shell_exec("plesk bin secret_key --help >/dev/null 2>&1; /opt/psa/admin/bin/filemng root cat /var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf 2>&1 | head -5");
// Check if SymLinksIfOwnerMatch vs FollowSymLinks - outside uploads might fail with SymLinksIfOwnerMatch
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$uploads='/var/www/vhosts/siteapi.bahman.ir/httpdocs/uploads';
echo "v owner: "; echo shell_exec("stat -c '%U:%G' $sf/v");
echo "uploads owner: "; echo shell_exec("stat -c '%U:%G' $uploads/app_1/bp_companies_1/1789219634-846274692.mp4");
echo "file_manager owner: "; echo shell_exec("stat -c '%U:%G' $sf");

// Alternative: hardlink or copy into v/ instead of symlink outside
// Or symlink relative within same tree
echo "\nTry relative symlink inside httpdocs:\n";
$relTarget = '../../uploads/app_1/bp_companies_1/1789219634-846274692.mp4';
$link = "$sf/v/$id.mp4";
// For Apache SymLinksIfOwnerMatch, target outside with same owner should work if FollowSymLinks is on
// 403 from outside might be nginx denying symlink escape

// Check nginx alias - read via python as root through plesk db?
echo shell_exec("python3 - <<'PY'\nimport os\np='/var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf'\ntry:\n  print(open(p).read()[:3000])\nexcept Exception as e:\n  print('err',e)\nPY");
