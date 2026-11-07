<?php
$fm = '/usr/local/psa/admin/bin/filemng';
$resDir = dirname(__DIR__) . '/resources';
$doc = '/var/www/vhosts/bahman.ir/httpdocs';
$user = trim((string) shell_exec("stat -c '%U' {$doc}/index.php 2>/dev/null"));
echo "user=$user\n";
$src = $resDir . '/index.php';
$dst = $doc . '/index.php';
$tmp = '/tmp/index.php.deploy';
copy($src, $tmp);
echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' cp ' . escapeshellarg($tmp) . ' ' . escapeshellarg($dst) . ' 2>&1');
echo 'index_has_tmp_cache=' . (strpos((string) file_get_contents($dst), 'bahman_sales_page.html') !== false ? 'yes' : 'no') . "\n";
@unlink('/tmp/bahman_sales_page.html');

$out = [];
for ($i = 0; $i < 5; $i++) {
    $hdr = tempnam('/tmp', 'sh');
    $ttfb = trim((string) shell_exec('curl -sk -D ' . escapeshellarg($hdr) . ' -o /tmp/sales_body.html -w "%{time_starttransfer}" --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/sales"'));
    $xh = trim((string) shell_exec('grep -iE "X-Bahman-Cache|HTTP/" ' . escapeshellarg($hdr) . ' | tr "\n" " "'));
    $cacheExists = is_file('/tmp/bahman_sales_page.html') ? ('yes:' . filesize('/tmp/bahman_sales_page.html')) : 'no';
    $out[] = "pass$i ttfb=$ttfb cachefile=$cacheExists hdr=$xh";
}
$html = @file_get_contents('/tmp/sales_body.html') ?: '';
$out[] = 'title=' . (preg_match('/<title[^>]*>(.*?)<\\/title>/is', $html, $t) ? trim(strip_tags($t[1])) : '');
$out[] = 'thumbs600=' . preg_match_all('#/thumb/[^"\']+/600#', $html);
$out[] = 'tmp_ls=' . trim((string) shell_exec('ls -la /tmp/bahman_sales_page.html 2>&1'));
$out[] = 'sys_tmp=' . sys_get_temp_dir();
$out[] = 'load=' . trim(@file_get_contents('/proc/loadavg'));
echo implode("\n", $out) . "\n";
