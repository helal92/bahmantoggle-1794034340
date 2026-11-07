<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$doc='/var/www/vhosts/bahman.ir/httpdocs';
$user=trim((string)shell_exec("stat -c '%U' {$doc}/index.php 2>/dev/null"));
echo "user=$user\n";
$map=[
  $res.'/index.php'=>$doc.'/index.php',
  $res.'/htaccess'=>$doc.'/.htaccess',
  $res.'/config.php'=>$doc.'/templates/bahman/config.php',
  $res.'/index.tpl'=>$doc.'/templates/bahman/index.tpl',
  $res.'/includes/footer_include.tpl'=>$doc.'/templates/bahman/includes/footer_include.tpl',
  $res.'/includes/header.tpl'=>$doc.'/templates/bahman/includes/header.tpl',
  $res.'/assets/home-product-slider.js'=>$doc.'/templates/bahman/assets/home-product-slider.js',
  $res.'/assets/product-menu-lazy.js'=>$doc.'/templates/bahman/assets/product-menu-lazy.js',
  $res.'/product_menu_fragment.tpl'=>$doc.'/templates/bahman/product_menu_fragment.tpl',
];
foreach($map as $src=>$dst){
  if(!is_file($src)){echo "MISSING $src\n";continue;}
  $tmp='/tmp/hld_'.md5($dst);
  copy($src,$tmp);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dst).' 2>&1');
  echo "ok $dst\n";
}
// bust old caches by name (v3)
@unlink('/tmp/bahman_home_page_v3.html');
@unlink('/tmp/bahman_sales_page_v3.html');

function hit($path){
  $hdr=tempnam('/tmp','h');
  $t=trim((string)shell_exec('curl -sk -D '.escapeshellarg($hdr).' -o /tmp/home_body.html -w "%{time_starttransfer}" --max-time 45 -H "Host: bahman.ir" "https://172.20.14.76'.$path.'"'));
  $xh=trim((string)shell_exec('grep -i X-Bahman-Cache '.escapeshellarg($hdr).' | head -1'));
  $html=(string)@file_get_contents('/tmp/home_body.html');
  return [
    'path'=>$path,'ttfb'=>$t,'size'=>strlen($html),'xh'=>$xh,
    'sitefile'=>preg_match_all('#sitefile\\.bahman\\.ir#',$html),
    'car_img'=>preg_match_all('#class="image"#',$html),
    'home_slider'=>(strpos($html,'home-product-slider')!==false),
    'home_js'=>(strpos($html,'home-product-slider.js')!==false),
    'title'=>(preg_match('/<title[^>]*>(.*?)<\\/title>/is',$html,$m)?trim(strip_tags($m[1])):''),
    'complete'=>(strpos($html,'</html>')!==false),
  ];
}
echo json_encode(hit('/'), JSON_UNESCAPED_UNICODE)."\n";
echo json_encode(hit('/'), JSON_UNESCAPED_UNICODE)."\n";
echo json_encode(hit('/product-menu.json'), JSON_UNESCAPED_UNICODE)."\n";
$j=@file_get_contents('/tmp/home_body.html');
$jd=@json_decode($j,true);
echo 'json_cats='.(is_array($jd)&&isset($jd['categories'])?count($jd['categories']):'fail')."\n";
echo 'load='.trim(@file_get_contents('/proc/loadavg'))."\n";
