<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$bhUser=trim((string)shell_exec("stat -c '%U' ".escapeshellarg("$bh/index.php")));
$res='/opt/psa/admin/plib/modules/bahmantoggle/resources';
$map=[
  'assets/product-rows.js'=>"$bh/templates/bahman/assets/product-rows.js",
  'assets/product-menu-lazy.js'=>"$bh/templates/bahman/assets/product-menu-lazy.js",
  'assets/product-list.js'=>"$bh/templates/bahman/assets/product-list.js",
  'assets/product_list.css'=>"$bh/templates/bahman/assets/product_list.css",
  'assets/404.css'=>"$bh/templates/bahman/assets/404.css",
  'assets/products-grid.css'=>"$bh/templates/bahman/assets/products-grid.css",
  'product.tpl'=>"$bh/templates/bahman/product.tpl",
  'product_menu_fragment.tpl'=>"$bh/templates/bahman/product_menu_fragment.tpl",
  'includes/footer_include.tpl'=>"$bh/templates/bahman/includes/footer_include.tpl",
];
foreach ($map as $src=>$dst) {
  $from="$res/$src";
  if (!is_file($from)) { echo "MISS $src\n"; continue; }
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg($from).' '.escapeshellarg($dst).' 2>&1');
  echo "ok $src bytes=".filesize($dst)."\n";
}
foreach (glob("$bh/templates_c/*")?:[] as $p) if (is_file($p)) @unlink($p);
foreach (glob('/tmp/bahman_*')?:[] as $p) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');

$product=shell_exec("curl -sk --max-time 40 -H 'Host: bahman.ir' 'https://172.20.14.76/product?x=".time()."'");
echo "product_len=".strlen($product)."\n";
echo "has_rows_js=".(strpos($product,'product-rows.js')!==false?'Y':'N')."\n";
echo "has_data_src=".(strpos($product,'data-src=')!==false?'Y':'N')."\n";
echo "img_src_count=".preg_match_all('#<img[^>]+src="#',$product,$m1)." data_src_count=".preg_match_all('#data-src="#',$product,$m2)."\n";

$menu=shell_exec("curl -sk --max-time 40 -H 'Host: bahman.ir' 'https://172.20.14.76/product-menu?x=".time()."'");
echo "menu_len=".strlen($menu)."\n";
echo "menu_data_src=".(strpos($menu,'data-src=')!==false?'Y':'N')."\n";
echo "menu_img_src=".preg_match_all('#<img[^>]+src="#',$menu,$a)." menu_data_src_n=".preg_match_all('#data-src="#',$menu,$b)."\n";

$home=shell_exec("curl -sk --max-time 30 -H 'Host: bahman.ir' 'https://172.20.14.76/?x=".time()."'");
echo "home_has_rows_js=".(strpos($home,'product-rows.js')!==false?'Y':'N')."\n";
echo "DONE\n";
