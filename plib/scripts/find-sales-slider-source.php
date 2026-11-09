<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs';
echo "grep_tpl=\n".sh('grep -Rnl --include="*.tpl" --include="*.php" -e "sales-img" -e "slider_car" -e "ProductCategory_Slider" '.$root.'/templates '.$root.' 2>/dev/null | head -40')."\n";
echo "---\n";
// show sales.tpl img area after patch
$tpl=$root.'/templates/bahman/sales.tpl';
$c=file_get_contents($tpl);
$p=strpos($c,'class="image"');
echo "sales_img_block=\n".substr($c,max(0,$p-40),700)."\n---\n";
// config cache bits
$cfg=$root.'/templates/bahman/config.php';
$cc=file_get_contents($cfg);
foreach (['cache','compile','smarty','sales'] as $k) {
  if (preg_match_all('/^.*'.$k.'.*$/mi',$cc,$m)) {
    echo "CFG_$k:\n";
    foreach (array_slice(array_unique($m[0]),0,20) as $line) echo "  ".trim($line)."\n";
  }
}
echo "---\n";
// look for full page cache files mentioning sales-img uuid
$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
echo "cache_hits=\n".sh('grep -Rnl --include="*" '.$uuid.' '.$root.'/cache '.$root.'/tmp '.$root.'/var '.$root.'/templates_c /tmp 2>/dev/null | head -30')."\n";
echo "---\n";
// hit sales with query and check response headers for cache
echo "headers=\n".sh('curl -sI --max-time 15 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?ts='.time().'"')."\n";
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "Cache-Control: no-cache" -H "Pragma: no-cache" "http://172.20.14.76/sales?ts='.time().'"');
echo "onerror=".substr_count($page,'onerror')." len=".strlen($page)."\n";
// also check homepage
$home=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/?ts='.time().'"');
echo "home_uuid=".(strpos($home,$uuid)!==false?'1':'0')." home_onerror=".substr_count($home,'onerror')." home_slider_car=".(strpos($home,'slider_car')!==false?'1':'0')."\n";
if (strpos($home,$uuid)!==false) {
  $p=strpos($home,$uuid);
  echo "home_snip=\n".substr($home,max(0,$p-180),450)."\n";
}
echo "DONE\n";
