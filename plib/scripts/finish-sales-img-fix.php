<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs';

// Patch other templates that use sales-img slider images
$files=[
  $root.'/templates/bahman/index.tpl',
  $root.'/templates/bahman/showroom.tpl',
  $root.'/templates/bahman/includes/footer_include.tpl',
];
foreach ($files as $f) {
  if (!is_file($f)) { echo "MISS $f\n"; continue; }
  $c=file_get_contents($f);
  echo "FILE=".basename(dirname($f)).'/'.basename($f)." sales-img=".substr_count($c,'sales-img')." onerror_thumb=".substr_count($c,'/thumb/')."\n";
  // show img contexts
  if (preg_match_all('#.{0,40}sales-img[^"\']+.{0,120}#us',$c,$m)) {
    foreach ($m[0] as $i=>$x) echo "  REF$i: ".preg_replace('/\s+/',' ',$x)."\n";
  }
}

// Add onerror to index/showroom/footer if they have the same _600.png pattern without onerror nearby
foreach ($files as $f) {
  if (!is_file($f)) continue;
  $c0=file_get_contents($f);
  $c=$c0;
  // generic: after src="/sales-img/{$...}_600.png" ensure onerror if missing in same tag
  $c=preg_replace_callback(
    '#<img([^>]*?)src="(/sales-img/\{\$[^"]+_600\.png)"([^>]*)>#us',
    function($m){
      $before=$m[1]; $src=$m[2]; $after=$m[3];
      $all=$before.$after;
      if (strpos($all,'onerror=')!==false) return $m[0];
      // extract smarty var uuid expression from src
      if (!preg_match('#/sales-img/(\{\$[^}]+(?:\}[^"]*)?)_600\.png#',$src,$mm)) {
        // fallback parse
      }
      if (preg_match('#/sales-img/(\{\$[^"]+)_600\.png#',$src,$mm)) {
        $var=$mm[1];
        // var like {$pro.values.image}
        $onerror=' onerror="this.onerror=null;this.src=\'{$admin_setting.file_url}/thumb/'.$var.'/600\'"';
        return '<img'.$before.'src="'.$src.'"'.$after.$onerror.'>';
      }
      return $m[0];
    },
    $c
  );
  // banner jpg
  $c=preg_replace_callback(
    '#src="(/sales-img/\{\$[^"]+_1920\.jpg)"#us',
    function($m) use (&$c){
      // only if following attrs in same tag lack onerror - handled loosely by checking nearby later
      return $m[0];
    },
    $c
  );
  if ($c!==$c0) {
    $tmp='/tmp/'.basename($f).'.patched';
    file_put_contents($tmp,$c);
    sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($f).' 2>&1');
    echo "PATCHED ".basename($f)."\n";
  } else {
    echo "NOCHANGE ".basename($f)."\n";
  }
}

// Clear page HTML cache (X-Bahman-Cache)
$tmp=sys_get_temp_dir();
echo "tmpdir=$tmp\n";
$cleared=0;
foreach (glob($tmp.'/bahman_*') ?: [] as $f) {
  if (is_file($f)) { @unlink($f); $cleared++; echo "rm ".basename($f)."\n"; }
}
foreach (glob($tmp.'/cms_cache/*') ?: [] as $f) {
  if (is_file($f)) { @unlink($f); $cleared++; }
}
// search common cache dirs for HTML page cache
foreach (['/var/www/vhosts/bahman.ir/httpdocs/cache','/var/www/vhosts/bahman.ir/tmp','/tmp/bahman_page_cache','/var/tmp'] as $d) {
  if (!is_dir($d)) continue;
  foreach (glob($d.'/*') ?: [] as $f) {
    if (!is_file($f)) continue;
    $h=@file_get_contents($f,false,null,0,200);
    if ($h && (strpos($h,'sales-img')!==false || strpos($h,'X-Bahman')!==false || strpos(basename($f),'bahman')!==false)) {
      @unlink($f); $cleared++; echo "rmcache $d/".basename($f)."\n";
    }
  }
}
// find cache implementation
echo "cache_impl=\n";
if (preg_match_all('/X-Bahman-Cache|bahman_page_cache|page_cache|CACHE_HIT/i', file_get_contents($root.'/templates/bahman/config.php'), $m, PREG_OFFSET_CAPTURE)) {
  foreach ($m[0] as $hit) {
    $pos=$hit[1];
    echo substr(file_get_contents($root.'/templates/bahman/config.php'), max(0,$pos-120), 300)."\n----\n";
  }
}
// also grep php files
echo sh('grep -Rnl --include="*.php" -e "X-Bahman-Cache" '.$root.' 2>/dev/null | head')."\n";

// wipe templates_c sales/index related
foreach (glob($root.'/templates_c/*') ?: [] as $f) {
  $b=basename($f);
  if (preg_match('/sales|index|showroom|footer_include/',$b)) { @unlink($f); echo "rmc $b\n"; }
}

$uuid='33676ad6-9ef6-11f1-9b58-005056b972d8';
echo "img=".sh('curl -s -o /dev/null -w "%{http_code}:%{size_download}:%{content_type}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$uuid.'_600.png"')."\n";
echo "img2=".sh('curl -s -o /dev/null -w "%{http_code}:%{size_download}" --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/822b48c8-dc30-11f1-94e6-005056b972d8_600.png"')."\n";

// force miss
$h=sh('curl -sI --max-time 15 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?purge='.time().'"');
echo "hdr=\n$h\n";
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/sales?purge='.time().'"');
$p=strpos($page,$uuid);
echo "snippet=\n".($p!==false?substr($page,max(0,$p-80),420):'NONE')."\n";
echo "DONE cleared=$cleared\n";
