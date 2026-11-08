<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){
  return root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -e ".escapeshellarg($sql)." 2>&1");
}
echo "DESC cms_contents:\n".ascii(mq('DESCRIBE cms_contents;'))."\n";
echo "row1075:\n".ascii(mq('SELECT * FROM cms_contents WHERE id IN (1075,22,4200,100)\\G'))."\n";
echo "types:\n".ascii(mq('SELECT id, `key`, title FROM cms_content_types LIMIT 30;'))."\n";
echo "vals1075:\n".ascii(mq("SELECT f.`key`, LEFT(v.value,100) v FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.field_id WHERE v.content_id=1075 LIMIT 30;"))."\n";
echo "vals22:\n".ascii(mq("SELECT f.`key`, LEFT(v.value,60) v FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.field_id WHERE v.content_id=22 LIMIT 15;"))."\n";
echo "DONE\n";
