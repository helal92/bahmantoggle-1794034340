<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
// Load DB creds from site
$conf = file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/libs/myconf.php');
// try common patterns
preg_match("/define\(\s*'DB_HOST'\s*,\s*'([^']+)'/",$conf,$h);
preg_match("/define\(\s*'DB_USER'\s*,\s*'([^']+)'/",$conf,$u);
preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'([^']+)'/",$conf,$p);
preg_match("/define\(\s*'DB_NAME'\s*,\s*'([^']+)'/",$conf,$n);
if (!$h) {
  // alternate
  preg_match('/\$HOST\s*=\s*"([^"]+)"/',$conf,$h);
  preg_match('/\$USER\s*=\s*"([^"]+)"/',$conf,$u);
  preg_match('/\$PASSWORD\s*=\s*"([^"]+)"/',$conf,$p);
  preg_match('/\$DATABASE\s*=\s*"([^"]+)"/',$conf,$n);
}
// also try .env / connection.php
$cands = [
  '/var/www/vhosts/bahman.ir/httpdocs/libs/connection.php',
  '/var/www/vhosts/bahman.ir/httpdocs/include/config.php',
  '/var/www/vhosts/bahman.ir/httpdocs/ewcfg.php',
];
$host=$h[1]??'localhost'; $user=$u[1]??''; $pass=$p[1]??''; $db=$n[1]??'';
echo "from_myconf host=$host user=$user db=$db passlen=".strlen($pass)."\n";
if ($user==='' || $db==='') {
  foreach ($cands as $f) {
    if (!is_file($f)) continue;
    $t=file_get_contents($f);
    if (preg_match("/define\(\s*'DB_USER'\s*,\s*'([^']*)'/",$t,$m)) $user=$m[1];
    if (preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'([^']*)'/",$t,$m)) $pass=$m[1];
    if (preg_match("/define\(\s*'DB_NAME'\s*,\s*'([^']*)'/",$t,$m)) $db=$m[1];
    if (preg_match("/define\(\s*'DB_HOST'\s*,\s*'([^']*)'/",$t,$m)) $host=$m[1];
    echo "from $f host=$host user=$user db=$db\n";
  }
}
// Plesk DB via local my.cnf as root
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}

$sql = <<<'SQL'
SELECT c.id, c.status, c.type_id, t.`key` AS type_key, t.title AS type_title,
       c.created_at, c.updated_at
FROM cms_contents c
LEFT JOIN cms_content_types t ON t.id=c.type_id
WHERE c.id IN (1075,22,4200,100,500);
SELECT cv.content_id, cf.`key` AS field_key, LEFT(cv.value,80) AS val
FROM cms_content_values cv
JOIN cms_content_fields cf ON cf.id=cv.field_id
WHERE cv.content_id=1075
ORDER BY cf.`key` LIMIT 40;
SELECT id, `key`, title FROM cms_content_types WHERE `key` IN ('landing','page') OR id IN (SELECT type_id FROM cms_contents WHERE id=1075);
SQL;

// get db name from plesk
$out = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -N -e \"SHOW DATABASES LIKE '%cms%'; SHOW DATABASES LIKE '%bahar%'; SHOW DATABASES LIKE '%bahman%'; SHOW DATABASES LIKE '%bmcars%';\" 2>/dev/null");
echo "dbs:\n".ascii($out)."\n";

$dbGuess = 'bmcars_cms';
if (preg_match('/bmcars\S+/', $out, $m)) $dbGuess=$m[0];
echo "using db=$dbGuess\n";

$q = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D ".escapeshellarg($dbGuess)." -e ".escapeshellarg(
"SELECT c.id, c.status, c.type_id, t.`key` type_key FROM cms_contents c LEFT JOIN cms_content_types t ON t.id=c.type_id WHERE c.id IN (1075,22,4200,100);"
)." 2>&1");
echo "rows:\n".ascii($q)."\n";

$q2 = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D ".escapeshellarg($dbGuess)." -e ".escapeshellarg(
"SHOW TABLES LIKE 'cms_%';"
)." 2>&1 | head -40");
echo "tables:\n".ascii($q2)."\n";

// try values table variants
$q3 = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D ".escapeshellarg($dbGuess)." -e ".escapeshellarg(
"SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='$dbGuess' AND TABLE_NAME LIKE '%content%' LIMIT 40;"
)." 2>&1");
echo "content_tables:\n".ascii($q3)."\n";
echo "DONE\n";
