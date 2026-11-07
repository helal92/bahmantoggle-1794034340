<?php
$log = '/tmp/cheval_disable.log';
file_put_contents($log, "start ".date('c')."\n");
chmod($log, 0644);

function out($s){ file_put_contents('/tmp/cheval_disable.log', $s."\n", FILE_APPEND); echo $s."\n"; }

// Connect via local mysql as root (plesk allows)
$mysqli = @new mysqli('localhost', 'admin', '', 'bmcars_cms');
if ($mysqli->connect_errno) {
  // try reading password from .my.cnf or plesk
  $pass = trim((string)@shell_exec('cat /etc/psa/.psa.shadow 2>/dev/null'));
  $mysqli = @new mysqli('localhost', 'admin', $pass, 'bmcars_cms');
}
if ($mysqli->connect_errno) {
  // last resort: parse from domain
  out('connect fail: '.$mysqli->connect_error);
  // use CLI
  $out = shell_exec('plesk db bmcars_cms -e "SELECT DATABASE();" 2>&1');
  out('plesk db: '.$out);
  // Write SQL script and run via plesk db
  $findSql = <<<'SQL'
SELECT v.content_id, CHAR_LENGTH(v.value) len, LEFT(v.value, 300) preview
FROM cms_content_field_values v
WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%' OR v.value LIKE '%Cheval%'
LIMIT 30;
SQL;
  file_put_contents('/tmp/cheval_find.sql', $findSql);
  out(shell_exec('plesk db bmcars_cms < /tmp/cheval_find.sql 2>&1'));
  exit(0);
}
out('connected');
$q = $mysqli->query("SELECT v.content_id, CHAR_LENGTH(v.value) len, LEFT(v.value, 400) preview FROM cms_content_field_values v WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%' OR v.value LIKE '%Cheval%' LIMIT 20");
while ($q && ($r = $q->fetch_assoc())) {
  out(json_encode($r, JSON_UNESCAPED_UNICODE));
}
