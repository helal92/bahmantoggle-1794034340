<?php
$bh='/var/www/vhosts/bahman.ir/httpdocs';
echo "=== sales related assets ===\n";
echo shell_exec("ls -la $bh/templates/bahman/assets/*sales* $bh/templates/bahman/assets/*product* $bh/templates/bahman/assets/slider* 2>/dev/null | head -40");
echo "\n=== sales tpl ===\n";
echo shell_exec("ls $bh/templates/bahman/*sales* $bh/templates/bahman/sales* 2>/dev/null");
echo shell_exec("find $bh/templates/bahman -iname '*sale*' -o -iname '*sales*' 2>/dev/null | head");

// fetch sales page html snippet around product slider
$html=shell_exec("curl -sk --max-time 40 -H 'Host: bahman.ir' 'https://172.20.14.76/sales'");
echo "sales_len=".strlen($html)." fatal=".(stripos($html,'Fatal error')!==false?'Y':'N')."\n";
if (preg_match_all('/<(section|div)[^>]*(product|slider|carousel|category|fidelity)[^>]*>/i',$html,$m)) {
  echo "sections=".count($m[0])."\n";
}
// CSS links on sales
preg_match_all('#<link[^>]+stylesheet[^>]*>#',$html,$m);
foreach ($m[0] as $t) echo "CSS: ".preg_replace('/\s+/',' ',$t)."\n";

// product slider markup sample
if (preg_match('/ProductCategory_Slider[\s\S]{0,1500}/',$html,$m)) echo "SLIDER:\n".$m[0]."\n";
if (preg_match('/home-product-slider|ProductCategory|tab_content[\s\S]{0,800}/',$html,$m)) echo "TAB:\n".$m[0]."\n";

// CSS rules for .Item .image object-fit
foreach (['products-grid.css','product-tabs.css','index.css','index2.css','slider related'] as $f) {}
echo shell_exec("grep -RnE 'object-fit|ProductCategory|\\.Item|\\.image|swiper-slide' $bh/templates/bahman/assets/products-grid.css $bh/templates/bahman/assets/product-tabs.css $bh/templates/bahman/assets/index.css $bh/templates/bahman/assets/index2.css 2>/dev/null | head -60");

echo "\n=== header sales CSS branch ===\n";
echo shell_exec("sed -n '80,140p' $bh/templates/bahman/includes/header_include.tpl");
