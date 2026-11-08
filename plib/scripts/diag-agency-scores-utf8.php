<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){
  $out=root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms --default-character-set=utf8mb4 -N -e ".escapeshellarg($sql)." 2>&1");
  return $out;
}
// dump as base64 to avoid panel UTF8 issues
$rows = mq("SELECT c.id, v.value FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id JOIN cms_content_field_values v ON v.content_id=c.id JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE t.`key`='agency' AND f.`key`='code' AND c.status=1");
file_put_contents('/tmp/agency_codes.tsv', $rows);
echo "codes_bytes=".strlen($rows)." lines=".substr_count($rows,"\n")."\n";
echo "b64=".base64_encode($rows)."\n";

$s1161 = mq("SELECT v.value FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=1161 AND f.`key`='scores'");
echo "scores1161_b64=".base64_encode(trim($s1161))."\n";
$code1161 = mq("SELECT v.value FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=1161 AND f.`key`='code'");
echo "code1161_b64=".base64_encode(trim($code1161))."\n";
$code1161hex=bin2hex(trim($code1161));
echo "code1161_hex=$code1161hex\n";
echo "DONE\n";
