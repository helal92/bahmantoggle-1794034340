<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$doc='/var/www/vhosts/bahman.ir/httpdocs';
$user=trim((string)shell_exec("stat -c '%U' {$doc}/index.php 2>/dev/null"));
foreach ([
  $res.'/index.php'=>$doc.'/index.php',
  $res.'/htaccess'=>$doc.'/.htaccess',
  $res.'/index.tpl'=>$doc.'/templates/bahman/index.tpl',
] as $src=>$dst) {
  $tmp='/tmp/hld2_'.md5($dst); copy($src,$tmp);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dst).' 2>&1');
  echo "ok $dst\n";
}
function hit($path){
  $hdr=tempnam('/tmp','h');
  $t=trim((string)shell_exec('curl -sk -D '.escapeshellarg($hdr).' -o /tmp/home_body.html -w "%{time_starttransfer}" --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76'.$path.'"'));
  $xh=trim((string)shell_exec('grep -iE "HTTP/|X-Bahman|Content-Type" '.escapeshellarg($hdr).' | tr "\n" " |"'));
  $html=(string)@file_get_contents('/tmp/home_body.html');
  return compact('path','t','xh')+['size'=>strlen($html),'snippet'=>substr(preg_replace('/\s+/',' ',$html),0,120)];
}
echo json_encode(hit('/'), JSON_UNESCAPED_UNICODE)."\n";
echo json_encode(hit('/'), JSON_UNESCAPED_UNICODE)."\n";
echo json_encode(hit('/product-menu-data'), JSON_UNESCAPED_UNICODE)."\n";
$j=@file_get_contents('/tmp/home_body.html');
$jd=@json_decode($j,true);
echo 'cats='.(is_array($jd)&&isset($jd['categories'])?count($jd['categories']):'no').' raw_start='.substr($j,0,80)."\n";
$html=(string)shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" https://172.20.14.76/');
echo 'home_has_menu_data_url='.(strpos($html,'product-menu-data')!==false?'yes':'no')."\n";
echo 'home_size='.strlen($html).' sitefile='.preg_match_all('#sitefile\\.bahman\\.ir#',$html).' cars='.preg_match_all('#class="image"#',$html)."\n";
