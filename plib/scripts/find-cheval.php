<?php
echo "=== FIND CHEVAL ===\n";
echo "panel_local:\n".trim(shell_exec('curl -sk -o /tmp/panel.html -w "ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code} size:%{size_download}\n" --max-time 30 -H "Host: sitepanel.bahman.ir" https://172.20.14.76/dashboard'))."\n";
echo "panel_title=".trim(shell_exec("grep -o '<title>[^<]*' /tmp/panel.html | head -1"))."\n";

# DB via plesk
$out = [];
$cmds = [
  // find in field values
  "plesk db \"SELECT v.id, v.content_id, v.value, f.\`key\` FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%' OR v.value LIKE '%cheval%' LIMIT 50\" 2>&1",
  "mysql -N -e \"SHOW DATABASES\" 2>/dev/null | head -30",
];
foreach ($cmds as $c) {
  echo ">> $c\n";
  echo trim((string)@shell_exec($c))."\n----\n";
}

# try common db name from earlier context: bmcars_cms
foreach (['bmcars_cms','bahman','bahar_cms'] as $db) {
  $q = "SELECT v.content_id, f.\`key\`, LEFT(v.value,180) AS val FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.value LIKE '%CHEVAL%' OR v.value LIKE '%شوال%' OR v.value LIKE '%cheval%' OR v.value LIKE '%HEV%' LIMIT 40";
  $r = trim((string)@shell_exec('plesk db '.$db.' -e '.escapeshellarg($q).' 2>&1'));
  if ($r !== '' && stripos($r,'ERROR')===false) {
    echo "DB $db:\n$r\n";
  } else {
    echo "DB $db: ".substr($r,0,200)."\n";
  }
}
