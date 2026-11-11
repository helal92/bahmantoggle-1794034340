<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs';

// Live rendered options from /agency
$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/agency?t='.time().'"');
echo "page_len=".strlen($page)."\n";
if (preg_match('/id="filter-company"[^>]*>(.*?)<\/select>/s',$page,$m)) {
  preg_match_all('/<option value="([^"]*)">([^<]*)<\/option>/',$m[1],$opts,PREG_SET_ORDER);
  echo "filter_options=".count($opts)."\n";
  foreach ($opts as $o) echo "  [".$o[1]."] ".$o[2]."\n";
}
// agency_companies section titles
if (preg_match_all('/agency-company[^>]*>.*?company[^>]*>/s',$page,$m)) {
  echo "sections=".count($m[0])."\n";
}
preg_match_all('/data-company-id="([^"]+)"/',$page,$m);
echo "data_company_ids=".json_encode(array_values(array_unique($m[1])))."\n";

// How company field looks in DB
$myconf=$root.'/libs/myconf.php';
$code=file_get_contents($myconf);
echo "myconf_vars:\n";
// extract array config
if (preg_match('/\$db\s*=\s*\[[^\]]+\]/s',$code,$m)) echo $m[0]."\n";
if (preg_match("/mysqli_connect\(([^)]+)\)/",$code,$m)) echo "mysqli ".$m[0]."\n";

// Find PDO/dsn usage
echo sh('grep -n "PDO\|mysqli\|dbname\|DB_" '.$root.'/libs/myconf.php '.$root.'/libs/myfunc.php 2>/dev/null | head -40')."\n";

// Look at CmsContent setType company ordering
echo "CmsContent=\n".sh('find '.$root.' -name CmsContent.php 2>/dev/null')."\n";
$cc=sh('find '.$root.' -name CmsContent.php 2>/dev/null');
if ($cc && is_file($cc)) {
  $c=file_get_contents($cc);
  if (preg_match_all('/function set(Count|Order|Sort|Type|Fields).{0,200}/s',$c,$m)) {
    foreach ($m[0] as $x) echo preg_replace('/\s+/',' ',$x)."\n";
  }
  if (preg_match('/order.{0,80}/i',$c,$m)) echo "order hint ".$m[0]."\n";
}

// Check if company smarty var is assigned globally before agency case
$cfg=file_get_contents($root.'/templates/bahman/config.php');
if (preg_match_all('/assign\(\s*[\'"]company[\'"]/',$cfg,$m,PREG_OFFSET_CAPTURE)) {
  foreach ($m[0] as $hit) {
    $p=$hit[1];
    echo "assign company @".substr_count(substr($cfg,0,$p),"\n")."\n";
    echo substr($cfg,max(0,$p-120),220)."\n---\n";
  }
}

// Sample agency values.company formats via SQL if we can
// Try loading site DB credentials from common locations
foreach ([$root.'/config.php',$root.'/configuration.php',$root.'/libs/myconf.php'] as $f) {
  if (!is_file($f)) continue;
  $t=file_get_contents($f);
  if (preg_match('/(?:host|server).*?[\'"]([^\'"]+)[\'"].*?(?:user).*?[\'"]([^\'"]+)[\'"].*?(?:pass|password).*?[\'"]([^\'"]*)[\'"].*?(?:name|dbname|database).*?[\'"]([^\'"]+)[\'"]/si',$t,$m)) {
    echo "possible creds host={$m[1]} db={$m[4]} user={$m[2]}\n";
  }
}

// Use existing helper: run through config functions by including index bootstrap lightly - too heavy.
// Instead query via mysql if .my.cnf or local socket root works
$q="SELECT c.id content_id, f.name field_name, v.value
FROM cms_content_field_values v
JOIN cms_contents c ON c.id=v.content_id
JOIN cms_content_fields f ON f.id=v.field_id
JOIN cms_content_types t ON t.id=c.type_id
WHERE t.name='agency' AND f.name='company'
LIMIT 15";
echo "mysql_try=\n".sh('mysql -N -e '.escapeshellarg($q).' 2>&1 | head -30')."\n";
echo "mysql_show=\n".sh('mysql -N -e "SHOW DATABASES" 2>&1 | head')."\n";
echo "DONE\n";
