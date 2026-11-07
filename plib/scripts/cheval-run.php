<?php
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$id='61491e0c-bfc8-11f1-9600-005056b972d8';
echo "t0 file.php size=".filesize("$sf/file.php")." mtime=".date('H:i:s',filemtime("$sf/file.php"))." loc=".(strpos(file_get_contents("$sf/file.php"),'Location: /v/')!==false?'Y':'N')."\n";

// find auto-deployers
echo "crons mentioning file.php/sitefile:\n";
echo shell_exec("grep -RniE 'file\\.php|sitefile|file_manager' /etc/cron* /var/spool/cron /var/www/vhosts/*/crontab 2>/dev/null | head -30");
echo shell_exec("ls -la /var/www/vhosts/siteapi.bahman.ir/git* /var/www/vhosts/siteapi.bahman.ir/.git 2>/dev/null | head");
echo shell_exec("find /var/www/vhosts/siteapi.bahman.ir -maxdepth 3 -name '*.sh' 2>/dev/null | head -20");
echo shell_exec("grep -RniE 'file\\.php|rsync|file_manager' /var/www/vhosts/siteapi.bahman.ir/httpdocs --include='*.sh' --include='*.php' 2>/dev/null | grep -iE 'copy|deploy|rsync|sync' | head -20");

// also check if laravel or watchdog restores
echo shell_exec("ls -la $sf/file.php; md5sum $sf/file.php");

sleep(8);
echo "t1 file.php size=".filesize("$sf/file.php")." mtime=".date('H:i:s',filemtime("$sf/file.php"))." loc=".(strpos(file_get_contents("$sf/file.php"),'Location: /v/')!==false?'Y':'N')."\n";

// UUID still apache static?
$h=shell_exec("curl -skI --max-time 8 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/$id'");
echo "uuid_server=".(preg_match('/Server:\\s*Apache/i',$h)?'Apache':(preg_match('/Server:\\s*web/i',$h)?'web':'?'))." status=".(preg_match('/HTTP\\/1\\.[01] (\\d+)/',$h,$m)?$m[1]:'?')."\n";

// parallel css+home while downloading video range repeatedly (simulate users)
echo "stress with 5 video ranges + css:\n";
$cmds=[];
for ($i=0;$i<5;$i++) $cmds[]="curl -sk --max-time 15 -o /dev/null -w 'v$i %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1048575' 'https://172.20.14.76/v/$id.mp4'";
foreach (['navigation.css','index.css','hero-bahman.css'] as $c) $cmds[]="curl -sk --max-time 15 -o /dev/null -w '$c %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/templates/bahman/assets/$c'";
$cmds[]="curl -sk --max-time 15 -o /dev/null -w 'home %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/'";
echo shell_exec(implode(' & ',$cmds).' & wait');
echo "load ".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "DONE\n";
