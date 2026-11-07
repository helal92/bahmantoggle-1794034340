<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$bhUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg("$bh/index.php")));
$sfUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg("$sf/file.php")));
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources';

@mkdir('/tmp/sales-img',0775,true);
$uuids=[
'61bb1145-d84f-11f1-b563-005056b972d8'=>'png',
'2ec8e505-d74c-11f1-b563-005056b972d8'=>'png',
'340d73a9-d74b-11f1-b563-005056b972d8'=>'png',
'a21fea2c-d6bd-11f1-b563-005056b972d8'=>'png',
'391782e2-d6bd-11f1-b563-005056b972d8'=>'png',
'03945293-d752-11f1-b563-005056b972d8'=>'png',
'bd7896ca-c799-11f1-9600-005056b972d8'=>'jpg',
];
$copied=0;
foreach ($uuids as $u=>$ext) {
  $sizes = ($ext==='jpg') ? ['1920','600','960'] : ['600','960','1920'];
  foreach ($sizes as $sz) {
    $src="$sf/t/{$u}_{$sz}.{$ext}";
    if (!is_file($src)) {
      // warm once
      shell_exec('curl -sk --max-time 20 -o /dev/null -H "Host: sitefile.bahman.ir" '.escapeshellarg("https://172.20.14.76/thumb/$u/$sz"));
    }
    if (!is_file($src)) { echo "missing $src\n"; continue; }
    $dstName="{$u}_{$sz}.{$ext}";
    $tmp="/tmp/sales-img/$dstName";
    copy($src,$tmp);
    @chmod($tmp,0644);
    echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' mkdir '.escapeshellarg("$bh/sales-img").' 2>&1');
    echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($tmp).' '.escapeshellarg("$bh/sales-img/$dstName").' 2>&1');
    if (is_file("$bh/sales-img/$dstName")) { $copied++; echo "ok $dstName ".filesize("$bh/sales-img/$dstName")."\n"; }
    else echo "fail $dstName\n";
  }
}
echo "copied=$copied\n";

// deploy sales.tpl
$tplSrc="$res/sales.tpl";
$tplDst="$bh/templates/bahman/sales.tpl";
if (is_file($tplSrc)) {
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($tplSrc).' '.escapeshellarg($tplDst).' 2>&1');
  echo "sales.tpl deployed has_local=".(strpos(file_get_contents($tplDst),'/sales-img/')!==false?'Y':'N')."\n";
}

// purge smarty + sales html cache
foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p)) @unlink($p);
foreach (glob('/tmp/bahman_*sales*')?:[] as $p) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');

$html=shell_exec("curl -sk --max-time 40 -H 'Host: bahman.ir' 'https://172.20.14.76/sales?x=".time()."'");
echo "sales_len=".strlen($html)."\n";
echo "sales_img_refs=".preg_match_all('#/sales-img/[^"\']+#',$html,$m)."\n";
echo "sitefile_thumb_refs=".preg_match_all('#sitefile\.bahman\.ir/thumb/#',$html,$m2)."\n";
if (!empty($m[0])) echo "sample=".implode(',', array_slice(array_unique($m[0]),0,5))."\n";

// local verify via bahman host
foreach (array_slice(array_keys($uuids),0,3) as $u) {
  $ext=$uuids[$u];
  $path="/sales-img/{$u}_600.".$ext;
  echo "get $path ".trim(shell_exec('curl -sk --max-time 8 -o /dev/null -w "%{http_code} %{size_download} %{time_total}" -H "Host: bahman.ir" '.escapeshellarg("https://172.20.14.76$path")))."\n";
}
echo "DONE\n";
