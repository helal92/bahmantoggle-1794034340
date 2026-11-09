<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
$cfgPath=$root.'/config.php';
$cfg=file_get_contents($cfgPath);

// how is date_only wired?
echo "date_only refs:\n";
$lines=explode("\n",$cfg);
foreach ($lines as $i=>$line) {
  if (stripos($line,'date_only')!==false || stripos($line,'registerPlugin')!==false || stripos($line,'register_modifier')!==false || stripos($line,'smarty->')!==false && stripos($line,'modifier')!==false) {
    echo ($i+1).": ".rtrim($line)."\n";
  }
}

// Smarty 3/4/5 styles
$patterns=[
  "registerPlugin('modifier'",
  'registerPlugin("modifier"',
  'register_modifier',
  "->registerPlugin",
];
foreach ($patterns as $p) echo "has:$p=".(strpos($cfg,$p)!==false?'1':'0')."\n";

// Find smarty init / plugins dir
echo "plugins:\n".sh('ls -la '.$root.'/../../smarty* '.$root.'/../plugins /var/www/vhosts/bahman.ir/httpdocs/libs 2>/dev/null | head -40')."\n";
echo sh('find /var/www/vhosts/bahman.ir/httpdocs -iname "*date_only*" 2>/dev/null | head')."\n";
echo sh('grep -Rnl --include="*.php" -e "date_only" /var/www/vhosts/bahman.ir/httpdocs --exclude-dir=templates_c 2>/dev/null | head -20')."\n";
echo "DONE\n";
