<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$hdr="$bh/templates/bahman/includes/header_include.tpl";
$lines=file($hdr);
// dump lines 75-140 with visible whitespace
for ($i=74;$i<min(140,count($lines));$i++) {
  echo ($i+1).': '.json_encode($lines[$i], JSON_UNESCAPED_UNICODE)."\n";
}

// Find index homepage branch start/end
$h=file_get_contents($hdr);
if (preg_match('/\{if isset\(\$is_index\).*?\{else if isset\(\$is_product\)/s',$h,$m)) {
  echo "\nbranch len=".strlen($m[0])."\n";
}
// show condition for homepage css
foreach ($lines as $i=>$line) {
  if (preg_match('/is_index|include eq \'index\'|footer-bahman\.css/',$line)) echo "C".($i+1).": ".trim($line)."\n";
}
