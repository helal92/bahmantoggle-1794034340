<?php
$root='/var/www/vhosts/bahman.ir/httpdocs';
$files=[
  "$root/templates/bahman/index.tpl",
  "$root/templates/bahman/assets/hero-video-control.js",
  "$root/templates/bahman/assets/hero-media-fix.js",
  "$root/templates/bahman/assets/home-product-slider.js",
  "$root/templates/bahman/includes/footer_include.tpl",
  "$root/templates/bahman/includes/header_include.tpl",
];
foreach ($files as $f) {
  echo "\n===== $f =====\n";
  if (!is_file($f)) { echo "MISSING\n"; continue; }
  $c=file_get_contents($f);
  // for index.tpl extract video-related sections
  if (substr($f,-4)==='.tpl') {
    if (preg_match_all('/.{0,120}(hero-media|video|data-src|sitefile|section).{0,200}/is',$c,$m)) {
      foreach (array_slice($m[0],0,40) as $line) echo trim(preg_replace('/\s+/',' ',$line))."\n---\n";
    }
    // also show lines with video tags
    $lines=explode("\n",$c);
    foreach ($lines as $i=>$line) {
      if (preg_match('/video|hero-media|data-src|preload|autoplay|poster/i',$line)) {
        echo ($i+1).': '.rtrim($line)."\n";
      }
    }
  } else {
    echo $c."\n";
  }
}

echo "\n===== nginx full sitefile nginx.conf =====\n";
echo file_get_contents('/var/www/vhosts/system/sitefile.bahman.ir/conf/nginx.conf');
