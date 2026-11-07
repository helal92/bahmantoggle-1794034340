<?php
$bh='/var/www/vhosts/bahman.ir/httpdocs';
echo "=== sales.tpl css links ===\n";
echo shell_exec("grep -nE 'stylesheet|product-tabs|slider' $bh/templates/bahman/sales.tpl | head -40");
echo "\n=== footer sales scripts ===\n";
echo shell_exec("grep -nE 'sales|slider_car|product-tabs|include eq .sales' $bh/templates/bahman/includes/footer_include.tpl | head -40");
echo "\n=== product-tabs.css image rules ===\n";
echo shell_exec("sed -n '180,250p' $bh/templates/bahman/assets/product-tabs.css");
echo "\n=== products-grid image rules ===\n";
echo shell_exec("grep -nE 'object-fit|\\.image|Item|swiper' $bh/templates/bahman/assets/products-grid.css | head -40");
echo "\n=== backup sales.tpl / header ===\n";
echo shell_exec("grep -n product-tabs $bh/templates/bahman/sales.tpl.pre_sales_fast $bh/templates/bahman/includes/header_include.tpl 2>/dev/null | head");
// Was product-tabs ever on sales branch historically?
echo shell_exec("grep -n product-tabs $bh/templates/bahman/base_template/sales-bahman.html 2>/dev/null | head");
echo shell_exec("grep -Rn product-tabs.css $bh/templates/bahman --include='*.tpl' --include='*.html' 2>/dev/null | head");
