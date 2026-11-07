<?php
echo shell_exec('sed -n "350,509p" /tmp/bahman_probe.txt');
echo "\n==== index.tpl video block ====\n";
$root='/var/www/vhosts/bahman.ir/httpdocs';
$idx=file_get_contents("$root/templates/bahman/index.tpl");
// find video tags with context
if (preg_match_all('/<video[\s\S]*?<\/video>/i',$idx,$m)) {
  foreach ($m[0] as $i=>$v) echo "VIDEO $i:\n$v\n\n";
}
echo "\n==== how media url built? ====\n";
foreach (explode("\n",$idx) as $i=>$line) {
  if (preg_match('/file_url|sitefile|get_file|image|attachment|links/i',$line) && preg_match('/video|hero|slider|section|image|file/i',$line)) {
    echo ($i+1).': '.trim($line)."\n";
  }
}
// search config for file URL helper
echo shell_exec("grep -Rn 'hero-media\\|data-src\\|file_url\\|sitefile' $root/templates/bahman/index.tpl $root/httpdocs/templates/bahman/config.php $root/templates/bahman/config.php 2>/dev/null | head -40");
echo shell_exec("ls $root/templates/bahman/config.php; wc -l $root/templates/bahman/index.tpl");
// show lines around first video in index.tpl
$lines=explode("\n",$idx);
for ($i=0;$i<count($lines);$i++) {
  if (stripos($lines[$i],'<video')!==false) {
    $start=max(0,$i-25); $end=min(count($lines)-1,$i+30);
    echo "\n--- context lines ".($start+1)."-".($end+1)." ---\n";
    for ($j=$start;$j<=$end;$j++) echo ($j+1).': '.$lines[$j]."\n";
    break;
  }
}
