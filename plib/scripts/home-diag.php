<?php
echo "=== HOME DIAG ".date('c')." ===\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "apache_busy=".trim(shell_exec('ps -eo cmd | grep -E "[a]pache|[h]ttpd" | wc -l'))."\n";

foreach (['/','/index.php?include=index','/sales','/product-menu'] as $path) {
  $hdr=tempnam('/tmp','h');
  $cmd='curl -sk -D '.escapeshellarg($hdr).' -o /tmp/home_body.html -w "ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code} size:%{size_download}" --max-time 25 -H "Host: bahman.ir" "https://172.20.14.76'.$path.'"';
  $w=trim((string)shell_exec($cmd));
  $xh=trim((string)shell_exec('grep -iE "HTTP/|X-Bahman" '.escapeshellarg($hdr).' | tr "\n" " "'));
  echo "$path $w | $xh\n";
}
$html=@file_get_contents('/tmp/home_body.html') ?: '';
echo "title=".(preg_match('/<title[^>]*>(.*?)<\\/title>/is',$html,$t)?trim(strip_tags($t[1])):'')."\n";
echo "fatal=".(preg_match('/Fatal error|Parse error|Allowed memory/i',$html)?'yes':'no')."\n";
echo "len=".strlen($html)."\n";

echo "--- error_log tail ---\n";
echo trim((string)shell_exec('tail -40 /var/www/vhosts/bahman.ir/logs/error_log 2>/dev/null'))."\n";
echo "--- php-fpm bahman log ---\n";
echo trim((string)shell_exec('ls /var/www/vhosts/system/bahman.ir/logs/ 2>/dev/null'))."\n";
echo trim((string)shell_exec('tail -30 /var/www/vhosts/system/bahman.ir/logs/php-error.log 2>/dev/null; tail -30 /var/www/vhosts/system/bahman.ir/logs/error_log 2>/dev/null | tail -20'))."\n";

echo "--- access last home ---\n";
echo trim((string)shell_exec('grep "GET / HTTP" /var/www/vhosts/system/bahman.ir/logs/access_log 2>/dev/null | tail -15'))."\n";

echo "--- index.php syntax ---\n";
echo trim((string)shell_exec('php -l /var/www/vhosts/bahman.ir/httpdocs/index.php 2>&1'))."\n";
echo trim((string)shell_exec('php -l /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php 2>&1'))."\n";

echo "--- return in config? ---\n";
echo trim((string)shell_exec('grep -n "bahmanSkipSharedNavQueries\|return;" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php | head -20'))."\n";
