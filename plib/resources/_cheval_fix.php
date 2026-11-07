<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';

echo "funcs Execute=". (function_exists(__NAMESPACE__.'\\Execute')||function_exists('Execute')?'yes':'no') ."\n";
echo "ExecuteRows=". (function_exists(__NAMESPACE__.'\\ExecuteRows')?'yes':'no') ."\n";
echo "ExecuteScalar=". (function_exists(__NAMESPACE__.'\\ExecuteScalar')?'yes':'no') ."\n";

// Config DB
try {
  $c = Config('CONNECTION');
  echo "CONNECTION=".json_encode($c['DB'] ?? $c, JSON_UNESCAPED_UNICODE)."\n";
} catch (\Throwable $e) {
  echo "config err ".$e->getMessage()."\n";
}

$db = ExecuteScalar('SELECT DATABASE()');
echo "database=$db\n";
$cnt = ExecuteScalar("SELECT COUNT(*) FROM cms_content_field_values WHERE value LIKE '%شوال%' OR value LIKE '%CHEVAL%'");
echo "cheval_count=$cnt\n";

$sectionKeys = ExecuteScalar("SELECT COUNT(*) FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE f.`key`='section_key' AND v.value='section-1'");
echo "section1_count=$sectionKeys\n";

// list section-1 content ids
$rs = Execute("SELECT c.id, c.parent_id, c.content_type_id, c.status FROM cms_contents c
JOIN cms_content_field_values sk ON sk.content_id=c.id
JOIN cms_content_type_fields fsk ON fsk.id=sk.content_type_field_id AND fsk.`key`='section_key' AND sk.value='section-1'
ORDER BY c.id DESC LIMIT 20");
echo "section1 rows:\n";
if ($rs) {
  while (!$rs->EOF()) {
    $f=$rs->fields;
    echo json_encode($f, JSON_UNESCAPED_UNICODE)."\n";
    $rs->MoveNext();
  }
  $rs->Close();
}

// landing 22
$p = ExecuteScalar("SELECT id FROM cms_contents WHERE id=22 OR old_cms_id=22 LIMIT 1");
echo "landing22=$p\n";
$child = ExecuteScalar("SELECT COUNT(*) FROM cms_contents WHERE parent_id=22");
echo "children_of_22=$child\n";

// dump titles from any link json containing HEV or 6
$rs = Execute("SELECT content_id, LEFT(value,200) FROM cms_content_field_values WHERE value LIKE '%HEV%' OR value LIKE '%شوال%' LIMIT 10");
echo "HEV/شوال samples:\n";
if ($rs) {
  while (!$rs->EOF()) {
    echo json_encode($rs->fields, JSON_UNESCAPED_UNICODE)."\n";
    $rs->MoveNext();
  }
  $rs->Close();
}
echo "DONE\n";
