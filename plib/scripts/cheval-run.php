<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
copy("$res/home-product-slider.js",'/tmp/home-product-slider.js');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/home-product-slider.js').' '.escapeshellarg("$bh/templates/bahman/assets/home-product-slider.js").' 2>&1');
echo "js=".filesize("$bh/templates/bahman/assets/home-product-slider.js")."\n";

// Lazy-load non-critical inline thumbs in index.tpl: /70 brand icons and /450 feature images beyond first
$idx=file_get_contents("$bh/templates/bahman/index.tpl");
// brand/footer small icons often: thumb/.../70 without loading=lazy
$idx2=preg_replace(
  '/(src="\{\$admin_setting\.file_url\}\/thumb\/\{\$[^}]+\}/70"[^>]*)(?<!loading="lazy")(>)/',
  '$1 loading="lazy"$2',
  $idx
);
// safer: add loading lazy to /70 if missing
$idx2=preg_replace_callback(
  '/<img([^>]*\/thumb\/[^>]*=\{[^}]+\}[^>]*\/70[^>]*)>/',
  function($m){
    if (strpos($m[1],'loading=')!==false) return $m[0];
    return '<img'.$m[1].' loading="lazy">';
  },
  $idx
);
// Also feature 450
$idx2=preg_replace_callback(
  '/<img([^>]*\/thumb\/[^>]*\/450"[^>]*)>/',
  function($m){
    if (strpos($m[1],'loading=')!==false) return $m[0];
    return '<img'.$m[1].' loading="lazy">';
  },
  $idx2
);
if ($idx2!==$idx) {
  file_put_contents('/tmp/index.tpl.lazy',$idx2);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/index.tpl.lazy').' '.escapeshellarg("$bh/templates/bahman/index.tpl").' 2>&1');
  echo "index lazy patched\n";
} else echo "index unchanged\n";

// Count how many thumb urls still in HTML without product slider
$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?nocache='.time().'"');
// purge first
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');
foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p))@unlink($p);

$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?x='.time().'"');
preg_match_all('#sitefile\.bahman\.ir/thumb/[^"\s]+#',$html,$m);
echo "html_thumb_refs=".count($m[0])." unique=".count(array_unique($m[0]))."\n";
$bySize=[];
foreach ($m[0] as $u) {
  if (preg_match('#/(\d+)$#',$u,$mm)) $bySize[$mm[1]]=($bySize[$mm[1]]??0)+1;
}
echo "by_size=".json_encode($bySize)."\n";
echo "home_product_slider_js_ok=".(strpos(file_get_contents("$bh/templates/bahman/assets/home-product-slider.js"),'EAGER_PER_BRAND')!==false?'Y':'N')."\n";
echo "DONE\n";
