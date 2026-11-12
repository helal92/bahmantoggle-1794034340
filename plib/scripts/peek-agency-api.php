<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs';
echo "api_files=\n".sh('find '.$root.' -iname "*api*" -type f 2>/dev/null | head -80')."\n";
echo "grep_json_endpoints=\n".sh('grep -Rnl --include="*.php" -e "Content-Type: application/json" -e "application/json" '.$root.' --exclude-dir=vendor --exclude-dir=templates_c 2>/dev/null | head -40')."\n";
echo "htaccess=\n".sh('sed -n "1,120p" '.$root.'/.htaccess 2>/dev/null')."\n";
echo "index_include=\n";
$idx=@file_get_contents($root.'/index.php');
foreach (explode("\n",$idx) as $i=>$l) {
  if (stripos($l,'include')!==false || stripos($l,'api')!==false || stripos($l,'json')!==false) {
    if (strlen(trim($l))<180) echo ($i+1).": ".rtrim($l)."\n";
  }
}
echo "agency case fields sample from tpl data attrs / config helpers:\n";
echo sh('grep -n -E "agency_details|function bahman_.*agency|scores|setType.\\\'agency\\\'" '.$root.'/templates/bahman/config.php | head -50')."\n";
// look at existing public JSON patterns like product-menu
echo "fragment patterns:\n".sh('grep -n "product_menu\\|Content-Type\\|json_encode" '.$root.'/index.php | head -40')."\n";
echo "DONE\n";
