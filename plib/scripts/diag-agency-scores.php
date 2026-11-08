<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){return root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms --default-character-set=utf8mb4 -e ".escapeshellarg($sql)." 2>&1");}

echo "agency type:\n".ascii(mq("SELECT id,`key`,title FROM cms_content_types WHERE `key` IN ('agency','scores','score');"))."\n";
echo "agency fields:\n".ascii(mq("SELECT id,`key`,title FROM cms_content_type_fields WHERE content_type_id=(SELECT id FROM cms_content_types WHERE `key`='agency') ORDER BY id;"))."\n";
echo "scores fields:\n".ascii(mq("SELECT id,`key`,title FROM cms_content_type_fields WHERE content_type_id=(SELECT id FROM cms_content_types WHERE `key`='scores') ORDER BY id;"))."\n";

echo "agency 1161:\n".ascii(mq("SELECT id,status,content_type_id FROM cms_contents WHERE id=1161;"))."\n";
echo "agency 1161 values:\n".ascii(mq("SELECT f.`key`, LEFT(v.value,200) val FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=1161;"))."\n";
echo "children of 1161:\n".ascii(mq("SELECT c.id,c.status,t.`key` FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id WHERE c.parent_id=1161;"))."\n";

// score children values
$kids=trim(root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -N -e \"SELECT c.id FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id WHERE c.parent_id=1161 AND t.\\`key\\`='scores';\" 2>&1"));
foreach (preg_split('/\s+/', $kids) as $id) {
  if (!ctype_digit($id)) continue;
  echo "score child $id:\n".ascii(mq("SELECT f.`key`, v.value FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=$id;"))."\n";
}

echo "agency 4279 values code:\n".ascii(mq("SELECT f.`key`, LEFT(v.value,120) val FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=4279;"))."\n";
echo "children 4279:\n".ascii(mq("SELECT c.id,t.`key` FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id WHERE c.parent_id=4279;"))."\n";

// how code field is keyed
echo "sample codes:\n".ascii(mq("SELECT c.id, v.value AS code FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id JOIN cms_content_field_values v ON v.content_id=c.id JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE t.`key`='agency' AND f.`key` IN ('code','agency_code','kod','کد') LIMIT 20;"))."\n";
echo "code field keys containing code:\n".ascii(mq("SELECT id,`key`,title FROM cms_content_type_fields WHERE content_type_id=(SELECT id FROM cms_content_types WHERE `key`='agency') AND (`key` LIKE '%code%' OR `key` LIKE '%kod%' OR title LIKE '%کد%');"))."\n";
echo "DONE\n";
