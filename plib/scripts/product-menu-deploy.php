<?php
$fm = '/usr/local/psa/admin/bin/filemng';
$res = dirname(__DIR__) . '/resources';
$doc = '/var/www/vhosts/bahman.ir/httpdocs';
$user = trim((string) shell_exec("stat -c '%U' {$doc}/index.php 2>/dev/null"));
echo "user=$user\n";
$map = [
    $res . '/index.php' => $doc . '/index.php',
    $res . '/htaccess' => $doc . '/.htaccess',
    $res . '/config.php' => $doc . '/templates/bahman/config.php',
    $res . '/product_menu_fragment.tpl' => $doc . '/templates/bahman/product_menu_fragment.tpl',
    $res . '/includes/header.tpl' => $doc . '/templates/bahman/includes/header.tpl',
    $res . '/includes/footer_include.tpl' => $doc . '/templates/bahman/includes/footer_include.tpl',
    $res . '/assets/product-menu-lazy.js' => $doc . '/templates/bahman/assets/product-menu-lazy.js',
];
foreach ($map as $src => $dst) {
    if (!is_file($src)) { echo "MISSING $src\n"; continue; }
    $tmp = '/tmp/pmd_' . md5($dst);
    copy($src, $tmp);
    $dir = dirname($dst);
    if (!is_dir($dir)) {
        echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' mkdir ' . escapeshellarg($dir) . ' 2>&1');
    }
    echo shell_exec(escapeshellcmd($fm) . ' ' . escapeshellarg($user) . ' cp ' . escapeshellarg($tmp) . ' ' . escapeshellarg($dst) . ' 2>&1');
    echo 'ok ' . $dst . ' ' . filesize($dst) . "\n";
}
@unlink('/tmp/bahman_product_menu_fragment.html');
@unlink('/tmp/bahman_product_category_tree.cache');

$checks = [];
$checks[] = 'htaccess=' . (strpos((string)@file_get_contents($doc.'/.htaccess'), 'product-menu') !== false ? 'yes' : 'no');
$checks[] = 'header_lazy=' . (strpos((string)@file_get_contents($doc.'/templates/bahman/includes/header.tpl'), 'data-product-menu-url') !== false ? 'yes' : 'no');
$checks[] = 'js=' . (is_file($doc.'/templates/bahman/assets/product-menu-lazy.js') ? 'yes' : 'no');
$checks[] = 'fragment=' . (is_file($doc.'/templates/bahman/product_menu_fragment.tpl') ? 'yes' : 'no');
echo implode(' ', $checks) . "\n";

// measure: sales page should NOT contain many product thumbs in HTML; product-menu should
function ttfb($path) {
    $hdr = tempnam('/tmp', 'h');
    $t = trim((string) shell_exec('curl -sk -D ' . escapeshellarg($hdr) . ' -o /tmp/pm_body.html -w "%{time_starttransfer}" --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76' . $path . '"'));
    $xh = trim((string) shell_exec('grep -i X-Bahman-Cache ' . escapeshellarg($hdr) . ' | head -1'));
    $size = filesize('/tmp/pm_body.html');
    $thumbs = preg_match_all('#/thumb/[^"\']+/300#', (string)@file_get_contents('/tmp/pm_body.html'));
    return "path=$path ttfb=$t size=$size thumbs300=$thumbs $xh";
}
echo ttfb('/sales') . "\n";
echo ttfb('/product-menu') . "\n";
echo ttfb('/product-menu') . "\n";
echo ttfb('/sales') . "\n";
$html = (string)@file_get_contents('/tmp/pm_body.html');
echo 'sales_has_product_menu_root=' . (strpos($html, 'product-menu-root') !== false ? 'yes' : 'no') . "\n";
echo 'sales_has_lazy_js=' . (strpos($html, 'product-menu-lazy.js') !== false ? 'yes' : 'no') . "\n";
echo 'sales_embedded_cars=' . (preg_match_all('#class="car"#', $html)) . "\n";
echo 'load=' . trim(@file_get_contents('/proc/loadavg')) . "\n";
