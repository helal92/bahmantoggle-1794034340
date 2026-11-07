<?php
echo "=== FIX ".date('c')." ===\n";
echo "load_before=".trim(@file_get_contents('/proc/loadavg'))."\n";

// Kill stuck / restart web stacks (as root via extension)
$cmds = [
  'plesk bin php_handler --reread',
  // graceful apache
  'apachectl graceful 2>&1 || httpd -k graceful 2>&1 || systemctl reload httpd 2>&1 || service apache2 reload 2>&1',
  // try kill -USR2 fpm masters
  'for p in /opt/plesk/php/*/var/run/php-fpm.pid /var/run/php-fpm*.pid; do [ -f "$p" ] && kill -USR2 $(cat "$p") && echo USR2:$p; done',
  // plesk domain php restart if exists
  'plesk sbin phpinimng --generate-site bahman.ir 2>&1 | tail -3',
  'plesk sbin phpinimng --generate-site sitefile.bahman.ir 2>&1 | tail -3',
];
foreach ($cmds as $c) {
  echo "CMD: $c\n";
  echo trim((string)shell_exec($c."\n"))."\n";
}

sleep(2);
echo "load_after=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm_sitefile=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
echo "fpm_bahman=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool bahman" | wc -l'))."\n";

// sample homepage HTML health
$html = (string)shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" https://172.20.14.76/');
echo "home_len=".strlen($html)."\n";
echo "has_body_end=".(strpos($html,'</body>')!==false?'yes':'no')."\n";
echo "has_html_end=".(strpos($html,'</html>')!==false?'yes':'no')."\n";
echo "script_count=".preg_match_all('#<script#i',$html)."\n";
echo "sitefile_count=".preg_match_all('#sitefile\\.bahman\\.ir#',$html)."\n";
echo "product_menu_lazy=".(strpos($html,'product-menu-lazy.js')!==false?'yes':'no')."\n";
echo "title=".(preg_match('/<title[^>]*>(.*?)<\\/title>/is',$html,$t)?trim(strip_tags($t[1])):'')."\n";

// check if homepage still loads FULL product tree (heavy) — count carousel items
echo "homepage_slider_items=".preg_match_all('#ProductCategory_Slider|tab_content_item#',$html)."\n";
echo "homepage_car_imgs=".preg_match_all('#class="image"#',$html)."\n";

// concurrent hammer simulation light
$times=[];
for($i=0;$i<5;$i++){
  $times[]=trim((string)shell_exec('curl -sk -o /dev/null -w "%{time_starttransfer}" --max-time 20 -H "Host: bahman.ir" https://172.20.14.76/'));
}
echo "home_ttfb_x5=".implode(',',$times)."\n";
