<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
// confirm tpl
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
echo "tpl_ok=".(strpos(file_get_contents($tpl),'سامانه فروش')!==false?'1':'0')."\n";
// discover and delete fpm cache via web probe
$probe='/tmp/_bust_probe.php';
file_put_contents($probe, '<?php $f=rtrim(sys_get_temp_dir(),"/")."/bahman_sales_page_v4.html"; echo sys_get_temp_dir()."\n".$f."\n".(is_file($f)?"HAS ".filesize($f):"NO")."\n"; if(is_file($f)){@unlink($f);');
$dest='/var/www/vhosts/bahman.ir/httpdocs/_bust_probe.php';
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($probe).' '.escapeshellarg($dest));
echo "probe=".sh('curl -s --max-time 10 -H "Host: bahman.ir" http://172.20.14.76/_bust_probe.php')."\n";
@unlink($dest);
sh($fm.' '.escapeshellarg($user).' rm '.escapeshellarg($dest).' 2>&1');
// wipe compile for sales
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*.php') ?: [] as $f) {
  $t=@file_get_contents($f);
  if ($t && (strpos($t,'sales.tpl')!==false || strpos($t,'product_slider_link2')!==false || strpos($t,'خرید خودرو')!==false)) @unlink($f);
}
$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/sales?_='.time().'"');
echo "hdr=".sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/sales?_h='.time().'" | tr -d "\\r" | grep -iE "HTTP/|X-Bahman"')."\n";
if (preg_match('/id="product_slider_link2"[^>]*>([^<]*)</u',$page,$m)) echo "btn=[".$m[1]."]\n";
echo "has_new_btn=".(strpos($page,'id="product_slider_link2"')!==false && strpos($page,'سامانه فروش')!==false?'1':'0')."\n";
echo "DONE\n";
