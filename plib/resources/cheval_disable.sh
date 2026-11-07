#!/bin/bash
exec > /tmp/cheval_disable.log 2>&1
chmod a+r /tmp/cheval_disable.log || true
set -x
date

# Discover DB name/user from siteapi or bahman config
php -r '
$files=["/var/www/vhosts/bahman.ir/httpdocs/ewcfg14.php","/var/www/vhosts/bahman.ir/httpdocs/ewcfg.php","/var/www/vhosts/siteapi.bahman.ir/httpdocs/ewcfg14.php","/var/www/vhosts/siteapi.bahman.ir/httpdocs/ewcfg.php"];
foreach($files as $f){ if(is_file($f)){ echo "FILE $f\n"; echo substr(file_get_contents($f),0,2500),"\n----\n"; } }
' 2>/dev/null || true

# Prefer plesk db credentials for domain
echo "DBS:"; mysql -N -e "SHOW DATABASES" 2>/dev/null | grep -iE 'bmcars|bahman|cms' || plesk db -e "SHOW DATABASES" 2>/dev/null | grep -iE 'bmcars|bahman|cms'

DB=$(mysql -N -e "SHOW DATABASES" 2>/dev/null | grep -E '^bmcars_cms$' | head -1)
if [ -z "$DB" ]; then DB=$(plesk db -e "SHOW DATABASES" 2>/dev/null | awk "/bmcars_cms/{print \$1}" | head -1); fi
echo "DB=$DB"

run_sql() {
  local sql="$1"
  if command -v mysql >/dev/null; then
    mysql -N "$DB" -e "$sql" 2>/dev/null || plesk db "$DB" -e "$sql" 2>/dev/null
  else
    plesk db "$DB" -e "$sql" 2>/dev/null
  fi
}

# Find field id for link on content_section, and rows mentioning Cheval/شوال
run_sql "SELECT id, \`key\`, field_type_id FROM cms_content_type_fields WHERE \`key\` IN ('link','section_key','title') LIMIT 30;"
run_sql "SELECT v.content_id, LEFT(v.value,200) FROM cms_content_field_values v WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%Cheval%' OR v.value LIKE '%شوال%' OR v.value LIKE '%cheval%' LIMIT 20;"

# Dump section-1 link JSON length and titles
run_sql "
SELECT c.id AS section_id, LEFT(v.value, 500)
FROM cms_contents c
JOIN cms_content_field_values sk ON sk.content_id=c.id
JOIN cms_content_type_fields fsk ON fsk.id=sk.content_type_field_id AND fsk.\`key\`='section_key'
JOIN cms_content_field_values v ON v.content_id=c.id
JOIN cms_content_type_fields fl ON fl.id=v.content_type_field_id AND fl.\`key\`='link'
WHERE sk.value='section-1' AND c.parent_id IN (
  SELECT IFNULL(old_cms_id,id) FROM cms_contents WHERE id=22 OR old_cms_id=22
)
LIMIT 5;
"

# Also parent_id = 22 directly
run_sql "
SELECT c.id, c.parent_id, c.status, sk.value AS section_key, CHAR_LENGTH(v.value) AS link_len
FROM cms_contents c
LEFT JOIN cms_content_field_values sk ON sk.content_id=c.id AND sk.content_type_field_id=(SELECT id FROM cms_content_type_fields WHERE \`key\`='section_key' LIMIT 1)
LEFT JOIN cms_content_field_values v ON v.content_id=c.id AND v.content_type_field_id=(SELECT id FROM cms_content_type_fields WHERE \`key\`='link' AND content_type_id=c.content_type_id LIMIT 1)
WHERE c.parent_id=22 OR c.id=22
LIMIT 30;
"

echo DONE_FIND
date
