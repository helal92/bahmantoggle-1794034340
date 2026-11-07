<?php
echo "=== HOST ===\n";
echo "date=".trim(shell_exec('date'))."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "uptime=".trim(shell_exec('uptime'))."\n";
echo "mem:\n".trim(shell_exec("free -h | head -3"))."\n";
echo "disk:\n".trim(shell_exec("df -h / /var/www 2>/dev/null | head -5"))."\n";

echo "\n=== NET QUEUE ===\n";
echo "syn_recv=".trim(shell_exec("ss -ant state syn-recv 2>/dev/null | wc -l"))."\n";
echo "listen80=\n".trim(shell_exec("ss -lnt '( sport = :80 or sport = :443 )' 2>/dev/null"))."\n";
echo "estab443=".trim(shell_exec("ss -ant state established '( sport = :443 or sport = :80 )' 2>/dev/null | wc -l"))."\n";
echo "conn_from_lb=".trim(shell_exec("ss -ant | awk '/10\\.18\\.8\\.2/{c++} END{print c+0}'"))."\n";

echo "\n=== PHP-FPM ===\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";
echo "fpm_all=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool" | wc -l'))."\n";
echo "apache=".trim(shell_exec("ps -eo cmd | grep -E '[a]pache2|[h]ttpd' | wc -l"))."\n";
echo "sitefile_max_children=".trim(shell_exec("grep -E 'pm.max_children|pm.start' /opt/plesk/php/*/etc/php-fpm.d/sitefile.bahman.ir.conf 2>/dev/null | head -10"))."\n";
echo "bahman_max_children=".trim(shell_exec("grep -E 'pm.max_children|pm.start' /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null | head -10"))."\n";

echo "\n=== LOCAL TTFB (bypass FortiWeb) ===\n";
foreach (['bahman.ir/'=>'/', 'bahman.ir/sales'=>'/sales', 'bahman.ir/product'=>'/product', 'bahman.ir/product-menu-data'=>'/product-menu-data', 'sitefile thumb'=>'/thumb/e021c087-7f62-11f1-9b58-005056b972d8/600'] as $label=>$path) {
  $host = (strpos($label,'sitefile')!==false) ? 'sitefile.bahman.ir' : 'bahman.ir';
  $url = "https://172.20.14.76$path";
  $w = trim(shell_exec('curl -sk --max-time 30 -o /dev/null -w "code=%{http_code} ttfb=%{time_starttransfer} total=%{time_total} size=%{size_download}" -H '.escapeshellarg("Host: $host").' '.escapeshellarg($url)));
  echo "$label $w\n";
}

echo "\n=== HOME CACHE ===\n";
foreach (glob('/tmp/bahman_home*')?:[] as $p) echo basename($p)." ".filesize($p)." mtime=".date('c', filemtime($p))."\n";
foreach (glob('/tmp/bahman_*sales*')?:[] as $p) echo basename($p)." ".filesize($p)." mtime=".date('c', filemtime($p))."\n";

echo "\n=== RECENT ERRORS (sitefile) ===\n";
$log='/var/www/vhosts/system/sitefile.bahman.ir/logs/error_log';
if (is_file($log)) echo trim(shell_exec('tail -n 25 '.escapeshellarg($log)))."\n";
$log2='/var/www/vhosts/system/bahman.ir/logs/error_log';
if (is_file($log2)) { echo "\n=== bahman error ===\n"; echo trim(shell_exec('tail -n 15 '.escapeshellarg($log2)))."\n"; }

echo "\n=== TOP CPU ===\n";
echo trim(shell_exec("ps -eo pid,pcpu,pmem,rss,cmd --sort=-pcpu | head -15"))."\n";
echo "DONE\n";
