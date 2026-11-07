<?php
$fm='/usr/local/psa/admin/bin/filemng';
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$id='a9b88900-aead-11f1-ad46-005056b972d8';

$ok=$fail=0;
foreach (glob("$sf/v/*") ?: [] as $p) {
  if (!is_link($p)) continue;
  $target=readlink($p);
  if (!is_file($target)) continue;
  $base=basename($p);
  $tmp="$sf/v/.$base.hl";
  // remove tmp if any
  @shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' rm '.escapeshellarg($tmp).' 2>/dev/null');
  $out=shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' exec '.escapeshellarg("$sf/v").' ln '.escapeshellarg($target).' '.escapeshellarg($tmp).' 2>&1');
  if (is_file($tmp) && !is_link($tmp)) {
    shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' rm '.escapeshellarg($p).' 2>&1');
    shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' mv '.escapeshellarg($tmp).' '.escapeshellarg($p).' 2>&1');
    $ok++;
  } else {
    $fail++;
    if ($fail<=5) echo "fail $base out=$out\n";
    @shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' rm '.escapeshellarg($tmp).' 2>/dev/null');
  }
}
echo "hardlinked=$ok fail=$fail\n";
echo shell_exec("ls -la $sf/v/$id.mp4");

// Verify not symlink and still 206
$h=shell_exec("curl -skI --max-time 10 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' -H 'Referer: https://bahman.ir/' 'https://172.20.14.76/v/$id.mp4'");
echo $h;
echo "is_link_now=".(is_link("$sf/v/$id.mp4")?'Y':'N')." is_file=".(is_file("$sf/v/$id.mp4")?'Y':'N')."\n";
echo "DONE\n";
