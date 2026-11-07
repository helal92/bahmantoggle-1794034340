<?php
$fm = '/usr/local/psa/admin/bin/filemng';
$resDir = dirname(__DIR__) . '/resources';
$doc = '/var/www/vhosts/bahman.ir/httpdocs';
$user = trim((string) shell_exec("stat -c '%U' {$doc}/index.php 2>/dev/null"));
if ($user === '') {
    echo "ERROR: cannot detect bahman system user\n";
    exit(1);
}
echo "user=$user\n";

$map = [
    $resDir . '/index.php' => $doc . '/index.php',
    $resDir . '/sales.tpl' => $doc . '/templates/bahman/sales.tpl',
    $resDir . '/config.php' => $doc . '/templates/bahman/config.php',
];
foreach ($map as $src => $dst) {
    if (!is_file($src)) {
        echo "MISSING $src\n";
        continue;
    }
    if (is_file($dst) && !is_file($dst . '.pre_sales_fast')) {
        echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' cp ' . escapeshellarg($dst) . ' ' . escapeshellarg($dst . '.pre_sales_fast') . ' 2>&1');
    }
    $tmp = '/tmp/' . basename($src) . '.deploy';
    copy($src, $tmp);
    echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' cp ' . escapeshellarg($tmp) . ' ' . escapeshellarg($dst) . ' 2>&1');
    echo "deployed $dst size=" . filesize($dst) . "\n";
}

file_put_contents('/tmp/FAST_MODE.flag', "on\n" . date('c') . "\n");
$apiUser = 'bmcars-api.bahman.ir_ghgap00458p';
$flag = '/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE';
echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($apiUser) . ' cp ' . escapeshellarg('/tmp/FAST_MODE.flag') . ' ' . escapeshellarg($flag) . ' 2>&1');
echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' cp ' . escapeshellarg('/tmp/FAST_MODE.flag') . ' ' . escapeshellarg($doc . '/.FAST_MODE') . ' 2>&1');

$pagesDir = $doc . '/cache/pages';
if (!is_dir($pagesDir)) {
    mkdir($pagesDir, 0755, true);
}
@chown($pagesDir, $user);
@unlink('/tmp/bahman_sales_page_data.cache');
$cache = $doc . '/cache/pages/sales.html';
if (is_file($cache)) {
    @unlink($cache);
}

$indexPhp = (string) @file_get_contents($doc . '/index.php');
$salesTpl = (string) @file_get_contents($doc . '/templates/bahman/sales.tpl');
echo 'grep_cache=' . (strpos($indexPhp, 'X-Bahman-Cache') !== false ? 'yes' : 'no') . "\n";
echo 'grep_thumb600=' . (strpos($salesTpl, '/600') !== false ? 'yes' : 'no') . "\n";
echo 'flags sitefile=' . (is_file($flag) ? 'ON' : 'OFF') . ' bahman=' . (is_file($doc . '/.FAST_MODE') ? 'ON' : 'OFF') . "\n";

$out = [];
for ($i = 0; $i < 4; $i++) {
    $hdr = tempnam('/tmp', 'sh');
    $ttfb = trim((string) shell_exec('curl -sk -D ' . escapeshellarg($hdr) . ' -o /tmp/sales_body.html -w "%{time_starttransfer}" --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/sales"'));
    $xh = trim((string) shell_exec('grep -i X-Bahman-Cache ' . escapeshellarg($hdr) . ' | head -1'));
    $out[] = "pass$i ttfb=$ttfb $xh size=" . filesize('/tmp/sales_body.html');
}
$html = @file_get_contents('/tmp/sales_body.html') ?: '';
$title = preg_match('/<title[^>]*>(.*?)<\\/title>/is', $html, $t) ? trim(strip_tags($t[1])) : '';
$out[] = 'title=' . $title;
$out[] = 'thumbs600=' . preg_match_all('#/thumb/[^"\']+/600#', $html);
$out[] = 'thumbs960=' . preg_match_all('#/thumb/[^"\']+/960#', $html);
$out[] = 'load=' . trim(@file_get_contents('/proc/loadavg'));
echo implode("\n", $out) . "\n";
