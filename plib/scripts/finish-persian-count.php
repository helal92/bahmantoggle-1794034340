<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$idx='/var/www/vhosts/bahman.ir/httpdocs/index.php';
$c=file_get_contents($idx);

// show registerPlugin block around date_only
$p=strpos($c,"'date_only'");
echo "date_only_pos=$p\n";
echo substr($c, max(0,$p-500), 900)."\n---\n";

if (strpos($c,"'persian_digits'")!==false) {
  echo "ALREADY_REGISTERED\n";
} else {
  // Insert after date_only registration block
  if (!preg_match('/\$smarty->registerPlugin\(\s*\'modifier\'\s*,\s*\'date_only\'\s*,\s*[^;]+;/s',$c,$m)) {
    echo "DATE_ONLY_BLOCK_MISSING\n";
  } else {
    $block=$m[0];
    $ins=$block."\n\$smarty->registerPlugin(\n    'modifier',\n    'persian_digits',\n    'bahman_cms_to_persian_digits'\n);";
    $c2=str_replace($block,$ins,$c);
    $tmp='/tmp/index.php.persian_digits';
    file_put_contents($tmp,$c2);
    sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($idx).' 2>&1');
    echo "INDEX_PATCHED\n";
  }
}

// clear sales compile + opcache if possible + sales data cache
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) @unlink($f);
foreach (glob(sys_get_temp_dir().'/bahman_sales*') ?: [] as $f) @unlink($f);
if (function_exists('opcache_reset')) { @opcache_reset(); echo "opcache_reset\n"; }

// Also bump: verify tpl still has modifier
$tpl=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl');
echo "tpl_has_mod=".(strpos($tpl,'|persian_digits')!==false?'1':'0')."\n";
echo "idx_has_mod=".(strpos(file_get_contents($idx),"'persian_digits'")!==false?'1':'0')."\n";
echo "css_has_dana=".(strpos(file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/circular.css'),'font-family:dana,sans-serif!important;font-size:12px')!==false?'1':'0')."\n";

// hit page - may need to bypass HTML cache. Look for cache purge in index
$idxNow=file_get_contents($idx);
if (preg_match('/X-Bahman-Cache.{0,200}/s',$idxNow,$m)) echo "cache_hdr_ctx=".preg_replace('/\s+/',' ',$m[0])."\n";
if (preg_match('/function\s+bahman_\w*cache\w*\s*\(/',$idxNow,$m)) echo "cache_fn ".$m[0]."\n";

// find cache file path pattern
if (preg_match_all('/bahman_[a-zA-Z0-9_]*cache[a-zA-Z0-9_]*/',$idxNow,$m)) {
  echo "cache_names=".implode(',',array_unique($m[0]))."\n";
}
$cfg=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php');
if (preg_match_all('/sys_get_temp_dir\(\)[^;]{0,80}bahman[^;]{0,40}/',$cfg,$m)) {
  foreach (array_unique($m[0]) as $x) echo "tmpdir_ref $x\n";
}

// purge all bahman temp caches
$n=0;
foreach (glob(sys_get_temp_dir().'/bahman_*') ?: [] as $f) { if (is_file($f)) { @unlink($f); $n++; } }
echo "purged_tmp=$n\n";
// also common html cache dirs used by custom page cache
foreach (['/var/www/vhosts/bahman.ir/httpdocs/cache','/tmp/cms_cache', sys_get_temp_dir().'/cms_cache'] as $d) {
  if (!is_dir($d)) continue;
  foreach (glob($d.'/*') ?: [] as $f) { if (is_file($f)) { @unlink($f); $n++; } }
}

$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?pc='.time().'"');
if (preg_match('/sales-inactive-company__count"[^>]*>([^<]+)</u',$page,$m)) {
  echo "count_value=".$m[1]."\n";
} else {
  echo "count_not_found\n";
  $p=strpos($page,'sales-inactive-company__count');
  if ($p!==false) echo substr($page,$p,150)."\n";
}
echo "hdr=\n".sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/sales?pc='.time().'" | tr -d "\\r" | head -15')."\n";
echo "DONE\n";
