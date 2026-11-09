<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$idx='/var/www/vhosts/bahman.ir/httpdocs/index.php';
$c=file_get_contents($idx);

$broken = <<<'BRK'
$smarty->registerPlugin(
    'modifier',
    'date_only',
    function ($value) {
        return bahman_cms_directive_start_date_label($value);
$smarty->registerPlugin(
    'modifier',
    'persian_digits',
    'bahman_cms_to_persian_digits'
);
    }
);
BRK;

$fixed = <<<'FIX'
$smarty->registerPlugin(
    'modifier',
    'date_only',
    function ($value) {
        return bahman_cms_directive_start_date_label($value);
    }
);

$smarty->registerPlugin(
    'modifier',
    'persian_digits',
    'bahman_cms_to_persian_digits'
);
FIX;

if (strpos($c,$broken)!==false) {
  $c=str_replace($broken,$fixed,$c);
  echo "FIXED_BROKEN_BLOCK\n";
} elseif (strpos($c,"'persian_digits'")!==false && preg_match('/registerPlugin\(\s*\'modifier\'\s*,\s*\'persian_digits\'\s*,\s*\'bahman_cms_to_persian_digits\'\s*\);/',$c)) {
  // ensure not nested - check structure
  echo "CHECK_STRUCTURE\n";
  $p=strpos($c,"'persian_digits'");
  echo substr($c,max(0,$p-250),500)."\n";
} else {
  echo "UNEXPECTED\n";
  $p=strpos($c,"'date_only'");
  echo substr($c,max(0,$p-50),500)."\n";
}

// syntax check via php binary paths
$phpBin='';
foreach (['/opt/plesk/php/8.3/bin/php','/opt/plesk/php/8.2/bin/php','/opt/plesk/php/8.1/bin/php','/usr/bin/php'] as $b) {
  if (is_file($b)) { $phpBin=$b; break; }
}
echo "phpBin=$phpBin\n";

$tmp='/tmp/index.php.persian_fixed';
file_put_contents($tmp,$c);
if ($phpBin) {
  echo "lint=".sh(escapeshellarg($phpBin).' -l '.escapeshellarg($tmp).' 2>&1')."\n";
}
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($idx).' 2>&1');
echo "deployed\n";
echo "verify_block=\n";
$c=file_get_contents($idx);
$p=strpos($c,"'date_only'");
echo substr($c,max(0,$p-20),520)."\n---\n";

// clear caches
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*') ?: [] as $f) {
  if (strpos(basename($f),'sales')!==false || strpos(@file_get_contents($f),'persian_digits')!==false) @unlink($f);
}
$tmpDir=rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
foreach (['bahman_sales_page.html','bahman_sales_page_data.cache','bahman_index.html'] as $name) {
  $f=$tmpDir.'/'.$name;
  if (is_file($f)) { @unlink($f); echo "rm $name\n"; }
}
foreach (glob($tmpDir.'/bahman_*sales*') ?: [] as $f) { @unlink($f); echo "rm ".basename($f)."\n"; }
foreach (glob($tmpDir.'/bahman_*.html') ?: [] as $f) { @unlink($f); echo "rmhtml ".basename($f)."\n"; }

$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/sales?ok='.time().'"');
echo "page_len=".strlen($page)."\n";
if (preg_match_all('/sales-inactive-company__count"[^>]*>([^<]+)</u',$page,$m)) {
  echo "counts=".json_encode($m[1], JSON_UNESCAPED_UNICODE)."\n";
} else {
  $p=strpos($page,'sales-inactive-company__count');
  echo "fail_snip=".json_encode($p===false?'NONE':substr($page,$p,180), JSON_UNESCAPED_UNICODE)."\n";
  echo "has_unknown_mod=".(strpos($page,"unknown modifier")!==false?'1':'0')."\n";
}
echo "DONE\n";
