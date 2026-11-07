<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';

echo "A count=".ExecuteScalar("SELECT COUNT(*) FROM cms_content_field_values WHERE content_id=23")."\n";

$rs = Execute("SELECT id, content_type_field_id, CHAR_LENGTH(value) AS len FROM cms_content_field_values WHERE content_id=23");
echo "B rs=".($rs?'ok':'false')."\n";
if ($rs) {
  while (!$rs->EOF()) {
    echo "id=".$rs->fields[0]." field=".$rs->fields[1]." len=".$rs->fields[2]."\n";
    $rs->MoveNext();
  }
  $rs->Close();
}

$rs = Execute("SELECT id, content_type_field_id, value FROM cms_content_field_values WHERE content_id=23 AND value LIKE '%CHEVAL%' LIMIT 1");
echo "C rs=".($rs?'ok':'false')."\n";
if ($rs && !$rs->EOF()) {
  $vid = $rs->fields[0];
  $fid = $rs->fields[1];
  $val = $rs->fields[2];
  echo "vid=$vid fid=$fid len=".strlen($val)."\n";
  $fkey = ExecuteScalar("SELECT `key` FROM cms_content_type_fields WHERE id=".(int)$fid);
  echo "fkey=$fkey\n";
  $arr = json_decode($val, true);
  if (!is_array($arr)) { echo "not json\n"; echo "DONE\n"; return; }
  $changed=0;
  foreach ($arr as $i=>&$item) {
    if (!is_array($item)) continue;
    $hay = ($item['title']??'').' '.($item['content']??'').' '.($item['href']??'');
    $hit = (stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false);
    echo "item[$i] hit=".($hit?'Y':'N')." status=".($item['status']??'?')." title=".($item['title']??'')." content=".($item['content']??'')."\n";
    if ($hit && (int)($item['status']??1)!==0) {
      $item['status']=0;
      $changed++;
    }
  }
  unset($item);
  if ($changed) {
    $new = json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    // use parameterized if available; else escape
    $conn = Conn();
    $ok = $conn->Execute("UPDATE cms_content_field_values SET value=".$conn->qstr($new)." WHERE id=".(int)$vid);
    echo "update_ok=".($ok?'yes':'no')." changed=$changed\n";
  } else {
    echo "no change needed\n";
  }
  // verify
  $rs2 = Execute("SELECT value FROM cms_content_field_values WHERE id=".(int)$vid);
  $val2 = $rs2 ? $rs2->fields[0] : '';
  $arr2 = json_decode($val2,true);
  foreach ($arr2 as $i=>$item) {
    $hay=($item['title']??'').' '.($item['content']??'');
    if (stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false) {
      echo "VERIFY[$i] status=".($item['status']??'?')."\n";
    }
  }
}
// clear caches
$cacheDirs = [__DIR__.'/_cache', __DIR__.'/tmp', sys_get_temp_dir()];
$cleared=[];
foreach ($cacheDirs as $dir) {
  if (!is_dir($dir)) continue;
  foreach (glob($dir.'/*home*') ?: [] as $p) { @unlink($p); $cleared[]=basename($p); }
  foreach (glob($dir.'/bahman_home*') ?: [] as $p) { @unlink($p); $cleared[]=basename($p); }
}
// also search common cache file names under httpdocs
foreach (glob(__DIR__.'/**/bahman_home*.html') ?: [] as $p) { @unlink($p); $cleared[]=$p; }
echo "cleared=".implode(',',array_unique($cleared))."\n";
echo "DONE\n";
