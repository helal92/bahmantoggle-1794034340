<?php
echo "find cache files:\n";
echo shell_exec("find /tmp /var/tmp /var/www/vhosts/bahman.ir -name 'bahman_home*' 2>/dev/null");
echo shell_exec("find /tmp -name 'bahman_*' 2>/dev/null");
// PrivateTmp for php-fpm
echo shell_exec("find /tmp/systemd-private-* -name 'bahman_home*' 2>/dev/null | head");
echo shell_exec("ls -la /proc/\$(pgrep -f 'php-fpm: pool bahman.ir' | head -1)/root/tmp 2>/dev/null | head");
$pid=trim((string)shell_exec("pgrep -f 'php-fpm: pool bahman.ir' | head -1"));
echo "pid=$pid\n";
if ($pid) {
  echo shell_exec("ls -la /proc/$pid/root/tmp/bahman_* 2>/dev/null | head");
  echo shell_exec("find /proc/$pid/root/tmp -name 'bahman_*' 2>/dev/null | head");
  // systemd private tmp path
  echo shell_exec("readlink -f /proc/$pid/root/tmp 2>/dev/null");
  echo shell_exec("ls -la /tmp/systemd-private-*-php*-fpm*/tmp/bahman_* 2>/dev/null | head");
  echo shell_exec("find /tmp -path '*php*' -name 'bahman_home*' 2>/dev/null | head");
}
// ask PHP itself
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
file_put_contents('/tmp/_tmpinfo.php','<?php header("Content-Type:text/plain"); echo "sys=".sys_get_temp_dir()."\n"; echo "file=".sys_get_temp_dir()."/bahman_home_page_v4.html exists=".(is_file(sys_get_temp_dir()."/bahman_home_page_v4.html")?"Y":"N")."\n"; if(is_file(sys_get_temp_dir()."/bahman_home_page_v4.html")){ echo "size=".filesize(sys_get_temp_dir()."/bahman_home_page_v4.html")."\n"; @unlink(sys_get_temp_dir()."/bahman_home_page_v4.html"); echo "deleted\n"; } foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p){ echo "also $p\n"; @unlink($p);} ');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg('/tmp/_tmpinfo.php').' '.escapeshellarg('/var/www/vhosts/bahman.ir/httpdocs/_tmpinfo.php').' 2>&1');
echo shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/_tmpinfo.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg('/var/www/vhosts/bahman.ir/httpdocs/_tmpinfo.php').' 2>&1');

// regenerate home and check
$html=shell_exec('curl -sk --max-time 45 -H "Host: bahman.ir" "https://172.20.14.76/?regen='.time().'"');
$hdr=shell_exec('curl -skI --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?regen2='.time().'"');
echo "HDR2:\n$hdr\nhome_len=".strlen($html)."\n";
if (preg_match('/<video class="hero-media[^"]*"[^>]*>/',$html,$m)) echo "V0: ".$m[0]."\n";
echo "eager=".(preg_match('/<video[^>]*\ssrc=/',$html)?'Y':'N')." autoplay=".(preg_match('/<video[^>]*autoplay/',$html)?'Y':'N')."\n";
