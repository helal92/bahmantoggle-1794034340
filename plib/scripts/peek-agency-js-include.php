<?php
function sh($c){return trim((string)shell_exec($c));}
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
echo sh('grep -Rn --include="*.tpl" "agents_bahman\|agency-search-select\|initAgencySearchSelects" '.$root.' 2>/dev/null')."\n";
$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/?include=agency"');
preg_match_all('/<script[^>]+>/',$page,$m);
foreach ($m[0] as $s) if (stripos($s,'agen')!==false || stripos($s,'agent')!==false) echo "$s\n";
echo "DONE\n";
