<?php
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$c=file_get_contents($tpl);
$old='product_slider_link2" class="Link2" href="#" target="_blank" style="display: none;">خرید خودرو</a>';
$new='product_slider_link2" class="Link2" href="#" target="_blank" style="display: none;">سامانه فروش</a>';
if (strpos($c,'سامانه فروش</a>')!==false && strpos($c,'product_slider_link2')!==false) {
  // maybe already
  if (strpos($c,$old)===false) { echo "ALREADY\n"; }
}
if (strpos($c,$old)===false) {
  // looser replace
  $c2=preg_replace('/(id="product_slider_link2"[^>]*>)\s*خرید خودرو\s*(<\/a>)/u','$1سامانه فروش$2',$c,1,$n);
  if (!$n) {
    echo "MISS\n";
    $p=strpos($c,'product_slider_link2');
    echo substr($c,max(0,$p),180)."\n";
    exit(1);
  }
  $c=$c2;
  echo "PATCHED_REGEX\n";
} else {
  $c=str_replace($old,$new,$c);
  echo "PATCHED\n";
}
$tmp='/tmp/_sales_btn_rename.tpl';
file_put_contents($tmp,$c);
echo trim((string)shell_exec($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tpl).' 2>&1'))."\n";
@unlink($tmp);
$chk=file_get_contents($tpl);
echo "ok=".(strpos($chk,'سامانه فروش')!==false && strpos($chk,'product_slider_link2')!==false?'1':'0')."\n";
echo "old_left=".(strpos($chk,'خرید خودرو')!==false?'1':'0')."\n";
// clear sales compile + page cache
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*.php') ?: [] as $f) {
  $t=@file_get_contents($f);
  if ($t && (strpos($t,'sales.tpl')!==false || strpos($t,'product_slider_link2')!==false)) @unlink($f);
}
$cache=rtrim(sys_get_temp_dir(),'/').'/bahman_sales_page_v4.html';
if (is_file($cache)) { @unlink($cache); echo "rm_cache=$cache\n"; }
echo "FIND_CACHE\n".trim((string)shell_exec('find /tmp /var/tmp -name "bahman_sales_page_v4.html" 2>/dev/null'))."\n";
foreach (explode("\n", trim((string)shell_exec('find /tmp /var/tmp -name "bahman_sales_page_v4.html" 2>/dev/null'))) as $f) {
  if ($f && is_file($f)) { @unlink($f); echo "rm $f\n"; }
}
$page=trim((string)shell_exec('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/sales?_='.time().'"'));
echo "has_new=".(strpos($page,'سامانه فروش')!==false?'1':'0')."\n";
echo "has_old=".(strpos($page,'خرید خودرو')!==false?'1':'0')."\n";
echo "DONE\n";
