<?php
echo "=== cheval find (psaadm) ===\n";
echo trim((string)shell_exec('plesk db -e "SHOW DATABASES" 2>&1 | grep -iE "bmcars|bahman|cms"'))."\n";
echo "----\n";
$sql = <<<'SQL'
SELECT v.content_id, CHAR_LENGTH(v.value) AS len,
  CASE
    WHEN v.value LIKE '%CHEVAL%' THEN 'CHEVAL'
    WHEN v.value LIKE '%شوال%' THEN 'SHIVAL'
    WHEN v.value LIKE '%Cheval%' THEN 'Cheval'
    ELSE 'other'
  END AS hit
FROM cms_content_field_values v
WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%' OR v.value LIKE '%Cheval%' OR v.value LIKE '%cheval%'
LIMIT 30;
SQL;
file_put_contents('/tmp/cheval_q.sql', $sql);
echo trim((string)shell_exec('plesk db bmcars_cms < /tmp/cheval_q.sql 2>&1'))."\n";
echo "---- section-1 ----\n";
$sql2 = <<<'SQL'
SELECT c.id, c.parent_id, c.status, CHAR_LENGTH(v.value) AS link_len
FROM cms_contents c
JOIN cms_content_type_fields fsk ON fsk.`key`='section_key'
JOIN cms_content_field_values sk ON sk.content_id=c.id AND sk.content_type_field_id=fsk.id AND sk.value='section-1'
JOIN cms_content_type_fields fl ON fl.`key`='link' AND fl.content_type_id=c.content_type_id
JOIN cms_content_field_values v ON v.content_id=c.id AND v.content_type_field_id=fl.id
WHERE c.parent_id=22 OR c.parent_id IN (SELECT id FROM cms_contents WHERE old_cms_id=22)
LIMIT 20;
SQL;
file_put_contents('/tmp/cheval_q2.sql', $sql2);
echo trim((string)shell_exec('plesk db bmcars_cms < /tmp/cheval_q2.sql 2>&1'))."\n";
echo "---- log ----\n";
echo trim((string)@file_get_contents('/tmp/cheval_disable.log'))."\n";
