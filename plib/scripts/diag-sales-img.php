<?php
function ascii(){return preg_replace('/[^\x20-\x7E\n]/','?',(string));}
='33676ad6-9ef6-11f1-9b58-005056b972d8';
=[
  "/var/www/vhosts/bahman.ir/httpdocs/sales-img/{}_600.png",
  "/var/www/vhosts/bahman.ir/httpdocs/sales-img",
  "/var/www/vhosts/sitefile.bahman.ir/httpdocs",
];
foreach ( as ) {
  echo ascii(trim(shell_exec('ls -la '.escapeshellarg().' 2>&1 | head -5')))."\n";
}
echo "count_sales_img=".trim(shell_exec('ls /var/www/vhosts/bahman.ir/httpdocs/sales-img 2>/dev/null | wc -l'))."\n";
echo "sample=".ascii(trim(shell_exec('ls /var/www/vhosts/bahman.ir/httpdocs/sales-img 2>/dev/null | head -5')))."\n";
echo "curl_local=".trim(shell_exec('curl -s -o /dev/null -w "%{http_code} %{size_download}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'..'_600.png"'))."\n";
echo "curl_thumb=".trim(shell_exec('curl -s -o /dev/null -w "%{http_code} %{size_download}" --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/thumb/'..'/600"'))."\n";
echo "curl_file=".trim(shell_exec('curl -sI --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/'..'" | tr -d "\r" | head -12'))."\n";
// find how sales-img is generated
echo "grep_sales:\n".ascii(trim(shell_exec('grep -Rnl "sales-img" /var/www/vhosts/bahman.ir/httpdocs --include="*.php" --include="*.js" --include="*.htaccess" --include="*.tpl" 2>/dev/null | head -20')))."\n";
echo "DONE\n";
