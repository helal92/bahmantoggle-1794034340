<?php
function sh($c){return trim((string)shell_exec($c));}
$idx=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/index.php');
// all page cache related lines
$lines=explode("\n",$idx);
foreach ($lines as $i=>$l) {
  if (stripos($l,'bahmanPageCache')!==false || stripos($l,'X-Bahman-Cache')!==false || stripos($l,'_page_v')!==false || stripos($l,'home_page')!==false) {
    echo ($i+1).": ".$l."\n";
  }
}

// PHP-FPM may use different tmp - check private tmp
echo "private_tmp=\n".sh('ls -la /tmp/systemd-private-*/tmp/bahman* 2>/dev/null; ls -la /tmp/systemd-private-* 2>/dev/null | head')."\n";
echo "find_home_cache=\n".sh('find /tmp /var/tmp /dev/shm -name "bahman_*page*.html" 2>/dev/null')."\n";
echo "find_any_html_cache=\n".sh('find /tmp /var/tmp /dev/shm -name "bahman_*.html" 2>/dev/null')."\n";

// Use php as domain user via filemng exec? Or create a web-accessible purge
// Hit include=index which uses bahman_home_page_v4.html and also check /
$php='/opt/plesk/php/8.3/bin/php';
// run a one-liner as root to delete from all possible systemd private tmps
echo sh('find /tmp /var/tmp /dev/shm -name "bahman_*.html" -delete -print 2>/dev/null')."\n";
echo sh('find /tmp -path "*systemd-private*" -name "bahman_*.html" -delete -print 2>/dev/null')."\n";

// Also expire by writing empty? Better: use sudo -u to find open_basedir tmp from a php cli with same ini
$ini='/var/www/vhosts/system/bahman.ir/etc/php.ini';
echo "sys_temp from ini:\n".sh(escapeshellarg($php).' -c '.escapeshellarg($ini).' -r \'echo sys_get_temp_dir(),"\\n"; echo implode("\\n", glob(sys_get_temp_dir()."/bahman_*") ?: []);\'')."\n";

// Force regenerate via include=index with cache miss after delete
@unlink(sys_get_temp_dir().'/bahman_home_page_v4.html');
@unlink(sys_get_temp_dir().'/bahman_sales_page_v4.html');

$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/?include=index&nocache='.time().'"');
$h=sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/?include=index&nocache2='.time().'"');
echo "include_index_hdr=\n$h\n";
if (preg_match('#<link[^>]+brand-slider\.css[^>]*>#',$page,$m)) echo "include_tag=".$m[0]."\n";

// root homepage
$h2=sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/"');
echo "root_hdr=\n$h2\n";
$root=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/"');
if (preg_match('#<link[^>]+brand-slider\.css[^>]*>#',$root,$m)) echo "root_tag=".$m[0]."\n";
echo "root_has_RO_import_path=n/a live js check separately\n";
echo "DONE\n";
