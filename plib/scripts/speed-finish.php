<?php
copy(dirname(__DIR__).'/resources/speed_finish.sh', '/tmp/speed_finish.sh');
chmod('/tmp/speed_finish.sh', 0755);
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach(preg_split('/\n/',$list) as $line){
  if(strpos($line,'speed_finish')===false) continue;
  if(preg_match('/\b(\d+)\b/',$line,$m)) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
}
$cmd='/bin/bash /tmp/speed_finish.sh';
shell_exec('plesk bin event_handler --create -user root -command '.escapeshellarg($cmd).' -event phys_hosting_update -priority 90 2>&1');
echo trim((string)shell_exec('plesk bin domain --update bahman.ir -www-root httpdocs 2>&1'))."\n";
for($i=0;$i<40;$i++){
  $log=(string)@file_get_contents('/tmp/speed_finish.log');
  if(strpos($log,'DONE')!==false) break;
  usleep(400000);
}
echo (string)@file_get_contents('/tmp/speed_finish.log');
$list=(string)shell_exec('plesk bin event_handler --list 2>&1');
foreach(preg_split('/\n/',$list) as $line){
  if(strpos($line,'speed_finish')===false) continue;
  if(preg_match('/\b(\d+)\b/',$line,$m)) shell_exec('plesk bin event_handler --delete '.escapeshellarg($m[1]).' 2>&1');
}
