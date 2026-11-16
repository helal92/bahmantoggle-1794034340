<?php
$fm = "/opt/psa/admin/bin/filemng";
$user = "bmcars.bahman.ir_vzb4o368qoo";
$files = [
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/index.css",
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/404.css",
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/base_template/assets/index.css",
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/base_template/assets/404.css",
];
function patch($s) {
  $count = 0;
  $s2 = preg_replace_callback('/Item>\.image\{[^}]+\}/', function ($m) use (&$count) {
    $block = $m[0];
    if (strpos($block, 'object-fit:cover') === false) {
      return $block;
    }
    $block = str_replace('object-fit:cover', 'object-fit:contain', $block);
    if (strpos($block, 'height:auto') === false) {
      $block = preg_replace('/\{/', '{height:auto!important;', $block, 1);
    }
    $count++;
    return $block;
  }, $s);
  return [$s2, $count];
}
foreach ($files as $dest) {
  if (!is_file($dest)) {
    echo "MISS $dest\n";
    continue;
  }
  $orig = file_get_contents($dest);
  list($patched, $n) = patch($orig);
  if ($n === 0) {
    $already = substr_count($orig, 'object-fit:contain;aspect-ratio:1/.54');
    echo "NOOP $dest already=$already\n";
    continue;
  }
  $tmp = "/tmp/_sales_img_css_" . md5($dest);
  file_put_contents($tmp, $patched);
  $out = trim((string)shell_exec($fm . " " . escapeshellarg($user) . " cp " . escapeshellarg($tmp) . " " . escapeshellarg($dest) . " 2>&1"));
  @unlink($tmp);
  $verify = file_get_contents($dest);
  $ok = (strpos($verify, 'Item>.image{height:auto!important;width:100%!important;object-fit:contain;aspect-ratio:1/.54}') !== false);
  echo "PATCHED $dest n=$n out=$out ok=" . ($ok ? "1" : "0") . "\n";
}
echo "DONE\n";
