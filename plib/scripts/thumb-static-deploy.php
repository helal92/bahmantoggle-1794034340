<?php
$fm='/usr/local/psa/admin/bin/filemng';
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$sfUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg($sf)));
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources';

@mkdir("$sf/t", 0775, true);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' mkdir '.escapeshellarg("$sf/t").' 2>&1');

foreach (['file.php'=>'file.php','htaccess.sitefile'=>'.htaccess'] as $src=>$dst) {
  $from="$res/$src"; $to="$sf/$dst";
  if (!is_file($from)) { echo "missing $from\n"; continue; }
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' cp '.escapeshellarg($from).' '.escapeshellarg($to).' 2>&1');
  echo "copied $dst\n";
}
@touch("$sf/.FAST_MODE");
@chmod("$sf/t", 0775);

// Ensure t is writable by apache/php
$httpUser=trim((string)shell_exec("ps -eo user,cmd | awk '/php-fpm: pool sitefile/{print $1; exit}'"));
echo "httpUser=$httpUser sfUser=$sfUser\n";
if ($httpUser) {
  echo shell_exec('chown -R '.escapeshellarg($sfUser.':psacln').' '.escapeshellarg("$sf/t").' 2>&1');
}

$uuids=[
'61bb1145-d84f-11f1-b563-005056b972d8',
'2ec8e505-d74c-11f1-b563-005056b972d8',
'340d73a9-d74b-11f1-b563-005056b972d8',
'a21fea2c-d6bd-11f1-b563-005056b972d8',
'391782e2-d6bd-11f1-b563-005056b972d8',
'03945293-d752-11f1-b563-005056b972d8',
'bd7896ca-c799-11f1-9600-005056b972d8',
];
$sizes=['600','960','1920'];

// Warm via local host (generates cache + publishes via new file.php)
foreach ($uuids as $u) {
  foreach ($sizes as $sz) {
    $url="https://172.20.14.76/thumb/$u/$sz";
    $out=trim(shell_exec('curl -sk --max-time 25 -o /tmp/_tw.bin -w "%{http_code}:%{size_download}:%{time_total}" -H "Host: sitefile.bahman.ir" '.escapeshellarg($url)));
    echo "warm $u/$sz $out\n";
  }
}

// Also explicitly publish from cache using meta (in case publish failed)
chdir($sf);
$pub=0;
foreach ($uuids as $u) {
  $meta="$sf/cache/meta_".md5('fr_'.$u).'.json';
  if (!is_file($meta)) continue;
  $j=json_decode(file_get_contents($meta),true);
  $row=$j['val']??null;
  if (!is_array($row)) continue;
  $filePath=$row['url'].'/'.$row['file_name'];
  if (!is_file($filePath)) continue;
  $ext=strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
  $pubExt=($ext==='jpeg')?'jpg':(($ext==='jpg')?'jpg':$ext);
  $fileTime=filemtime($filePath);
  foreach ($sizes as $sz) {
    $hash=md5('v1.0.5'.($filePath.$sz.'0'.'1'.'0'.false.'0'.'0'.'0').$fileTime);
    $cache="$sf/cache/$hash.img.txt";
    if (!is_file($cache) || filesize($cache)<1) continue;
    $dest="$sf/t/{$u}_{$sz}.{$pubExt}";
    @unlink($dest);
    if (!@link($cache,$dest)) @copy($cache,$dest);
    if (is_file($dest)) { $pub++; echo "pub $dest ".filesize($dest)."\n"; }
  }
}
echo "published=$pub\n";
echo "t_count=".trim(shell_exec('ls '.escapeshellarg("$sf/t").' | wc -l'))."\n";

// Verify Apache static rewrite (should be fast, no php)
foreach (['61bb1145-d84f-11f1-b563-005056b972d8/600','340d73a9-d74b-11f1-b563-005056b972d8/600'] as $p) {
  echo "verify $p ".trim(shell_exec('curl -sk --max-time 8 -o /dev/null -w "%{http_code} %{size_download} %{time_total}" -H "Host: sitefile.bahman.ir" '.escapeshellarg("https://172.20.14.76/thumb/$p")))."\n";
}

// Mild FPM bump if too low
$pool=trim(shell_exec('plesk bin php_handler --list 2>/dev/null | head -5'));
echo "handlers:\n$pool\n";
$conf=trim(shell_exec('grep -R "pm.max_children" /var/www/vhosts/system/sitefile.bahman.ir/etc/php-fpm.d/ 2>/dev/null | head -5'));
if ($conf==='') $conf=trim(shell_exec('grep -R "pm.max_children" /opt/plesk/php/*/etc/php-fpm.d/*sitefile* 2>/dev/null | head -5'));
if ($conf==='') $conf=trim(shell_exec('grep -R "\[sitefile" -n /etc/php-fpm.d /opt/plesk/php/*/etc/php-fpm.d 2>/dev/null | head -20'));
echo "fpm_conf:\n$conf\n";
echo "fpm_now=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "DONE\n";
