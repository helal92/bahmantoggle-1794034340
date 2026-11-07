<?php
$uuids=[
'61bb1145-d84f-11f1-b563-005056b972d8',
'2ec8e505-d74c-11f1-b563-005056b972d8',
'340d73a9-d74b-11f1-b563-005056b972d8',
'a21fea2c-d6bd-11f1-b563-005056b972d8',
'391782e2-d6bd-11f1-b563-005056b972d8',
'03945293-d752-11f1-b563-005056b972d8',
'bd7896ca-c799-11f1-9600-005056b972d8',
];
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$doc='/var/www/vhosts/siteapi.bahman.ir/httpdocs';
echo "cwd_check uploads=". (is_dir("$doc/uploads")?'Y':'N')." companies=".(is_dir("$doc/uploads/app_1/bp_companies_1")?'Y':'N')."\n";
echo "fm_uploads=". (is_dir("$sf/../uploads")?'Y':'N')."\n";
# absolute resolve
chdir($sf);
foreach ($uuids as $uuid) {
  $meta="$sf/cache/meta_".md5('fr_'.$uuid).".json";
  echo "==== $uuid meta=".(is_file($meta)?'Y':'N')."\n";
  $row=null;
  if (is_file($meta)) {
    $j=json_decode(file_get_contents($meta),true);
    $row=$j['val']??null;
    echo "exp=".($j['exp']??0)." left=".(($j['exp']??0)-time())."\n";
  }
  if (!is_array($row)) {
    // try DB via mysqli from ewcfg
    include_once "$doc/ewcfg.php";
    // PHPMaker constants
    $host=defined('EW_CONN_HOST')?EW_CONN_HOST:(defined('EWR_CONN_HOST')?EWR_CONN_HOST:'');
    $user=defined('EW_CONN_USER')?EW_CONN_USER:'';
    $pass=defined('EW_CONN_PASS')?EW_CONN_PASS:'';
    $db=defined('EW_CONN_DB')?EW_CONN_DB:'';
    if ($host==='') {
      // try parse
      echo "no EW_CONN\n";
    } else {
      $m=@new mysqli($host,$user,$pass,$db);
      if ($m && !$m->connect_error) {
        $q=$m->query("SELECT uuid,file_name,url,folder,file_size FROM bp_file_manager WHERE uuid='".$m->real_escape_string($uuid)."' LIMIT 1");
        $row=$q?$q->fetch_assoc():null;
        echo "db_row=".($row?json_encode($row,JSON_UNESCAPED_UNICODE):'NULL')."\n";
      } else echo "db_fail\n";
    }
  } else {
    echo "row_file=".($row['file_name']??'')." url=".($row['url']??'')."\n";
  }
  if (is_array($row)) {
    $rel=($row['url']??'').'/'.($row['file_name']??'');
    $abs=realpath($sf).'/'.$rel;
    // also try from docroot
    $cands=[
      $rel,
      "$sf/$rel",
      "$doc/uploads/".ltrim(preg_replace('#^\.\./uploads/#','',$rel),'/'),
      "$doc/".ltrim(str_replace('../','',$rel),'/'),
    ];
    foreach ($cands as $i=>$p) {
      $ok=is_file($p);
      echo "cand$i ok=".($ok?'Y':'N')." p=$p".($ok?(' sz='.filesize($p)):'') ."\n";
    }
    // search filename
    $fn=$row['file_name']??'';
    if ($fn) {
      $found=trim(shell_exec('timeout 5 find '.$doc.'/uploads -name '.escapeshellarg($fn).' 2>/dev/null | head -5'));
      echo "find=$found\n";
    }
  }
}

# local httpd request via Host header to real IP of sitefile
echo "--- local curl ---\n";
echo trim(shell_exec('curl -sk --max-time 8 -o /tmp/_t.bin -w "code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer} total=%{time_total}\n" -H "Host: sitefile.bahman.ir" "https://172.20.14.76/thumb/61bb1145-d84f-11f1-b563-005056b972d8/600"'))."\n";
echo trim(shell_exec('curl -sk --max-time 8 -o /tmp/_t2.bin -w "code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer} total=%{time_total}\n" -H "Host: sitefile.bahman.ir" "https://172.20.14.76/61bb1145-d84f-11f1-b563-005056b972d8"'))."\n";
echo trim(shell_exec('curl -sk --max-time 8 -o /tmp/_t3.bin -w "code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer} total=%{time_total}\n" -H "Host: sitefile.bahman.ir" "https://172.20.14.76/thumb/bd7896ca-c799-11f1-9600-005056b972d8/1920"'))."\n";
echo "fpm_busy=".trim(shell_exec('ps -eo stat,cmd | grep "[p]hp-fpm: pool sitefile" | awk \'{print $1}\' | sort | uniq -c'))."\n";
echo "DONE\n";
