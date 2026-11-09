<?php
function sh($c){return trim((string)shell_exec($c));}
$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
$dir='/var/www/vhosts/bahman.ir/httpdocs/sales-img';
echo "dir_exists=".(is_dir($dir)?'yes':'no')."\n";
echo "count=".sh('ls '.escapeshellarg($dir).' 2>/dev/null | wc -l')."\n";
echo "file=".sh('ls -la '.escapeshellarg($dir.'/'.$uuid.'_600.png').' 2>&1')."\n";
echo "sample=".sh('ls '.escapeshellarg($dir).' 2>/dev/null | head -8')."\n";
echo "curl_sales=".sh('curl -s -o /dev/null -w "%{http_code} %{size_download}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$uuid.'_600.png"')."\n";
echo "curl_thumb=".sh('curl -s -o /dev/null -w "%{http_code} %{size_download} %{content_type}" --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/thumb/'.$uuid.'/600"')."\n";
echo "curl_raw=".sh('curl -sI --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/'.$uuid.'" | tr -d "\r" | head -15')."\n";
echo "grep=\n".sh('grep -Rnl "sales-img" /var/www/vhosts/bahman.ir/httpdocs --include="*.php" --include="*.js" --include="*.tpl" --include="*.htaccess" 2>/dev/null | head -30')."\n";
echo "htaccess=\n".sh('grep -n "sales-img\|sales_img" /var/www/vhosts/bahman.ir/httpdocs/.htaccess 2>/dev/null')."\n";
echo "DONE\n";
