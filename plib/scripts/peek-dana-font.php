<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo "fonts=\n".sh('ls -la '.$root.'/fonts '.$root.'/assets/../fonts '.$root.'/base_template/fonts 2>/dev/null | head -80')."\n";
echo "fontface_dana=\n".sh('grep -Rno --include="*.css" -e "@font-face{[^{}]*dana[^{}]*}" -e "font-family:dana" '.$root.'/assets/index2.css '.$root.'/assets/main.css 2>/dev/null | head -5')."\n";
// extract dana font-face blocks from index2/main
foreach (['assets/index2.css','assets/main.css','base_template/assets/index2.css'] as $rel) {
  $f=$root.'/'.$rel;
  if (!is_file($f)) continue;
  $c=file_get_contents($f);
  if (preg_match_all('/@font-face\{[^}]*dana[^}]*\}/i',$c,$m)) {
    echo "$rel dana faces=".count($m[0])."\n";
    echo $m[0][0]."\n";
  }
  if (preg_match_all('/@font-face\{[^}]*avenir[^}]*\}/i',$c,$m)) {
    echo "$rel avenir faces=".count($m[0])."\n";
  }
  if (preg_match_all('/@font-face\{[^}]*FaNum|fanum|Farsi[^}]*\}/i',$c,$m)) {
    echo "$rel fanum=".count($m[0])."\n".($m[0][0]??'')."\n";
  }
}
echo "font files with Fa/Num/Dana:\n".sh('find '.$root.' -iname "*dana*" -o -iname "*fanum*" -o -iname "*fa-num*" 2>/dev/null | head -40')."\n";
// which circular.css is linked on /sales
$page=sh('curl -s --max-time 20 -H "Host: bahman.ir" "http://172.20.14.76/sales?t='.time().'"');
preg_match_all('#href="([^"]+\.css[^"]*)"#',$page,$m);
echo "css_links:\n";
foreach ($m[1] as $u) if (stripos($u,'circular')!==false || stripos($u,'main')!==false || stripos($u,'typo')!==false || stripos($u,'index')!==false) echo "  $u\n";
// persian digit helper already?
echo "persian_filter=\n".sh('grep -Rn --include="*.php" --include="*.tpl" -e "persian" -e "fa_num" -e "to_persian" -e "۰۱۲۳" '.$root.'/../.. 2>/dev/null | grep -i "num\|digit\|persian" | head -30')."\n";
echo sh('grep -Rn --include="*.php" -e "date_only\|modifier" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php 2>/dev/null | head -20')."\n";
echo "DONE\n";
