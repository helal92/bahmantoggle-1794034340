<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$hdr="$bh/templates/bahman/includes/header_include.tpl";
$lines=file($hdr);

// Fix line 108 missing newline after brand else-if
for ($i=0;$i<count($lines);$i++) {
  if (preg_match('/\{else if isset\(\$smarty\.get\.include\) and \(\$smarty\.get\.include eq \'brand\'.*emdad\'\)\}\s*<style>/', $lines[$i])) {
    $lines[$i] = preg_replace('/\}(\s*)<style>/', "}\n    <style>", $lines[$i]);
    echo "fixed brand newline\n";
  }
}

// Remove the third consecutive {/if} after the css chain (extra one)
// Find pattern: {/if}\n{/if}\n{/if}\n{* Shared
$h=implode('', $lines);
$h2=preg_replace('/\{\/if\}\n\{\/if\}\n\{\/if\}\n(\{\* Shared overrides)/', "{/if}\n{/if}\n$1", $h, 1, $c);
echo "removed_extra_if=$c\n";
echo "balance open=".preg_match_all('/\{if\b/',$h2)." close=".preg_match_all('/\{\/if\}/',$h2)."\n";

file_put_contents('/tmp/header_fix.tpl',$h2);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/header_fix.tpl').' '.escapeshellarg($hdr).' 2>&1');

foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p))@unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');

$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?fix='.time().'"');
echo "home_len=".strlen($html)."\n";
if (strlen($html)<1000) { echo $html; echo "\n"; }
preg_match_all('#<link[^>]+stylesheet[^>]*>#',$html,$m);
$b=$d=0;
foreach ($m[0] as $tag) {
  $is=strpos($tag,'media="print"')!==false; if($is)$d++; else $b++;
  if (preg_match('/assets\/([^?"]+)/',$tag,$mm)) echo ($is?'D ':'B ').$mm[1]."\n";
}
echo "blocking=$b defer=$d fatal=".(stripos($html,'Fatal error')!==false?'Y':'N')."\n";
echo "DONE\n";
