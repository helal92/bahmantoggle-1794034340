<?php
copy(dirname(__DIR__).'/resources/speed_bahman.sh','/tmp/speed_bahman.sh');
chmod('/tmp/speed_bahman.sh',0755);
@unlink('/tmp/speed_bahman.log');
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach(preg_split('/\n/',$list) as $line){
  if(strpos($line,'speed_bahman')===false) continue;
  if(preg_match('/\b(\d+)\b/',$line,$m)) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
}
shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg('/bin/bash /tmp/speed_bahman.sh').' -event phys_hosting_update -priority 90 2>&1');
echo trim((string)shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
for($i=0;$i<50;$i++){
  clearstatcache(true,'/tmp/speed_bahman.log');
  $log=(string)@file_get_contents('/tmp/speed_bahman.log');
  if(strpos($log,'DONE')!==false) break;
  usleep(300000);
}
echo (string)@file_get_contents('/tmp/speed_bahman.log');
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach(preg_split('/\n/',$list) as $line){
  if(strpos($line,'speed_bahman')===false) continue;
  if(preg_match('/\b(\d+)\b/',$line,$m)) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
}
