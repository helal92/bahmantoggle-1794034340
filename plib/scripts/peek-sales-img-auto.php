<?php
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$ht='/var/www/vhosts/bahman.ir/httpdocs/.htaccess';
echo "TPL_EXISTS=".(is_file($tpl)?"1":"0")."\n";
$c=file_get_contents($tpl);
foreach (['sales-img','onerror','thumb/','banner'] as $k) echo "$k=".substr_count($c,$k)."\n";
preg_match_all('/src="[^"]*sales-img[^"]*"/',$c,$m);
echo "SRC:\n".implode("\n",$m[0])."\n";
preg_match_all('/onerror="[^"]*"/',$c,$m2);
echo "ONERR:\n".implode("\n",$m2[0])."\n";
echo "HT_SALESIMG:\n";
echo preg_replace('/[^\x20-\x7E\n]/','?', trim((string)shell_exec('grep -n "sales-img\|RewriteRule" '.escapeshellarg($ht).' 2>/dev/null | head -40')))."\n";
echo "DIR_COUNT=".trim((string)shell_exec('ls /var/www/vhosts/bahman.ir/httpdocs/sales-img 2>/dev/null | wc -l'))."\n";
echo "DONE\n";
