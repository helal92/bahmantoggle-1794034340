#!/bin/bash
exec > /tmp/cheval_work.log 2>&1
chmod a+r /tmp/cheval_work.log
set -x
date
PASS=$(cat /etc/psa/.psa.shadow)
MYSQL="mysql -uadmin -p${PASS} bmcars_cms -N"

echo '=== hits ==='
$MYSQL -e "SELECT v.content_id, CHAR_LENGTH(v.value) FROM cms_content_field_values v WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%' OR v.value LIKE '%Cheval%' LIMIT 20;"

echo '=== section-1 under 22 ==='
$MYSQL -e "
SELECT c.id, c.parent_id, c.status, CHAR_LENGTH(v.value)
FROM cms_contents c
JOIN cms_content_type_fields fsk ON fsk.\`key\`='section_key'
JOIN cms_content_field_values sk ON sk.content_id=c.id AND sk.content_type_field_id=fsk.id AND sk.value='section-1'
JOIN cms_content_type_fields fl ON fl.\`key\`='link' AND fl.content_type_id=c.content_type_id
JOIN cms_content_field_values v ON v.content_id=c.id AND v.content_type_field_id=fl.id
WHERE c.parent_id=22;
"

# Export full JSON to file for processing
SID=$($MYSQL -e "
SELECT c.id
FROM cms_contents c
JOIN cms_content_type_fields fsk ON fsk.\`key\`='section_key'
JOIN cms_content_field_values sk ON sk.content_id=c.id AND sk.content_type_field_id=fsk.id AND sk.value='section-1'
WHERE c.parent_id=22
LIMIT 1;")
echo "SID=$SID"

LINK_FIELD=$($MYSQL -e "
SELECT fl.id FROM cms_contents c
JOIN cms_content_type_fields fl ON fl.\`key\`='link' AND fl.content_type_id=c.content_type_id
WHERE c.id=$SID LIMIT 1;")
echo "LINK_FIELD=$LINK_FIELD"

$MYSQL -e "SELECT value FROM cms_content_field_values WHERE content_id=$SID AND content_type_field_id=$LINK_FIELD LIMIT 1;" > /tmp/cheval_links.json
# value may have tabs; use batch
$MYSQL --batch --raw -e "SELECT value FROM cms_content_field_values WHERE content_id=$SID AND content_type_field_id=$LINK_FIELD LIMIT 1;" | tail -n +2 > /tmp/cheval_links.json
wc -c /tmp/cheval_links.json
chmod a+r /tmp/cheval_links.json

python3 <<'PY'
import json,re
raw=open('/tmp/cheval_links.json','r',encoding='utf-8',errors='ignore').read().strip()
# sometimes mysql adds escaping
try:
  data=json.loads(raw)
except Exception as e:
  print('json err',e,'rawhead',raw[:200])
  raise
print('items',len(data) if isinstance(data,list) else type(data))
for i,it in enumerate(data if isinstance(data,list) else []):
  title=str(it.get('title') or '')
  content=str(it.get('content') or '')
  status=it.get('status',1)
  img=str(it.get('image') or '')
  img2=str(it.get('image2') or '')
  blob=' '.join([title,content])
  mark=''
  if re.search(r'CHEVAL|cheval|شوال|Cheval', blob, re.I):
    mark=' <== MATCH'
  print(f'{i}: status={status} title={title!r} content={content[:60]!r} image={img[:40]}{mark}')
PY

echo DONE
date
