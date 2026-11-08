<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){return root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -e ".escapeshellarg($sql)." 2>&1");}

echo "product 551 row:\n".ascii(mq('SELECT id,status,content_type_id FROM cms_contents WHERE id=551;'))."\n";
echo "field values related gallery/video:\n".ascii(mq("SELECT f.id,f.`key`, LEFT(v.value,200) val FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=551 AND (f.`key` LIKE '%gall%' OR f.`key` LIKE '%video%' OR f.`key` LIKE '%aparat%' OR f.`key` LIKE '%media%' OR f.`key` LIKE '%file%') ORDER BY f.`key`;"))."\n";
echo "all field keys for product type:\n".ascii(mq("SELECT id,`key`,title,field_type FROM cms_content_type_fields WHERE content_type_id=(SELECT content_type_id FROM cms_contents WHERE id=551) ORDER BY id;"))."\n";

// HTML snippet around gallery from product page
$code=trim(shell_exec('curl -s -o /tmp/p551.html -w "%{http_code}" --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" "http://172.20.14.76/product/551"'));
echo "product_page_http=$code size=".filesize('/tmp/p551.html')."\n";
echo "gallery markers:\n".ascii(shell_exec('grep -nE "section-gallery|gallery|video|aparat|mp4|swiper-gallery" /tmp/p551.html | head -60'))."\n";
echo "DONE\n";
