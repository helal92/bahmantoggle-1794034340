<?php
$js=file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/agents_bahman.js');
echo substr($js,0,800)."\n---\n";
echo "len=".strlen($js)."\n";
echo "has_import=".(strpos($js,'import')!==false?'1':'0')."\n";
echo "has_initAgency=".(strpos($js,'initAgencySearchSelects')!==false?'1':'0')."\n";
