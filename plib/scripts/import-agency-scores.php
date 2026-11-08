<?php
$fm = '/opt/psa/admin/bin/filemng';
function root($cmd) {
  global $fm;
  return (string)shell_exec($fm . ' root exec / bash -c ' . escapeshellarg($cmd) . ' --allow-root 2>&1');
}
function mq($sql) {
  return root("MYSQL_PWD=$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms --default-character-set=utf8mb4 -e " . escapeshellarg($sql) . " 2>&1");
}

$payload = json_decode(base64_decode(file_get_contents(__DIR__.'/agency_scores_payload.b64')), true);
if (!is_array($payload)) { echo "BAD_PAYLOAD\n"; exit(1); }

// resolve scores field id for agency type
$fieldId = trim(root("MYSQL_PWD=$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -N -e \"SELECT id FROM cms_content_type_fields WHERE content_type_id=3 AND \\`key\\`='scores' LIMIT 1\" 2>&1"));
$typeId = '3';
if (!ctype_digit($fieldId)) { echo "NO_FIELD field=$fieldId\n"; exit(1); }
echo "scores_field_id=$fieldId count=".count($payload)."\n";

$ok=0; $fail=0; $errors=[];
foreach ($payload as $item) {
  $aid = (int)$item['agency_id'];
  $json = json_encode($item['scores'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($aid <= 0 || $json === false) { $fail++; $errors[]="bad item $aid"; continue; }
  $jsonSql = addslashes($json);
  // upsert field value
  $sql = "INSERT INTO cms_content_field_values (content_id, content_type_id, content_type_field_id, value, company_id, status)
          VALUES ($aid, $typeId, $fieldId, '$jsonSql', 1, 1)
          ON DUPLICATE KEY UPDATE value=VALUES(value), status=1";
  $res = mq($sql);
  if (stripos($res, 'ERROR') !== false) {
    $fail++;
    $errors[] = "id=$aid ".$res;
    continue;
  }
  // touch m_date
  mq("UPDATE cms_contents SET m_date=NOW() WHERE id=$aid");
  $ok++;
}
echo "OK=$ok FAIL=$fail\n";
if ($errors) echo "ERRORS:\n".implode("\n", array_slice($errors,0,10))."\n";

// verify a few
$sampleIds = [];
foreach (array_slice($payload, 0, 3) as $it) $sampleIds[] = (int)$it['agency_id'];
$sampleIds[] = 1161;
$ids = implode(',', array_unique($sampleIds));
$ver = root("MYSQL_PWD=$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms --default-character-set=utf8mb4 -N -e \"SELECT content_id, LEFT(value,180) FROM cms_content_field_values WHERE content_type_field_id=$fieldId AND content_id IN ($ids)\" 2>&1");
echo "VERIFY_B64=".base64_encode($ver)."\n";
echo "DONE\n";
