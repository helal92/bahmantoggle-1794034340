<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){return root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -e ".escapeshellarg($sql)." 2>&1");}

echo "gallery children of 551:\n".ascii(mq("SELECT c.id,c.status,c.parent_id,c.content_type_id FROM cms_contents c WHERE c.parent_id=551 OR c.id IN (SELECT content_id FROM cms_content_field_values WHERE value LIKE '%551%' LIMIT 0);"))."\n";
echo "by parent:\n".ascii(mq("SELECT id,status,content_type_id,parent_id FROM cms_contents WHERE parent_id=551;"))."\n";
echo "type gallery id:\n".ascii(mq("SELECT id,`key` FROM cms_content_types WHERE `key`='gallery';"))."\n";

// children gallery type
echo "gallery kids:\n".ascii(mq("SELECT c.id,c.status FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id WHERE c.parent_id=551 AND t.`key`='gallery';"))."\n";

$kids = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -N -e ".escapeshellarg("SELECT c.id FROM cms_contents c JOIN cms_content_types t ON t.id=c.content_type_id WHERE c.parent_id=551 AND t.`key`='gallery';")." 2>&1");
foreach (preg_split('/\s+/', trim($kids)) as $id) {
  if (!ctype_digit($id)) continue;
  echo "--- gallery $id values ---\n";
  echo ascii(mq("SELECT f.`key`, LEFT(v.value,300) val FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=$id;"))."\n";
}

$vid='58ac2d10-51e9-11f1-9b58-005056b972d8';
foreach ([
  "http://172.20.14.76/media/$vid.mp4",
  "https://sitefile.bahman.ir/$vid",
  "https://sitefile.bahman.ir/v/$vid.mp4",
  "http://172.20.14.76/",
] as $u) {
  // host resolve local for sitefile
}
echo "probe local media:\n".ascii(shell_exec('curl -sI --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/media/'.$vid.'.mp4" | tr -d "\\r" | head -15'));
echo "\nprobe sitefile raw:\n".ascii(shell_exec('curl -skI --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/'.$vid.'" | tr -d "\\r" | head -15'));
echo "\nprobe sitefile /v/:\n".ascii(shell_exec('curl -skI --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/v/'.$vid.'.mp4" | tr -d "\\r" | head -15'));
echo "DONE\n";
