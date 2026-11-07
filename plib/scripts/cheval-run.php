<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$tpl='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/index.tpl';
echo "TPL LINE:\n";
echo shell_exec("grep -n 'hero-media\\|data-src\\|autoplay' ".escapeshellarg($tpl)." | head -20");
$body=file_get_contents($tpl);
$old='data-src="{$admin_setting.file_url}/{$item.image}"{if $hero_i eq 0} src="{$admin_setting.file_url}/{$item.image}" autoplay{/if}>';
$new='data-src="{$admin_setting.file_url}/{$item.image}">';
echo "has_old=".(strpos($body,$old)!==false?'Y':'N')." has_new_only=".(strpos($body,$new)!==false?'Y':'N')." has_autoplay_token=".(strpos($body,'autoplay{/if}')!==false?'Y':'N')."\n";

// force rewrite again
if (strpos($body, 'src="{$admin_setting.file_url}/{$item.image}" autoplay') !== false) {
  $body2 = preg_replace(
    '/data-src="\{\$admin_setting\.file_url\}\/\{\$item\.image\}"\{if \$hero_i eq 0\} src="\{\$admin_setting\.file_url\}\/\{\$item\.image\}" autoplay\{\/if\}>/',
    'data-src="{$admin_setting.file_url}/{$item.image}">',
    $body,
    1,
    $count
  );
  echo "preg_count=$count\n";
  if ($count) {
    file_put_contents('/tmp/index.tpl.deploy2', $body2);
    echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/index.tpl.deploy2').' '.escapeshellarg($tpl).' 2>&1');
  }
} else {
  echo "no eager src in tpl file now\n";
}
echo "AFTER:\n";
echo shell_exec("grep -n 'data-src.*item.image' ".escapeshellarg($tpl));

// clear smarty compile + page cache
$tc='/var/www/vhosts/bahman.ir/httpdocs/templates_c';
if (is_dir($tc)) {
  $n=0;
  foreach (glob("$tc/*") ?: [] as $p) { if (is_file($p)) { @unlink($p); $n++; } }
  echo "cleared_templates_c=$n\n";
}
foreach (glob('/tmp/bahman_*') ?: [] as $p) { if (is_file($p)) { @unlink($p); echo "rm ".basename($p)."\n"; } }

// also disable FAST page cache briefly? just delete again after request
$html=shell_exec("curl -sk --max-time 45 -H 'Host: bahman.ir' -H 'Cache-Control: no-cache' 'https://172.20.14.76/?_='".time());
$hdr=shell_exec("curl -skI --max-time 20 -H 'Host: bahman.ir' 'https://172.20.14.76/?_='".time());
echo "HDR:\n$hdr\n";
echo "home_len=".strlen($html)."\n";
if (preg_match_all('/<video class="hero-media[^"]*"[^>]*>/',$html,$m)) {
  foreach ($m[0] as $i=>$t) echo "V$i: $t\n";
}
echo "eager_count=".preg_match_all('/<video[^>]*\ssrc=/',$html)."\n";
echo "autoplay_count=".preg_match_all('/<video[^>]*autoplay/',$html)."\n";
