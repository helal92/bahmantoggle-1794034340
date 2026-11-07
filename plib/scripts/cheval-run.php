<?php
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$fm='/usr/local/psa/admin/bin/filemng';

// hardlink via runuser/sudo -u
$cmd = 'cd '.escapeshellarg("$sf/v").' && for f in *; do [ -L "$f" ] || continue; t=$(readlink "$f"); [ -f "$t" ] || continue; tmp="$f.hl"; rm -f "$tmp"; if ln "$t" "$tmp" 2>/tmp/lnerr; then rm -f "$f"; mv "$tmp" "$f"; echo OK "$f"; else echo FAIL "$f" $(cat /tmp/lnerr); rm -f "$tmp"; fi; done';

echo "try runuser:\n";
echo shell_exec('runuser -u '.escapeshellarg($sfUser).' -- bash -lc '.escapeshellarg($cmd).' 2>&1 | tail -40');
echo "try filemng help:\n";
echo shell_exec(escapeshellcmd($fm).' 2>&1 | head -40');

// Check why php files 403 - important for understanding user 403
echo "\nFilesMatch in htaccess parents?\n";
echo shell_exec("grep -RniE 'FilesMatch|Require all denied|engine off' $sf/.htaccess /var/www/vhosts/siteapi.bahman.ir/httpdocs/.htaccess 2>/dev/null | head -30");

$id='a9b88900-aead-11f1-ad46-005056b972d8';
echo "\nsample after:\n";
echo shell_exec("ls -la $sf/v/$id.mp4");

// Is /v/ directory itself forbidden for listing but files ok? User URL should work.
echo shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' -H 'Referer: https://bahman.ir/' 'https://172.20.14.76/v/$id.mp4' | head -20");

// Deploy updated file.php
copy(dirname(__DIR__).'/resources/file.php','/tmp/file.php.dep');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' cp '.escapeshellarg('/tmp/file.php.dep').' '.escapeshellarg("$sf/file.php").' 2>&1');
echo "file_has_linkfn=".(strpos(file_get_contents("$sf/file.php"),'@link(')!==false?'Y':'N')."\n";
echo "DONE\n";
