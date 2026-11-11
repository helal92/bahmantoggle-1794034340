<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "css=\n".file_get_contents($root.'/assets/brand-slider.css')."\n---\n";
echo "import hits=\n".sh('grep -Rn --include="*.js" --include="*.tpl" -e "brand-slider" -e "initBrandCarousel" '.$root.' 2>/dev/null')."\n";
// footer_include scripts
echo "footer scripts brand/module:\n".sh('grep -n "type=.module\|brand\|home-stack\|defer" '.$root.'/includes/footer_include.tpl '.$root.'/index.tpl 2>/dev/null | head -60')."\n";
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/?t='.time().'"');
preg_match_all('/<script[^>]+>/',$page,$m);
echo "scripts:\n";
foreach ($m[0] as $s) {
  if (stripos($s,'brand')!==false || stripos($s,'module')!==false || stripos($s,'index')!==false || stripos($s,'main')!==false || stripos($s,'home')!==false) echo "  $s\n";
}
// check image availability
$urls=[
'https://sitefile.bahman.ir/thumb/b2841745-6948-11f1-9b58-005056b972d8/70',
];
foreach ($urls as $u) {
  echo "img=".sh('curl -sI --max-time 10 -H "Host: sitefile.bahman.ir" '.escapeshellarg(str_replace('https://sitefile.bahman.ir','http://172.20.14.76',$u)).' | tr -d "\\r" | head -8')."\n";
}
// How many company items in HTML before JS clones
if (preg_match('/brand-carousel__track">(.*?)<\/div>\s*<\/div>\s*<button[^>]*brand-carousel__nav--next/s',$page,$m)) {
  preg_match_all('/brand-carousel__item/',$m[1],$im);
  echo "server_items=".count($im[0])."\n";
}
echo "DONE\n";
