<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';

echo "=== ALL FIELDS content 23 ===\n";
$rs = Execute("SELECT v.id, f.`key`, CHAR_LENGTH(v.value) AS len, LEFT(v.value,120) AS preview FROM cms_content_field_values v INNER JOIN cms_content_type_fields f ON f.id = v.content_type_field_id WHERE v.content_id = 23 ORDER BY f.`key`");
if ($rs) {
  while (!$rs->EOF()) {
    $row = [];
    foreach ($rs->fields as $k=>$v) { if (!is_int($k)) $row[$k]=$v; }
    echo json_encode($row, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";
    $rs->MoveNext();
  }
  $rs->Close();
} else {
  echo "rs=false\n";
}

echo "=== SEARCH CHEVAL ascii ===\n";
$n = ExecuteScalar("SELECT COUNT(*) FROM cms_content_field_values WHERE content_id=23 AND value LIKE '%CHEVAL%'");
echo "count23=$n\n";
$vid = ExecuteScalar("SELECT id FROM cms_content_field_values WHERE content_id=23 AND value LIKE '%CHEVAL%' LIMIT 1");
echo "value_id=$vid\n";
if ($vid) {
  $full = ExecuteScalar("SELECT value FROM cms_content_field_values WHERE id=".(int)$vid);
  echo "full_len=".strlen($full)."\n";
  $arr = json_decode($full, true);
  echo "json_ok=".(is_array($arr)?'yes':'no')." items=".(is_array($arr)?count($arr):0)."\n";
  if (is_array($arr)) {
    foreach ($arr as $i=>$item) {
      echo "item[$i] status=".($item['status']??'?')." title=".($item['title']??'')." content=".($item['content']??'')." image=".($item['image']??'')."\n";
    }
  }
}
echo "DONE\n";
