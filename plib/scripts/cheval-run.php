<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$id='61491e0c-bfc8-11f1-9600-005056b972d8';

function fcp($fm,$user,$src,$dst){
  $tmp='/tmp/'.basename($src).'.'.getmypid();
  copy($src,$tmp);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dst).' 2>&1');
  @unlink($tmp);
}

// Who overwrote file.php?
echo "file.php mtime=".date('c', filemtime("$sf/file.php"))." size=".filesize("$sf/file.php")."\n";
echo "has_Location=".(strpos(file_get_contents("$sf/file.php"),'Location: /v/')!==false?'Y':'N')."\n";
echo shell_exec("ls -la $sf/file.php $sf/file-back.php 2>&1");

// Redeploy file.php + htaccess
fcp($fm,$sfUser,"$res/file.php","$sf/file.php");
fcp($fm,$sfUser,"$res/htaccess.sitefile","$sf/.htaccess");
@mkdir("$sf/v",0775,true);

// Ensure symlink for hero uuid
$real='/var/www/vhosts/siteapi.bahman.ir/httpdocs/uploads/app_1/bp_companies_1/1791100281-38666780.mp4';
$link="$sf/v/$id.mp4";
if (!is_link($link) && !is_file($link)) symlink($real,$link);
echo "link_ok=".(is_link($link)||is_file($link)?'Y':'N')."\n";
echo "htaccess_has_static_rule=".(strpos(file_get_contents("$sf/.htaccess"),'DOCUMENT_ROOT}/v/')!==false?'Y':'N')."\n";
echo "file_has_Location=".(strpos(file_get_contents("$sf/file.php"),'Location: /v/')!==false?'Y':'N')."\n";

// Verify UUID hits Apache static (Server: Apache) not PHP stream
echo "=== UUID ===\n";
$h=shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/$id'");
echo $h;
echo "server_is_apache=".(preg_match('/Server:\\s*Apache/i',$h)?'Y':'N')."\n";
echo "is_302=".(preg_match('/HTTP\\/1\\.[01] 302/',$h)?'Y':'N')."\n";
echo "is_206=".(preg_match('/HTTP\\/1\\.[01] 206/',$h)?'Y':'N')."\n";

// Warm many video symlinks from meta cache (so UUID never hits PHP)
$n=0;
foreach (glob("$sf/cache/meta_*.json") ?: [] as $mf) {
  $j=json_decode(@file_get_contents($mf),true);
  $row=$j['val'] ?? null;
  if (!$row || empty($row['uuid']) || empty($row['file_name'])) continue;
  $ext=strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
  if (!in_array($ext,['mp4','webm','mov','m4v'],true)) continue;
  $cand=$sf.'/'.$row['url'].'/'.$row['file_name'];
  $realPath=realpath($cand);
  if (!$realPath) continue;
  $lp="$sf/v/{$row['uuid']}.$ext";
  if (!is_link($lp) && !is_file($lp)) { @symlink($realPath,$lp); $n++; }
}
echo "new_symlinks=$n total_v=".trim(shell_exec("ls $sf/v | wc -l"))."\n";

// apache CPU check
echo shell_exec("ps -eo pcpu,args --sort=-pcpu | grep -E 'apache2|php-fpm: pool sitefile' | grep -v grep | head -15");
echo "load ".trim(@file_get_contents('/proc/loadavg'))."\n";

// Confirm homepage still points to /v/
$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/"');
if (preg_match('/data-src="([^"]+)"/',$html,$m)) echo "hero_data_src=".$m[1]."\n";
echo "DONE\n";
