<?php
$fm = '/usr/local/psa/admin/bin/filemng';
$res = dirname(__DIR__) . '/resources';
$doc = '/var/www/vhosts/bahman.ir/httpdocs';
$user = trim((string) shell_exec("stat -c '%U' {$doc}/index.php 2>/dev/null"));
$tmp = '/tmp/pmd_index.php';
copy($res . '/index.php', $tmp);
echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' cp ' . escapeshellarg($tmp) . ' ' . escapeshellarg($doc . '/index.php') . ' 2>&1');
echo 'index_v2=' . (strpos((string) file_get_contents($doc . '/index.php'), 'bahman_sales_page_v2') !== false ? 'yes' : 'no') . "\n";

function hit($path){
  $hdr=tempnam('/tmp','h');
  $t=trim((string)shell_exec('curl -sk -D '.escapeshellarg($hdr).' -o /tmp/pm_body.html -w "%{time_starttransfer}" --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76'.$path.'"'));
  $xh=trim((string)shell_exec('grep -i X-Bahman-Cache '.escapeshellarg($hdr).' | head -1'));
  $html=(string)@file_get_contents('/tmp/pm_body.html');
  return json_encode([
    'path'=>$path,'ttfb'=>$t,'size'=>strlen($html),'xh'=>$xh,
    'root'=>(strpos($html,'product-menu-root')!==false),
    'lazy'=>(strpos($html,'product-menu-lazy.js')!==false),
    'cars'=>preg_match_all('#class="car"#',$html),
    'thumbs300'=>preg_match_all('#/thumb/[^"\']+/300#',$html),
  ], JSON_UNESCAPED_UNICODE);
}
echo hit('/sales')."\n";
echo hit('/sales')."\n";
echo hit('/product-menu')."\n";
echo hit('/product-menu')."\n";
echo hit('/')."\n";
echo 'load='.trim(@file_get_contents('/proc/loadavg'))."\n";
