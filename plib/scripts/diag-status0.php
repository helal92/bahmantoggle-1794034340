<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){return root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -e ".escapeshellarg($sql)." 2>&1");}
echo ascii(mq('DESCRIBE cms_content_field_values;'))."\n";
echo "landing status counts:\n".ascii(mq('SELECT status, COUNT(*) c FROM cms_contents WHERE content_type_id=4 GROUP BY status;'))."\n";
echo "recently disabled landings:\n".ascii(mq("SELECT id, status, m_date, m_bp_user_id FROM cms_contents WHERE content_type_id=4 AND status=0 ORDER BY m_date DESC LIMIT 25;"))."\n";
// title field for 1075
echo "fields schema sample:\n".ascii(mq('SHOW COLUMNS FROM cms_content_field_values;'))."\n";
echo "title1075:\n".ascii(mq("SELECT * FROM cms_content_field_values WHERE content_id=1075 LIMIT 5\\G"))."\n";
echo "DONE\n";
