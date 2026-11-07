<?php
$out=[];
$out[]='=== SALES '.date('c').' ===';
$out[]='load='.trim(@file_get_contents('/proc/loadavg'));
$out[]='flag='.(is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE')?'ON':'OFF');
$out[]='fpm_sitefile='.trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'));
$out[]='fpm_bahman='.trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman.ir" | wc -l'));
foreach (['/sales','/index.php?include=sales','/'] as $path) {
  $times=[];
  for ($i=0;$i<3;$i++) {
    $times[]=trim((string)shell_exec('curl -sk -o /tmp/sales_body.html -w "%{time_starttransfer}" --max-time 90 -H "Host: bahman.ir" "https://172.20.14.76'.$path.'"'));
  }
  $out[]=$path.' ttfb=['.implode(',', $times).'] size='.filesize('/tmp/sales_body.html');
}
$html=@file_get_contents('/tmp/sales_body.html') ?: '';
preg_match_all('#https?://sitefile\\.bahman\\.ir[^"\'\\s<>]+#', $html, $m);
$out[]='sitefile_urls='.count($m[0]);
$out[]='thumb_urls='.count(array_filter($m[0], fn($u)=>str_contains($u,'/thumb/')));
$out[]='title='.(preg_match('/<title[^>]*>(.*?)<\\/title>/is',$html,$t)?trim(strip_tags($t[1])):'');
$out[]='has_fatal='.(preg_match('/Fatal error|Allowed memory|Maximum execution/i',$html)?'yes':'no');
$out[]='--- access /sales last ---';
$out[]=trim((string)shell_exec('grep -E "\"GET /sales|include=sales" /var/www/vhosts/system/bahman.ir/logs/access_log 2>/dev/null | tail -20'));
$out[]='--- php slow? strace skip ---';
// Warm: second pass after first
$out[]='warm sales '.trim((string)shell_exec('curl -sk -o /dev/null -w "ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code}" --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/sales"'));
echo implode("\n",$out)."\n";
