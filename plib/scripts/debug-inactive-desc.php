<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
$tpl=file_get_contents($root.'/sales.tpl');
echo "tpl_has_desc_span=".(strpos($tpl,'sales-inactive-item__desc')!==false?'1':'0')."\n";
if (preg_match('/sales-inactive-item__title.{0,450}/s',$tpl,$m)) echo "block:\n".$m[0]."\n---\n";

echo "compiled=\n".sh('ls -la /var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales* 2>/dev/null')."\n";
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) {
  $c=file_get_contents($f);
  echo basename($f)." has_desc=".(strpos($c,'sales-inactive-item__desc')!==false?'1':'0')." mtime=".date('c',filemtime($f))."\n";
}

$tmp=rtrim(sys_get_temp_dir(), '/').'/';
echo "html_caches:\n";
foreach (glob($tmp.'bahman_*.html') ?: [] as $f) {
  $body=file_get_contents($f);
  echo basename($f)." ".filesize($f)." ".date('c',filemtime($f))." has_desc=".(strpos($body,'sales-inactive-item__desc')!==false?'1':'0')."\n";
}

$hdr=sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/sales?x='.time().'"');
echo "hdr=\n$hdr\n";

// force miss by deleting caches first
foreach (glob($tmp.'bahman_*.html') ?: [] as $f) @unlink($f);
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) @unlink($f);
@unlink($tmp.'bahman_sales_page_data.cache');

$page=sh('curl -s --max-time 35 -H "Host: bahman.ir" "http://172.20.14.76/sales?x='.time().'"');
echo "len=".strlen($page)." has_desc=".(strpos($page,'sales-inactive-item__desc')!==false?'1':'0')." xbahman=".(preg_match('/X-Bahman-Cache:\s*(\w+)/i',$hdr,$m)?$m[1]:'?')."\n";
if (preg_match('/sales-inactive-item".{0,600}/s',$page,$m)) echo "item_html=\n".$m[0]."\n";

// How many inactive items vs how many with desc key nonempty in cached data
$cacheFile=$tmp.'bahman_sales_page_data.cache';
if (is_file($cacheFile)) {
  $data=@unserialize(file_get_contents($cacheFile));
  if (is_array($data) && isset($data['groupedInactiveCirculars'])) {
    $with=0;$total=0;$samples=[];
    foreach ($data['groupedInactiveCirculars'] as $list) {
      if (!is_array($list)) continue;
      foreach ($list as $item) {
        $total++;
        $d=$item['values']['desc'] ?? '';
        if ($d!=='' && $d!==null) {
          $with++;
          if (count($samples)<5) $samples[]=mb_substr((string)$d,0,80);
        }
      }
    }
    echo "inactive_total=$total with_desc=$with\n";
    echo "samples=".json_encode($samples, JSON_UNESCAPED_UNICODE)."\n";
  } else echo "cache_data_unexpected\n";
} else echo "no_sales_data_cache_yet\n";

echo "DONE\n";
