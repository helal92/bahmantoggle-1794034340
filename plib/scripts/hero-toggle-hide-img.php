<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$bhUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg("$bh/index.php")));
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources';
$map=[
  'assets/hero-video-control.js'=>"$bh/templates/bahman/assets/hero-video-control.js",
  'index.tpl'=>"$bh/templates/bahman/index.tpl",
];
foreach($map as $src=>$dst){
  $from="$res/$src";
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($from).' '.escapeshellarg($dst).' 2>&1');
  echo basename($dst).' '.(is_file($dst)?filesize($dst):0)."\n";
}
$js=file_get_contents("$bh/templates/bahman/assets/hero-video-control.js");
echo "hide_no_video=".(strpos($js,'Image-only slides')!==false?'Y':'N')."\n";
$tpl=file_get_contents("$bh/templates/bahman/index.tpl");
echo "btn_hidden_attr=".(preg_match('/hero-video-toggle[^>]*\bhidden\b/',$tpl)?'Y':'N')."\n";
foreach(glob("$bh/templates_c/*")?:[] as $p) if(is_file($p)) @unlink($p);
foreach(glob('/tmp/bahman_*')?:[] as $p) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');
echo "DONE\n";
