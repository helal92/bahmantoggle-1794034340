<?php
function sh($c){return trim((string)shell_exec($c));}
$roots=[
  '/var/www/vhosts/bahman.ir/httpdocs/tmp',
  '/var/www/vhosts/bahman.ir/tmp',
  '/tmp',
  '/var/www/vhosts/bahman.ir/httpdocs/templates_c',
];
$n=0;
foreach ($roots as $r) {
  if (!is_dir($r)) continue;
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($r, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $p=$f->getPathname();
    $bn=strtolower($f->getFilename());
    if (strpos($bn,'sales')!==false || strpos($bn,'bahman_sales')!==false) {
      @unlink($p); $n++; echo "rm $p\n";
    }
  }
}
// also compiled templates with sales-img
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*.php') ?: [] as $f) {
  $t=@file_get_contents($f);
  if ($t && (strpos($t,'sales-img')!==false || strpos($t,'onerror')!==false || strpos($t,'sales.tpl')!==false)) {
    @unlink($f); $n++; echo "rmc ".basename($f)."\n";
  }
}
echo "cleared=$n\n";
// bust via query? check if index caches by include
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?nocache='.time().'"');
echo "len=".strlen($page)."\n";
echo "onerror=".(strpos($page,'onerror=')!==false?'1':'0')."\n";
echo "thumb600=".(strpos($page,'/thumb/')!==false?'1':'0')."\n";
// show snippet around image
$p=strpos($page,'2b8db709-b4c4-11f1-ad46-005056b972d8_600.png');
echo "snip=".substr($page, max(0,$p-30), 220)."\n";
// verify htaccess + generator publicly
$fake='00000000-0000-4000-8000-000000000001';
echo "missing_fake=".sh('curl -s -o /dev/null -w "%{http_code}" --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$fake.'_600.png"')."\n";
echo "ht=". (strpos(file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/.htaccess'),'sales_img.php')!==false?'1':'0')."\n";
echo "DONE\n";
