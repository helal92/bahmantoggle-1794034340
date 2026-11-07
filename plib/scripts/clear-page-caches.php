<?php
foreach ([
  '/tmp/bahman_sales_page.html',
  '/tmp/bahman_product_menu_fragment.html',
  '/tmp/bahman_product_category_tree.cache',
  '/tmp/bahman_sales_page_data.cache',
] as $f) {
  @unlink($f);
  echo "rm $f exists=".(is_file($f)?'yes':'no')."\n";
}
# also clear php-fpm private tmp copies if accessible
echo shell_exec('find /tmp -maxdepth 2 -name "bahman_sales_page.html" -o -name "bahman_product_menu_fragment.html" 2>/dev/null | head');
# restart bahman php-fpm to drop PrivateTmp caches
echo shell_exec('plesk bin php_handler --reread 2>&1 | tail -2');
# find pool and reload
echo shell_exec('systemctl reload plesk-php74-fpm 2>&1; systemctl reload plesk-php80-fpm 2>&1; systemctl reload plesk-php81-fpm 2>&1; systemctl reload plesk-php82-fpm 2>&1; echo reload_done');
# measure without cache
function hit($path){
  $hdr=tempnam('/tmp','h');
  $t=trim((string)shell_exec('curl -sk -D '.escapeshellarg($hdr).' -o /tmp/pm_body.html -w "%{time_starttransfer}" --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76'.$path.'"'));
  $xh=trim((string)shell_exec('grep -i X-Bahman-Cache '.escapeshellarg($hdr).' | head -1'));
  $html=(string)@file_get_contents('/tmp/pm_body.html');
  return [
    'path'=>$path,'ttfb'=>$t,'size'=>strlen($html),'xh'=>$xh,
    'root'=>(strpos($html,'product-menu-root')!==false),'lazy'=>(strpos($html,'product-menu-lazy.js')!==false),
    'cars'=>preg_match_all('#class="car"#',$html),'thumbs300'=>preg_match_all('#/thumb/[^"\']+/300#',$html),
    'title'=>(preg_match('/<title[^>]*>(.*?)<\\/title>/is',$html,$m)?trim(strip_tags($m[1])):'')
  ];
}
foreach (['/sales','/sales','/product-menu','/product-menu','/'] as $p) {
  $r=hit($p);
  echo json_encode($r, JSON_UNESCAPED_UNICODE)."\n";
}
