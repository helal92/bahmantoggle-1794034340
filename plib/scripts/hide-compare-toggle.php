<?php
$fm = "/opt/psa/admin/bin/filemng";
$user = "bmcars.bahman.ir_vzb4o368qoo";
$dest = "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/product-compare.css";
$css = file_get_contents($dest);
if ($css === false) {
  echo "READ_FAIL\n";
  exit(1);
}
$old = ".product-compare-toggle {\n  display: inline-flex;";
$new = ".product-compare-toggle {\n  display: none !important;";
if (strpos($css, "display: none !important;\n  align-items: center;\n  justify-content: center;\n  width: 40px;") !== false
    && strpos($css, ".product-compare-toggle {\n  display: none !important;") !== false) {
  echo "ALREADY_HIDDEN\n";
  echo "DONE\n";
  exit(0);
}
if (strpos($css, $old) === false) {
  // fallback: any display value on toggle block start
  $patched = preg_replace(
    '/(\.product-compare-toggle\s*\{\s*)display\s*:\s*[^;]+;/',
    '$1display: none !important;',
    $css,
    1,
    $count
  );
  if (!$count) {
    echo "MISS\n";
    $p = strpos($css, '.product-compare-toggle');
    echo substr($css, max(0, $p), 200) . "\n";
    exit(1);
  }
} else {
  $patched = str_replace($old, $new, $css);
}
$tmp = "/tmp/_product_compare_css_hide";
file_put_contents($tmp, $patched);
$out = trim((string)shell_exec($fm . " " . escapeshellarg($user) . " cp " . escapeshellarg($tmp) . " " . escapeshellarg($dest) . " 2>&1"));
@unlink($tmp);
$verify = file_get_contents($dest);
$ok = (strpos($verify, ".product-compare-toggle {\n  display: none !important;") !== false)
  || (preg_match('/\.product-compare-toggle\s*\{\s*display\s*:\s*none\s*!important;/', $verify) === 1);
echo "out=$out ok=" . ($ok ? "1" : "0") . "\n";
echo "DONE\n";
