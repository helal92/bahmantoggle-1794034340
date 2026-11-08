<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
function mq($sql){return root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -e ".escapeshellarg($sql)." 2>&1");}
echo "before:\n".ascii(mq('SELECT id,status,m_date FROM cms_contents WHERE id=1075;'))."\n";
echo ascii(mq("UPDATE cms_contents SET status=1, m_date=NOW() WHERE id=1075 AND content_type_id=4;"))."\n";
echo "after:\n".ascii(mq('SELECT id,status,m_date FROM cms_contents WHERE id=1075;'))."\n";
// clear home/page caches if any
root('rm -f /var/www/vhosts/bahman.ir/httpdocs/cache/bahman_home_page*.html /tmp/bahman_home_page*.html 2>/dev/null; echo cache_cleared');
$code=trim(shell_exec('curl -s -o /tmp/p1075.body -w "%{http_code}" --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" "http://172.20.14.76/page/1075"'));
$title=preg_replace('/[^\x20-\x7E]/','?', trim((string)shell_exec('grep -oE "<title>[^<]*" /tmp/p1075.body | head -1')));
echo "http=$code title=$title\n";
echo "other_disabled_landings:\n".ascii(mq('SELECT id,status,m_date FROM cms_contents WHERE content_type_id=4 AND status=0;'))."\n";
echo "DONE\n";
