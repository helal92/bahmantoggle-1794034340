<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');

$action = $_GET['do'] ?? 'find';

chdir(__DIR__);
include_once 'autoload.php';

function out($s){ echo $s, "\n"; }

// Find section-1 link field for landing 22
$rows = ExecuteRows("
SELECT c.id AS section_id, v.id AS value_id, v.value
FROM cms_contents c
JOIN cms_content_type_fields fsk ON fsk.`key` = 'section_key'
JOIN cms_content_field_values sk ON sk.content_id = c.id AND sk.content_type_field_id = fsk.id AND sk.value = 'section-1'
JOIN cms_content_type_fields fl ON fl.`key` = 'link' AND fl.content_type_id = c.content_type_id
JOIN cms_content_field_values v ON v.content_id = c.id AND v.content_type_field_id = fl.id
WHERE c.parent_id = 22 AND c.status = '1'
LIMIT 5
");

if (!is_array($rows) || !$rows) {
  // try without status filter / alternate parent
  $rows = ExecuteRows("
  SELECT c.id AS section_id, v.id AS value_id, v.value
  FROM cms_contents c
  JOIN cms_content_type_fields fsk ON fsk.`key` = 'section_key'
  JOIN cms_content_field_values sk ON sk.content_id = c.id AND sk.content_type_field_id = fsk.id AND sk.value = 'section-1'
  JOIN cms_content_type_fields fl ON fl.`key` = 'link' AND fl.content_type_id = c.content_type_id
  JOIN cms_content_field_values v ON v.content_id = c.id AND v.content_type_field_id = fl.id
  WHERE c.parent_id = 22
  LIMIT 5
  ");
}

out('rows='.(is_array($rows)?count($rows):0));
if (!is_array($rows) || !$rows) {
  // broader search
  $hits = ExecuteRows("SELECT content_id, id AS value_id, CHAR_LENGTH(value) len FROM cms_content_field_values WHERE value LIKE '%CHEVAL%' OR value LIKE '%شوال%' LIMIT 20");
  out('broader_hits='.json_encode($hits, JSON_UNESCAPED_UNICODE));
  exit;
}

foreach ($rows as $row) {
  $sid = $row['section_id'];
  $vid = $row['value_id'];
  $raw = $row['value'];
  out("section_id=$sid value_id=$vid len=".strlen((string)$raw));
  $data = json_decode((string)$raw, true);
  if (!is_array($data)) { out('bad json'); continue; }
  $changed = false;
  foreach ($data as $i => $it) {
    if (!is_array($it)) continue;
    $title = (string)($it['title'] ?? '');
    $content = (string)($it['content'] ?? '');
    $status = $it['status'] ?? 1;
    $blob = $title.' '.$content;
    $match = (bool)preg_match('/CHEVAL|cheval|شوال/iu', $blob);
    out("[$i] status=$status title=".json_encode($title, JSON_UNESCAPED_UNICODE).' content='.json_encode(mb_substr($content,0,80), JSON_UNESCAPED_UNICODE).($match?' MATCH':''));
    if ($match && $action === 'disable') {
      if ((int)$status !== 0) {
        $data[$i]['status'] = 0;
        $changed = true;
        out("DISABLED index=$i");
      } else {
        out("already disabled index=$i");
      }
    }
  }
  if ($action === 'disable' && $changed) {
    $new = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $esc = addslashes($new);
    Execute("UPDATE cms_content_field_values SET value = '{$esc}' WHERE id = '".(int)$vid."'");
    out('UPDATED value_id='.$vid.' new_len='.strlen($new));
    // bust homepage caches
    @unlink(sys_get_temp_dir().'/bahman_home_page_v4.html');
    @unlink('/tmp/bahman_home_page_v4.html');
    out('cache bust attempted');
  }
}
out('DONE');
