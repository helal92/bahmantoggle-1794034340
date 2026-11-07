<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$hdr="$bh/templates/bahman/includes/header_include.tpl";
$h=file_get_contents($hdr);

// Add product-tabs.css to sales/after_sales/showroom-like pages branch (the big else-if with 404.css+navigation)
$needle='<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
{/if}
{* Shared overrides last so footer/typography stay consistent on every page *}';

// There may be multiple navigation.css closes - target the one after circular in sales branch
// More specific: after circular.css in the sales else-if block
$old = <<<'OLD'
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/404.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/circular.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
{/if}
OLD;

$new = <<<'NEW'
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/404.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/circular.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
{/if}
NEW;

if (strpos($h,$old)===false) {
  echo "exact block missing, searching...\n";
  // show context around sales navigation link
  $lines=explode("\n",$h);
  foreach ($lines as $i=>$line) {
    if (strpos($line,'404.css')!==false || (strpos($line,'navigation.css')!==false && $i>100)) {
      echo ($i+1).': '.$line."\n";
    }
  }
} else {
  $h=str_replace($old,$new,$h);
  echo "patched sales CSS branch\n";
}

// Also ensure homepage still has product-tabs (deferred) - already does
// Clear sales HTML cache
file_put_contents('/tmp/header_sales.tpl',$h);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/header_sales.tpl').' '.escapeshellarg($hdr).' 2>&1');

foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p))@unlink($p);
foreach (glob('/tmp/bahman_*sales*')?:[] as $p) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');

$html=shell_exec("curl -sk --max-time 40 -H 'Host: bahman.ir' 'https://172.20.14.76/sales?x=".time()."'");
echo "sales_len=".strlen($html)."\n";
preg_match_all('#assets/([^?"\']+\.css)#',$html,$m);
echo "css=".implode(',', array_unique($m[1]))."\n";
echo "has_product_tabs=".(strpos($html,'product-tabs.css')!==false?'Y':'N')."\n";
echo "DONE\n";
