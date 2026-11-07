<?php
// Homepage hero media markup
$html = (string)shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/"');
echo "home_len=".strlen($html)."\n";
if (preg_match_all('/<(video|source|img)[^>]{0,400}>/i', $html, $m)) {
  echo "MEDIA_TAGS=".count($m[0])."\n";
  foreach (array_slice($m[0],0,25) as $t) echo $t."\n";
}
// sitefile URLs
if (preg_match_all('#https?://sitefile\.bahman\.ir/[a-f0-9\-]+#i', $html, $m)) {
  $u = array_values(array_unique($m[0]));
  echo "SITEFILE_URLS=".count($u)."\n";
  foreach (array_slice($u,0,15) as $x) echo $x."\n";
}
// sitefile HEAD/GET timing for empower uuid
$id='61491e0c-bfc8-11f1-9600-005056b972d8';
foreach (['','?thumb=1&size=800'] as $q) {
  $url = "https://172.20.14.76/$id$q";
  $cmd = "curl -skI --max-time 20 -H 'Host: sitefile.bahman.ir' -H 'Referer: https://bahman.ir/' ".($q===''?"-H 'Range: bytes=0-' ":"")."'$url'";
  echo "\n=== $id$q ===\n";
  echo shell_exec($cmd);
}
// FPM status if available
echo "\nFPM:\n";
echo shell_exec("ls /var/www/vhosts/system/sitefile.bahman.ir/conf/ 2>/dev/null");
echo shell_exec("grep -E 'pm\\.|max_children|max_requests' /var/www/vhosts/system/sitefile.bahman.ir/conf/php-fpm.conf 2>/dev/null | head -20");
echo shell_exec("grep -E 'pm\\.|max_children' /opt/plesk/php/*/etc/php-fpm.d/*sitefile* 2>/dev/null | head -20");
echo shell_exec("find /var/www/vhosts/system -name 'php-fpm.conf' 2>/dev/null | xargs grep -l sitefile 2>/dev/null | head -5");
$confs = trim((string)shell_exec("grep -rl 'sitefile.bahman.ir' /var/www/vhosts/system/*/conf/php.ini /var/www/vhosts/system/*/conf/php-fpm.conf /etc/php-fpm.d /opt/plesk/php/*/etc/php-fpm.d 2>/dev/null | head -10"));
echo "confs=$confs\n";
foreach (preg_split('/\n/',$confs) as $c) {
  if (!$c) continue;
  echo "FILE $c\n";
  echo shell_exec("grep -E 'pm\\.|=|pool' ".escapeshellarg($c)." | head -30");
}
// apache workers / mpm
echo "\nAPACHE MPM:\n";
echo shell_exec("httpd -V 2>/dev/null | grep -i mpm; apache2ctl -V 2>/dev/null | grep -i mpm; ls /etc/httpd/conf.modules.d 2>/dev/null | head");
echo shell_exec("ps aux | grep -E 'php-fpm.*sitefile|php-fpm: pool' | grep -v grep | wc -l");
echo shell_exec("ps aux | grep php-fpm | grep -v grep | head -20");
