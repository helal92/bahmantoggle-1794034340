<?php
echo "=== DISK /var ===\n";
echo trim(shell_exec("df -h /var /tmp /var/www /var/log 2>/dev/null"))."\n";
echo "du top /var:\n".trim(shell_exec("du -xh --max-depth=2 /var 2>/dev/null | sort -hr | head -25"))."\n";
echo "du /tmp:\n".trim(shell_exec("du -xh --max-depth=1 /tmp 2>/dev/null | sort -hr | head -15"))."\n";
echo "du sitefile cache:\n".trim(shell_exec("du -sh /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/cache 2>/dev/null; ls /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/cache 2>/dev/null | wc -l"))."\n";
echo "du uploads:\n".trim(shell_exec("du -sh /var/www/vhosts/siteapi.bahman.ir/httpdocs/uploads 2>/dev/null"))."\n";
echo "du logs:\n".trim(shell_exec("du -sh /var/log /var/www/vhosts/system/*/logs 2>/dev/null | sort -hr | head -20"))."\n";

echo "\n=== WHY HOME SLOW ===\n";
// warm and time home with cache miss/hit
@unlink('/tmp/bahman_home_page_v4.html');
foreach (glob('/tmp/bahman_home*')?:[] as $p) @unlink($p);
$t0=microtime(true);
$html=shell_exec('curl -sk --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/?warm='.time().'"');
echo "home_miss len=".strlen($html)." dt=".round(microtime(true)-$t0,3)."\n";
echo "cache_after:\n";
foreach (glob('/tmp/bahman_home*')?:[] as $p) echo basename($p)." ".filesize($p)."\n";
$t0=microtime(true);
$html2=shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/?hit='.time().'"');
echo "home_2nd len=".strlen($html2)." dt=".round(microtime(true)-$t0,3)."\n";

echo "\n=== LB CONNECTIONS sample ===\n";
echo "syn_by_age sample:\n".trim(shell_exec("ss -antoe state syn-recv 2>/dev/null | head -8"))."\n";
echo "estab to :80 from lb count=".trim(shell_exec("ss -ant state established '( sport = :80 )' 2>/dev/null | grep -c 10.18.8.2 || true"))."\n";
echo "estab to :443 from lb count=".trim(shell_exec("ss -ant state established '( sport = :443 )' 2>/dev/null | grep -c 10.18.8.2 || true"))."\n";
echo "nproc=".trim(shell_exec('nproc'))."\n";

echo "\n=== APACHE CPU hint ===\n";
// what are workers doing - open files / status
echo trim(shell_exec("apache2ctl status 2>&1 | head -40"))."\n";
echo "server-status?\n".trim(shell_exec('curl -sk --max-time 5 -H "Host: bahman.ir" "https://172.20.14.76/server-status?auto" 2>&1 | head -30'))."\n";

echo "DONE\n";
