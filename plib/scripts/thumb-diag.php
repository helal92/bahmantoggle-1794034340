<?php
$uuid='61bb1145-d84f-11f1-b563-005056b972d8';
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
echo "FAST=".(is_file("$sf/.FAST_MODE")?'ON':'OFF')."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
$meta="$sf/cache/meta_".md5('fr_'.$uuid).".json";
echo "meta_exists=".(is_file($meta)?'Y':'N')."\n";
if (is_file($meta)) {
  $j=json_decode(file_get_contents($meta),true);
  echo "meta_exp=".($j['exp']??'')." now=".time()."\n";
  $row=$j['val']??null;
  if (is_array($row)) {
    echo "url=".($row['url']??'')."\n";
    echo "file_name=".($row['file_name']??'')."\n";
    $fp=($row['url']??'').'/'.($row['file_name']??'');
    echo "path=$fp\n";
    echo "exists=".(is_file($fp)?'Y':'N')."\n";
    if (is_file($fp)) {
      echo "size=".filesize($fp)." mtime=".filemtime($fp)."\n";
      $i=@getimagesize($fp);
      echo "imagesize=".json_encode($i)."\n";
    }
  } else echo "meta_val_bad\n";
}
# DB lookup via mysql if credentials available
$cfgCandidates=[
  "$sf/../ewcfg14.php",
  "$sf/../ewcfg.php",
  "/var/www/vhosts/siteapi.bahman.ir/httpdocs/ewcfg14.php",
  "/var/www/vhosts/siteapi.bahman.ir/httpdocs/config.php",
];
foreach ($cfgCandidates as $c) echo "cfg_cand $c ".(is_file($c)?'Y':'N')."\n";

# Local curl timings (bypass FortiWeb)
foreach (['600','1920',''] as $sz) {
  $url = $sz==='' 
    ? "https://127.0.0.1/$uuid"
    : "https://127.0.0.1/thumb/$uuid/$sz";
  $cmd='curl -sk --resolve sitefile.bahman.ir:443:127.0.0.1 --max-time 15 -o /tmp/_th.bin -w "code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer} total=%{time_total} type=%{content_type}\n" -H "Host: sitefile.bahman.ir" '.escapeshellarg($url);
  echo "REQ $sz ";
  echo trim(shell_exec($cmd))."\n";
  if (is_file('/tmp/_th.bin')) {
    $h=bin2hex(file_get_contents('/tmp/_th.bin', false, null, 0, 16));
    echo "headhex=$h\n";
  }
}

# also try via apache/docroot path and php-cli simulate hotpath
echo "--- php-cli hotpath ---\n";
$t0=microtime(true);
$_GET=['id'=>$uuid,'thumb'=>'1','size'=>'600'];
ob_start();
# don't include full file.php (exits); instead replicate hotpath check
define('BAHMAN_FAST_MODE', is_file("$sf/.FAST_MODE"));
$uuid2=preg_replace('/[^a-fA-F0-9\-]/','',$uuid);
$sizeRaw='600';
$metaPath="$sf/cache/meta_".md5('fr_'.$uuid2).'.json';
$raw=is_file($metaPath)?@file_get_contents($metaPath):false;
$data=($raw!==false&&$raw!=='')?json_decode($raw,true):null;
$row=(is_array($data)&&(int)($data['exp']??0)>=time()&&is_array($data['val']??null))?$data['val']:null;
echo "hot_row=".(is_array($row)?'Y':'N')."\n";
if (is_array($row)&&!empty($row['url'])&&!empty($row['file_name'])) {
  $filePath=$row['url'].'/'.$row['file_name'];
  echo "hot_file=".(is_file($filePath)?'Y':'N')." size=".(is_file($filePath)?filesize($filePath):0)."\n";
  $fileSalt='v1.0.5'; $fileTime=@filemtime($filePath);
  $fileHash=md5($fileSalt.($filePath.'600'.'0'.'1'.'0'.false.'0'.'0'.'0').$fileTime);
  $fileTemp="$sf/cache/$fileHash.img.txt";
  echo "thumb_cache=".(is_file($fileTemp)?'Y':'N')." ". (is_file($fileTemp)?filesize($fileTemp):0)." hash=$fileHash\n";
}
echo "cli_dt=".round(microtime(true)-$t0,3)."\n";

# find row via find in uploads?
echo "--- find uuid in uploads (timeout) ---\n";
echo trim(shell_exec("timeout 8 find /var/www/vhosts/siteapi.bahman.ir -name '*61bb1145*' 2>/dev/null | head -20"))."\n";
echo "DONE\n";
