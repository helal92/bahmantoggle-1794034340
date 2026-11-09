<?php
function sh($c){return trim((string)shell_exec($c));}
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$idx='/var/www/vhosts/bahman.ir/httpdocs/index.php';
echo "tpl_count_line:\n";
foreach (file($tpl) as $i=>$l) if (strpos($l,'inactive_count')!==false || strpos($l,'persian_digits')!==false) echo ($i+1).": ".$l;
echo "php -l index:\n".sh('php -l '.escapeshellarg($idx).' 2>&1')."\n";
echo "php -l config:\n".sh('php -l '.escapeshellarg('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php').' 2>&1')."\n";

// show register block
$c=file_get_contents($idx);
$p=strpos($c,"persian_digits");
echo "reg_block=\n".substr($c,max(0,$p-180),420)."\n---\n";

// fetch and dump raw bytes around count
$raw=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/sales?d='.time().'"');
echo "page_len=".strlen($raw)."\n";
$p=strpos($raw,'sales-inactive-company__count');
echo "pos=$p\n";
if ($p!==false) {
  $sn=substr($raw,$p,200);
  echo "snip_repr=".json_encode($sn, JSON_UNESCAPED_UNICODE)."\n";
}
// check if whole page is escaped tpl
echo "has_literal_smarty=".(strpos($raw,'{$company')!==false?'1':'0')."\n";
echo "has_persian_digit_char=".(preg_match('/[۰-۹]/u',$raw)?'1':'0')."\n";
echo "xcache=".sh('curl -sI --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales?d='.time().'" | tr -d "\\r" | grep -i Bahman')."\n";

// find page cache files
echo "cache_files=\n".sh('find /tmp /var/tmp /var/www/vhosts/bahman.ir -name "*sales*" 2>/dev/null | head -40')."\n";
if (preg_match('/\$bahmanPageCacheFile\s*=\s*[^;]+;/',$c,$m)) echo "cache_assign=".$m[0]."\n";
// broader
if (preg_match('/bahmanPageCacheFile.{0,300}/s',$c,$m)) echo "cache_ctx=".preg_replace('/\s+/',' ',$m[0])."\n";
echo "DONE\n";
