<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';

// 1) ensure template
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$c=file_get_contents($tpl);
$c2=preg_replace('/(id="product_slider_link2"[^>]*>)\s*خرید خودرو\s*(<\/a>)/u','$1سامانه فروش$2',$c, -1, $n);
if ($n) {
  $tmp='/tmp/_sales_btn2.tpl';
  file_put_contents($tmp,$c2);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tpl));
  @unlink($tmp);
  echo "tpl_repatched=$n\n";
} else {
  echo "tpl_already=".(strpos($c,'سامانه فروش')!==false?'1':'0')."\n";
}

// 2) find cache via root find including private tmp
$out=sh("find /tmp /var/tmp /var/lib/php /run -name 'bahman_sales_page_v4.html' 2>/dev/null");
echo "FIND=$out\n";
foreach (preg_split('/\n+/', $out) as $f) {
  if ($f==='' || !is_file($f)) continue;
  $html=file_get_contents($f);
  $html2=preg_replace('/(id="product_slider_link2"[^>]*>)\s*خرید خودرو\s*(<\/a>)/u','$1سامانه فروش$2',$html,-1,$n2);
  if ($n2) {
    file_put_contents($f,$html2);
    echo "patched_cache=$f n=$n2\n";
  } else {
    @unlink($f);
    echo "deleted_cache=$f\n";
  }
}

// 3) write a self-deleting cache buster into docroot and hit it as web
$buster = '<?php
$f = rtrim(sys_get_temp_dir(), "/")."/bahman_sales_page_v4.html";
$info = sys_get_temp_dir()."|".(is_file($f)?"1":"0")."|".$f;
if (is_file($f)) {
  $h = file_get_contents($f);
  $h2 = preg_replace(\'/(id="product_slider_link2"[^>]*>)\\s*خرید خودرو\\s*(<\\/a>)/u\', \'$1سامانه فروش$2\', $h, -1, $n);
  if ($n) file_put_contents($f, $h2); else @unlink($f);
  $info .= "|n=$n";
}
header("Content-Type: text/plain; charset=UTF-8");
echo $info;
@unlink(__FILE__);
';
$tmp='/tmp/_sales_cache_buster.php';
file_put_contents($tmp,$buster);
$dest='/var/www/vhosts/bahman.ir/httpdocs/_sales_cache_buster.php';
echo "cp=".sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dest))."\n";
echo "bust=".sh('curl -s --max-time 15 -H "Host: bahman.ir" http://172.20.14.76/_sales_cache_buster.php')."\n";
@unlink($tmp);

// 4) also clear smarty compiles containing old text
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*.php') ?: [] as $f) {
  $t=@file_get_contents($f);
  if ($t && strpos($t,'product_slider_link2')!==false) { @unlink($f); echo "rmc ".basename($f)."\n"; }
}

$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/sales?_='.time().'"');
if (preg_match('/id="product_slider_link2"[^>]*>([^<]*)</u',$page,$m)) echo "btn=[".$m[1]."]\n"; else echo "btn=NOTFOUND\n";
echo "cache_hdr=".sh('curl -sI --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales?_i='.time().'" | tr -d "\\r" | grep -i X-Bahman')."\n";
echo "DONE\n";
