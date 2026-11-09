<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'));}
$dir='/var/www/vhosts/bahman.ir/httpdocs/sales-img';
echo "existing_sample_info=\n".sh('file '.$dir.'/03945293-d752-11f1-b563-005056b972d8_600.png; identify '.$dir.'/03945293-d752-11f1-b563-005056b972d8_600.png 2>/dev/null; ls -la '.$dir.' | head -5')."\n";

// get all image uuids used by active directives + landing banner from sales page
$sql = <<<'SQL'
SELECT DISTINCT v.value
FROM cms_contents c
JOIN cms_content_types t ON t.id=c.content_type_id
JOIN cms_content_field_values v ON v.content_id=c.id
JOIN cms_content_type_fields f ON f.id=v.content_type_field_id
WHERE t.`key`='directive' AND c.status=1 AND f.`key`='image' AND v.value<>'' AND v.value IS NOT NULL;
SQL;
$images = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -N -e ".escapeshellarg($sql));
echo "directive_images=\n$images\n";
$sql2 = <<<'SQL'
SELECT v.value FROM cms_contents c
JOIN cms_content_types t ON t.id=c.content_type_id
JOIN cms_content_field_values v ON v.content_id=c.id
JOIN cms_content_type_fields f ON f.id=v.content_type_field_id
WHERE t.`key`='landing' AND c.id=22 AND f.`key`='banner' LIMIT 5;
SQL;
# sales landing - find sales page id from config - often a fixed landing. Check banner field usages on sales include.
$sql3="SELECT c.id, f.`key`, v.value FROM cms_contents c JOIN cms_content_field_values v ON v.content_id=c.id JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE f.`key` IN ('banner','image') AND c.id IN (SELECT content_id FROM cms_content_field_values WHERE value LIKE '%فروش%' LIMIT 0);";

// Also parse homepage/sales HTML for sales-img refs
$html=sh('curl -s --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/sales');
preg_match_all('#/sales-img/([0-9a-fA-F-]{36})_(600|960|1920)\\.(png|jpg)#', $html, $m);
$needed=array_unique($m[1]??[]);
echo "from_sales_page_needed=".count($needed)."\n".implode("\n",$needed)."\n";
$html2=sh('curl -s --max-time 20 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/');
preg_match_all('#/sales-img/([0-9a-fA-F-]{36})_(600|960|1920)\\.(png|jpg)#', $html2, $m2);
$needed2=array_unique($m2[1]??[]);
echo "from_home_needed=".count($needed2)."\n".implode("\n",$needed2)."\n";

$all=array_unique(array_filter(array_merge(
  preg_split('/\s+/', trim($images)),
  $needed,
  $needed2
)));
echo "ALL_COUNT=".count($all)."\n";

$sizes=['600'=>'png','960'=>'png','1920'=>'jpg'];
// actually check existing extensions
echo "ext_check=\n".sh('ls '.$dir.' | sed "s/.*_//;s/\\..*//" | sort | uniq -c')."\n";
echo "ext2=\n".sh('ls '.$dir.' | grep -oE "_[0-9]+\\.[a-z]+$" | sort | uniq -c')."\n";

$missing=[];
foreach ($all as $uuid) {
  if (!preg_match('/^[0-9a-fA-F-]{36}$/', $uuid)) continue;
  foreach ([600,960,1920] as $w) {
    $ext = ($w==1920) ? 'jpg' : 'png';
    // detect from existing pattern: 1920.png also exists in sample list!
    $candidates=["{$dir}/{$uuid}_{$w}.png","{$dir}/{$uuid}_{$w}.jpg"];
    $ok=false;
    foreach ($candidates as $p) { if (is_file($p)) { $ok=true; break; } }
    if (!$ok) $missing[]="{$uuid}_{$w}";
  }
}
echo "MISSING_COUNT=".count($missing)."\n";
echo implode("\n", array_slice($missing,0,40))."\n";
echo "DONE\n";
