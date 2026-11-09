<?php
function sh($c){return trim((string)shell_exec($c));}
echo "scripts=\n".sh('ls -la /var/www/vhosts/bahman.ir/httpdocs/scripts /var/www/vhosts/bahman.ir/httpdocs/bin /var/www/vhosts/bahman.ir/httpdocs/tools 2>&1 | head -40')."\n";
echo "find_gen=\n".sh('find /var/www/vhosts/bahman.ir/httpdocs -maxdepth 3 \( -name "*sales*" -o -name "*crop*" -o -name "*static*img*" \) 2>/dev/null | head -40')."\n";
echo "sales_tpl=\n".sh('grep -n "sales-img\|thumb\|image\|banner" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl | head -40')."\n";
echo "home_js=\n".sh('grep -Rnl "sales-img" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets 2>/dev/null | head')."\n";
echo "config_sales=\n".sh('grep -n "sales-img\|sales_img\|directive\|active.sale\|setType..directive" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php | head -40')."\n";
echo "index_tpl=\n".sh('grep -n "sales-img\|sales_img" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/index.tpl /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/*.js 2>/dev/null | head -40')."\n";
echo "DONE\n";
