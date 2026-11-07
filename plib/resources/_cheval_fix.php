<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';

$conn = Conn();
echo "db=".$conn->database." err=".$conn->ErrorMsg()."\n";

$sql = "SELECT id, content_type_field_id, value FROM cms_content_field_values WHERE content_id=23 AND value LIKE '%CHEVAL%' LIMIT 1";
$rs = $conn->Execute($sql);
echo "rs=".($rs?'ok':'false')." err=".$conn->ErrorMsg()."\n";
if (!$rs || $rs->EOF) {
  // try ExecuteRows helper
  if (function_exists(__NAMESPACE__.'\\ExecuteRows')) {
    $rows = ExecuteRows($sql);
    echo "ExecuteRows=".json_encode(array_map(function($r){return ['id'=>$r['id']??$r[0]??null,'flen'=>isset($r['value'])?strlen($r['value']):null];}, $rows?:[]))."\n";
  }
  echo "FAIL read\nDONE\n";
  return;
}

$vid = $rs->fields['id'] ?? $rs->fields[0];
$fid = $rs->fields['content_type_field_id'] ?? $rs->fields[1];
$val = $rs->fields['value'] ?? $rs->fields[2];
$rs->Close();
echo "vid=$vid fid=$fid len=".strlen($val)."\n";

$arr = json_decode($val, true);
if (!is_array($arr)) { echo "not json\nDONE\n"; return; }

$changed = 0;
foreach ($arr as $i => &$item) {
  if (!is_array($item)) continue;
  $hay = ($item['title'] ?? '') . ' ' . ($item['content'] ?? '') . ' ' . ($item['href'] ?? '');
  $hit = (stripos($hay, 'CHEVAL') !== false || mb_strpos($hay, 'شوال') !== false);
  echo "item[$i] hit=".($hit?'Y':'N')." status=".($item['status']??'?')." title=".($item['title']??'')." content=".($item['content']??'')."\n";
  if ($hit && (int)($item['status'] ?? 1) !== 0) {
    $item['status'] = 0;
    $changed++;
  }
}
unset($item);

if ($changed) {
  $new = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  $ok = $conn->Execute("UPDATE cms_content_field_values SET value=".$conn->qstr($new)." WHERE id=".(int)$vid);
  echo "update=".($ok?'ok':'fail')." err=".$conn->ErrorMsg()." changed=$changed\n";
} else {
  echo "already disabled\n";
}

$rs = $conn->Execute("SELECT value FROM cms_content_field_values WHERE id=".(int)$vid);
$val2 = $rs ? ($rs->fields['value'] ?? $rs->fields[0]) : '';
$arr2 = json_decode($val2, true);
foreach ($arr2 as $i=>$item) {
  $hay = ($item['title']??'').' '.($item['content']??'');
  if (stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false) {
    echo "VERIFY[$i] status=".($item['status']??'?')."\n";
  }
}

// clear home html caches anywhere under httpdocs
$cleared = [];
$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $n = $f->getFilename();
  if (stripos($n, 'home') !== false && (substr($n,-5)==='.html' || substr($n,-6)==='.cache' || substr($n,-4)==='.php')) {
    // only cache-looking
    if (preg_match('/bahman_home|home_page|homepage/i', $n)) {
      @unlink($f->getPathname());
      $cleared[] = $n;
    }
  }
}
echo "cleared=".implode(',', $cleared)."\n";
echo "DONE\n";
