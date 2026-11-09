<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$idx='/var/www/vhosts/bahman.ir/httpdocs/index.php';
$c=file_get_contents($idx);

$old = <<<'OLD'
$smarty->registerPlugin(
    'modifier',
    'persian_digits',
    'bahman_cms_to_persian_digits'
);
OLD;

$new = <<<'NEW'
$smarty->registerPlugin(
    'modifier',
    'persian_digits',
    function ($value) {
        return bahman_cms_to_persian_digits((string) ($value ?? ''));
    }
);
NEW;

if (strpos($c,$old)===false) {
  echo "OLD_MISS\n";
  $p=strpos($c,'persian_digits');
  echo substr($c,max(0,$p-100),400)."\n";
} else {
  $c=str_replace($old,$new,$c);
  echo "REPLACED\n";
}
$tmp='/tmp/index.php.persian_callable';
file_put_contents($tmp,$c);
$php='/opt/plesk/php/8.3/bin/php';
echo "lint=".sh(escapeshellarg($php).' -l '.escapeshellarg($tmp))."\n";
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($idx).' 2>&1');

// clear compiled templates referencing persian_digits / sales
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*') ?: [] as $f) {
  $b=basename($f);
  if (strpos($b,'sales')!==false) @unlink($f);
}
$tmpDir=rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
foreach (glob($tmpDir.'/bahman_*.html') ?: [] as $f) @unlink($f);
foreach (glob($tmpDir.'/bahman_sales*') ?: [] as $f) @unlink($f);

$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/sales?ok2='.time().'"');
echo "page_len=".strlen($page)."\n";
echo "fatal=".(stripos($page,'Fatal error')!==false?'1':'0')."\n";
if (preg_match_all('/sales-inactive-company__count"[^>]*>([^<]+)</u',$page,$m)) {
  echo "counts=".json_encode($m[1], JSON_UNESCAPED_UNICODE)."\n";
} else {
  $p=strpos($page,'sales-inactive-company__count');
  echo "snip=".json_encode($p===false?substr($page,0,300):substr($page,$p,180), JSON_UNESCAPED_UNICODE)."\n";
}
// confirm css still has dana
echo "css_dana=".(strpos(file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/circular.css'),'font-family:dana,sans-serif!important;font-size:12px')!==false?'1':'0')."\n";
echo "DONE\n";
