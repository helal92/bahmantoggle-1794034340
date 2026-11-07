<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';

$contentId = 23;
$fieldKey = 'links'; // likely

// find field ids for content 23 with CHEVAL
$rs = Execute("SELECT v.id AS value_id, f.`key` AS field_key, v.value
FROM cms_content_field_values v
JOIN cms_content_type_fields f ON f.id=v.content_type_field_id
WHERE v.content_id=$contentId AND (v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%')");
echo "MATCHING FIELDS:\n";
$targets = [];
if ($rs) {
  while (!$rs->EOF()) {
    $f = $rs->fields;
    echo "value_id={$f['value_id']} key={$f['field_key']} len=".strlen($f['value'])."\n";
    $targets[] = ['id'=>$f['value_id'], 'key'=>$f['field_key'], 'value'=>$f['value']];
    $rs->MoveNext();
  }
  $rs->Close();
}

$changed = 0;
foreach ($targets as $t) {
  $decoded = json_decode($t['value'], true);
  if (!is_array($decoded)) {
    echo "skip non-json key={$t['key']}\n";
    continue;
  }
  $mod = false;
  foreach ($decoded as $i => &$item) {
    if (!is_array($item)) continue;
    $hay = ($item['title'] ?? '').' '.($item['content'] ?? '').' '.($item['href'] ?? '');
    if (stripos($hay, 'CHEVAL') !== false || mb_strpos($hay, 'شوال') !== false) {
      echo "FOUND item[$i] title=".($item['title']??'')." content=".($item['content']??'')." status=".($item['status']??'?')."\n";
      if ((int)($item['status'] ?? 1) !== 0) {
        $item['status'] = 0;
        $mod = true;
        $changed++;
      }
    }
  }
  unset($item);
  if ($mod) {
    $newJson = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $esc = addslashes($newJson);
    Execute("UPDATE cms_content_field_values SET value='$esc' WHERE id=".(int)$t['id']);
    echo "UPDATED value_id={$t['id']} key={$t['key']}\n";
  }
}

// also disable whole section content 23 if needed? No - only slide.
// clear home caches
$cacheDir = __DIR__ . '/_cache';
$cleared = [];
foreach (['bahman_home_page_v4.html','bahman_home_page_v3.html','bahman_home_page_v2.html','bahman_home_page.html'] as $f) {
  $p = "$cacheDir/$f";
  if (is_file($p)) { @unlink($p); $cleared[] = $f; }
}
// also any home*
foreach (glob("$cacheDir/*home*") ?: [] as $p) {
  @unlink($p);
  $cleared[] = basename($p);
}

echo "changed_items=$changed cleared=".implode(',', array_unique($cleared))."\n";

// verify
$val = ExecuteScalar("SELECT value FROM cms_content_field_values v
JOIN cms_content_type_fields f ON f.id=v.content_type_field_id
WHERE v.content_id=23 AND f.`key`='links' LIMIT 1");
$arr = json_decode($val, true);
echo "VERIFY links count=".count($arr)."\n";
foreach ($arr as $i=>$item) {
  $hay = ($item['title']??'').' '.($item['content']??'');
  if (stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false) {
    echo "slide[$i] status=".($item['status']??'?')." content=".($item['content']??'')." title=".($item['title']??'')."\n";
  }
}
echo "DONE\n";
