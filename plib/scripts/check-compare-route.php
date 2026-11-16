<?php
$files = [
  "/var/www/vhosts/bahman.ir/httpdocs/.htaccess",
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php",
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/product-compare.js",
  "/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/includes/footer_include.tpl",
];
foreach ($files as $f) {
  echo "==== $f ====
";
  echo "exists=" . (is_file($f)?"1":"0") . " size=" . (is_file($f)?filesize($f):0) . "
";
  if (!is_file($f)) continue;
  $c = file_get_contents($f);
  echo "has_product_compare_json=" . (strpos($c,"product_compare_json")!==false?"1":"0") . "
";
  echo "has_product-compare-data=" . (strpos($c,"product-compare-data")!==false?"1":"0") . "
";
  echo "has_product-compare.js=" . (strpos($c,"product-compare.js")!==false?"1":"0") . "
";
  if (strpos($f,".htaccess")!==false) {
    if (preg_match("/product-compare[^\n]*/",$c,$m)) echo "rule=$m[0]
";
    else echo "rule=MISSING
";
  }
}
echo "DONE
";
