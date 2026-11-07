<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';
$conn = Conn();

$rs = $conn->Execute("SHOW COLUMNS FROM cms_content_field_values");
echo "COLUMNS:\n";
while ($rs && !$rs->EOF) {
  echo ($rs->fields['Field'] ?? $rs->fields[0])." ".($rs->fields['Type']??$rs->fields[1])."\n";
  $rs->MoveNext();
}

$rs = $conn->Execute("SELECT * FROM cms_content_field_values WHERE content_id=23 AND value LIKE '%CHEVAL%' LIMIT 1");
echo "ROW keys: ";
if ($rs && !$rs->EOF) {
  $keys = [];
  foreach ($rs->fields as $k=>$v) if (!is_int($k)) $keys[$k] = is_string($v)?('len='.strlen($v)):$v;
  echo json_encode($keys, JSON_UNESCAPED_UNICODE)."\n";
  $val = $rs->fields['value'];
  $fid = $rs->fields['content_type_field_id'];
  $cid = $rs->fields['content_id'];
  $arr = json_decode($val, true);
  foreach ($arr as $i=>$item) {
    echo "item[$i] status=".($item['status']??'?')." title=".($item['title']??'')." content=".($item['content']??'')."\n";
  }
  $changed=0;
  foreach ($arr as &$item) {
    if (!is_array($item)) continue;
    $hay=($item['title']??'').' '.($item['content']??'');
    if ((stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false) && (int)($item['status']??1)!==0) {
      $item['status']=0; $changed++;
    }
  }
  unset($item);
  if ($changed) {
    $new = json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $sql = "UPDATE cms_content_field_values SET value=".$conn->qstr($new)." WHERE content_id=".(int)$cid." AND content_type_field_id=".(int)$fid;
    $ok = $conn->Execute($sql);
    echo "update=".($ok?'ok':'fail')." err=".$conn->ErrorMsg()." changed=$changed\n";
  } else echo "nochange\n";

  $rs2 = $conn->Execute("SELECT value FROM cms_content_field_values WHERE content_id=".(int)$cid." AND content_type_field_id=".(int)$fid);
  $arr2 = json_decode($rs2->fields['value'] ?? $rs2->fields[0], true);
  foreach ($arr2 as $i=>$item) {
    $hay=($item['title']??'').' '.($item['content']??'');
    if (stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false) {
      echo "VERIFY[$i] status=".($item['status']??'?')."\n";
    }
  }
} else {
  echo "no row err=".$conn->ErrorMsg()."\n";
}

foreach (['bahman_home_page_v4.html','bahman_home_page_v3.html','bahman_home_page_v2.html'] as $f) {
  $p=__DIR__.'/_cache/'.$f;
  if (is_file($p)) { unlink($p); echo "rm $f\n"; }
}
echo "DONE\n";
