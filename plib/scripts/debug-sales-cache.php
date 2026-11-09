<?php
function sh($c){return trim((string)shell_exec($c));}
$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
echo "mtime=".date('c',filemtime($tpl))."\n";
echo "tpl_onerror_count=".substr_count(file_get_contents($tpl),'onerror')."\n";
echo "find_sales_tpl=\n".sh('find /var/www/vhosts/bahman.ir -name sales.tpl 2>/dev/null')."\n";
echo "templates_c=\n".sh('ls -la /var/www/vhosts/bahman.ir/httpdocs/templates_c 2>/dev/null | head -20')."\n";
echo "find_compile_sales=\n".sh('grep -l sales-img /var/www/vhosts/bahman.ir/httpdocs/templates_c/* 2>/dev/null | head')."\n";
echo "find_any_c=\n".sh('find /var/www/vhosts/bahman.ir -type d -name templates_c 2>/dev/null')."\n";
echo "find_cache=\n".sh('find /var/www/vhosts/bahman.ir -type d -name cache 2>/dev/null | head')."\n";

// fetch page and extract img around uuid
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?nocache='.time().'"');
$p=strpos($page,$uuid);
echo "uuid_pos=".($p===false?'-1':$p)." page_len=".strlen($page)."\n";
if ($p!==false) echo "snippet=\n".substr($page,max(0,$p-200),500)."\n";
echo "onerror_in_page=".substr_count($page,'onerror')."\n";

// check if smarty compile dir elsewhere via config
foreach (['/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php','/var/www/vhosts/bahman.ir/httpdocs/config.php','/var/www/vhosts/bahman.ir/httpdocs/includes/config.php'] as $f) {
  if (!is_file($f)) continue;
  $c=file_get_contents($f);
  if (preg_match_all('#compile|cache|template.{0,40}#i',$c,$m)) {
    echo "cfg=$f\n";
    foreach (array_slice($m[0],0,15) as $x) echo "  $x\n";
  }
}
echo "DONE\n";
