<?php
$root='/var/www/vhosts/bahman.ir/httpdocs';
$out='/tmp/bahman_probe.txt';
$fh=fopen($out,'w');
function w($fh,$s){fwrite($fh,$s);}

w($fh, "hero-video-control.js\n");
w($fh, file_get_contents("$root/templates/bahman/assets/hero-video-control.js")."\n\n");
w($fh, "hero-media-fix.js\n");
w($fh, file_get_contents("$root/templates/bahman/assets/hero-media-fix.js")."\n\n");
w($fh, "home-product-slider.js\n");
w($fh, file_get_contents("$root/templates/bahman/assets/home-product-slider.js")."\n\n");

$idx=file_get_contents("$root/templates/bahman/index.tpl");
w($fh, "index.tpl len=".strlen($idx)."\n");
$lines=explode("\n",$idx);
foreach ($lines as $i=>$line) {
  if (preg_match('/video|hero-media|data-src|preload|autoplay|poster|sitefile|swiper-slide|item_slider/i',$line)) {
    w($fh, ($i+1).': '.$line."\n");
  }
}

w($fh, "\nfooter scripts mentioning hero:\n");
$foot=file_get_contents("$root/templates/bahman/includes/footer_include.tpl");
foreach (explode("\n",$foot) as $i=>$line) {
  if (preg_match('/hero|video|slider|product-slider|home-/i',$line)) w($fh, ($i+1).': '.$line."\n");
}

w($fh, "\nnginx.conf:\n");
w($fh, @file_get_contents('/var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf') ?: 'missing');
w($fh, "\n\nvhost_ssl locations:\n");
$v=@file_get_contents('/var/www/vhosts/system/sitefile.bahman.ir/conf/vhost_ssl.conf') ?: '';
foreach (explode("\n",$v) as $i=>$line) {
  if (preg_match('/location|root|alias|fastcgi|sendfile|internal|proxy/i',$line)) w($fh, ($i+1).': '.$line."\n");
}
fclose($fh);
echo "wrote ".filesize($out)."\n";
echo shell_exec('wc -l '.$out);
// print first 400 lines
echo shell_exec('sed -n "1,350p" '.$out);
