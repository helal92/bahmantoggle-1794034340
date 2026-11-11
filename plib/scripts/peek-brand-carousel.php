<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "files=\n".sh('find '.$root.' -iname "*brand*" 2>/dev/null')."\n";
echo "grep=\n".sh('grep -Rnl --include="*.js" --include="*.css" --include="*.tpl" -e "brand-carousel" -e "brand_carousel" -e "data-brand-carousel" '.$root.' 2>/dev/null')."\n";

// JS
foreach (['assets/brand-slider.js','assets/brand-carousel.js','base_template/assets/brand-slider.js'] as $rel) {
  $f=$root.'/'.$rel;
  if (!is_file($f)) continue;
  echo "=== $rel bytes=".filesize($f)." ===\n";
  echo file_get_contents($f)."\n";
}

// CSS snippets
echo "=== css hits ===\n";
echo sh('grep -Rn --include="*.css" -e "brand-carousel" -e "brands " -e "\\.brands" '.$root.'/assets '.$root.'/base_template/assets 2>/dev/null | head -40')."\n";

// tpl markup
echo "=== tpl ===\n";
echo sh('grep -n -A80 "brand-carousel\|section.brands\|class=\"brands\"" '.$root.'/index.tpl 2>/dev/null | head -120')."\n";

// live page check logos
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/?bc='.time().'"');
echo "page_len=".strlen($page)."\n";
echo "has_carousel=".(strpos($page,'brand-carousel')!==false?'1':'0')."\n";
preg_match_all('/brand-carousel[\s\S]{0,2500}/',$page,$m);
if ($m[0]) {
  $chunk=$m[0][0];
  preg_match_all('/<img[^>]+>/',$chunk,$imgs);
  echo "imgs_in_chunk=".count($imgs[0])."\n";
  foreach (array_slice($imgs[0],0,3) as $img) echo "  $img\n";
  preg_match_all('/src="([^"]+)"/',$chunk,$srcs);
  echo "srcs=".json_encode(array_slice($srcs[1],0,5))."\n";
}
// script tag for brand
preg_match_all('/src="([^"]*brand[^"]*\.js[^"]*)"/',$page,$sm);
echo "brand_js=".json_encode($sm[1])."\n";
echo "DONE\n";
