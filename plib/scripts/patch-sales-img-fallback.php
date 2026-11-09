<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$c=file_get_contents($tpl);
$bak='/tmp/sales.tpl.bak.'.date('YmdHis');
file_put_contents($bak,$c);
echo "bak=$bak\n";

$old=<<<'OLD'
                                                    <img class="image"
                                                        src="/sales-img/{$pro.values.image}_600.png"
                                                        alt="{$pro.values.title|default: ''}"
                                                        width="600"
                                                        height="400"
                                                        loading="lazy"
                                                        decoding="async"
                                                        data-title="{$pro.values.title|default: ''|escape:'html'}"
                                                        data-desc="{$pro.values.desc|default: ''|escape:'html'}"
                                                        {if isset($pro.values.attachemnt) and $pro.values.attachemnt neq ''}data-show-link="{$admin_setting.file_url}/{$pro.values.attachemnt}"{/if}
                                                        {if isset($pro.values.product) and $pro.values.product neq ''}data-product-link="{$admin_setting.site_url}/product/{$pro.values.product}"{/if}
                                                        {if isset($pro.values.sales_link) and $pro.values.sales_link neq ''}data-buy-link="{$pro.values.sales_link}"{/if}>
OLD;

$new=<<<'NEW'
                                                    <img class="image"
                                                        src="/sales-img/{$pro.values.image}_600.png"
                                                        alt="{$pro.values.title|default: ''}"
                                                        width="600"
                                                        height="400"
                                                        loading="lazy"
                                                        decoding="async"
                                                        onerror="this.onerror=null;this.src='{$admin_setting.file_url}/thumb/{$pro.values.image}/600';"
                                                        data-title="{$pro.values.title|default: ''|escape:'html'}"
                                                        data-desc="{$pro.values.desc|default: ''|escape:'html'}"
                                                        {if isset($pro.values.attachemnt) and $pro.values.attachemnt neq ''}data-show-link="{$admin_setting.file_url}/{$pro.values.attachemnt}"{/if}
                                                        {if isset($pro.values.product) and $pro.values.product neq ''}data-product-link="{$admin_setting.site_url}/product/{$pro.values.product}"{/if}
                                                        {if isset($pro.values.sales_link) and $pro.values.sales_link neq ''}data-buy-link="{$pro.values.sales_link}"{/if}>
NEW;

if (strpos($c,'onerror="this.onerror=null;this.src=\'{$admin_setting.file_url}/thumb/{$pro.values.image}/600\'"')!==false) {
  echo "ALREADY_PATCHED_SLIDER\n";
} elseif (strpos($c,$old)===false) {
  echo "SLIDER_BLOCK_NOT_FOUND\n";
  // show nearby for debug
  $p=strpos($c,'class="image"');
  echo substr($c,max(0,$p-80),500)."\n";
} else {
  $c=str_replace($old,$new,$c);
  echo "SLIDER_PATCHED\n";
}

// banner fallback
$oldB='src="/sales-img/{$sales_page.values.banner}_1920.jpg"';
$newB='src="/sales-img/{$sales_page.values.banner}_1920.jpg" onerror="this.onerror=null;this.src=\'{$admin_setting.file_url}/thumb/{$sales_page.values.banner}/1920\'"';
if (strpos($c,'thumb/{$sales_page.values.banner}/1920')!==false) {
  echo "ALREADY_PATCHED_BANNER\n";
} elseif (strpos($c,$oldB)===false) {
  echo "BANNER_SRC_NOT_FOUND\n";
} else {
  $c=str_replace($oldB,$newB,$c);
  echo "BANNER_PATCHED\n";
}

$tmp='/tmp/sales.tpl.patched';
file_put_contents($tmp,$c);
$cp=sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tpl).' 2>&1');
echo "cp=$cp\n";
$chk=file_get_contents($tpl);
echo "has_slider_onerror=".(strpos($chk,'thumb/{$pro.values.image}/600')!==false?'1':'0')."\n";
echo "has_banner_onerror=".(strpos($chk,'thumb/{$sales_page.values.banner}/1920')!==false?'1':'0')."\n";

// clear smarty compile for sales if present
$comp='/var/www/vhosts/bahman.ir/httpdocs/templates_c';
if (is_dir($comp)) {
  $n=0;
  foreach (glob($comp.'/*sales*') ?: [] as $f) { @unlink($f); $n++; }
  // also wipe compiled templates that contain sales-img path marker
  foreach (glob($comp.'/*.php') ?: [] as $f) {
    $t=@file_get_contents($f);
    if ($t && strpos($t,'sales-img')!==false) { @unlink($f); $n++; }
  }
  echo "cleared_compile=$n\n";
}

$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
echo "img=".sh('curl -s -o /dev/null -w "%{http_code} %{size_download}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$uuid.'_600.png"')."\n";
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/sales');
echo "page_has_onerror=".(strpos($page,'onerror=')!==false?'1':'0')."\n";
echo "page_has_uuid=".(strpos($page,$uuid)!==false?'1':'0')."\n";
echo "DONE\n";
