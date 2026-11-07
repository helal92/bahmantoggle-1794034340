<?php
$fm='/usr/local/psa/admin/bin/filemng';
$res=dirname(__DIR__).'/resources';
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';
$id='a9b88900-aead-11f1-ad46-005056b972d8';

function fcp($fm,$user,$src,$dst){
  $tmp='/tmp/'.basename($src).'.'.getmypid();
  copy($src,$tmp);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dst).' 2>&1');
  @unlink($tmp);
}

fcp($fm,$sfUser,"$res/htaccess.sitefile","$sf/.htaccess");
fcp($fm,$sfUser,"$res/file.php","$sf/file.php");

// Convert existing symlinks in v/ to hardlinks
$n=0; $fail=0; $keep=0;
foreach (glob("$sf/v/*") ?: [] as $p) {
  if (!is_link($p)) { $keep++; continue; }
  $target=readlink($p);
  if (!is_file($target)) { @unlink($p); $fail++; continue; }
  $tmp=$p.'.hlnew';
  @unlink($tmp);
  if (@link($target, $tmp)) {
    @unlink($p);
    rename($tmp, $p);
    $n++;
  } else {
    // leave symlink
    @unlink($tmp);
    $fail++;
  }
}
echo "hardlinked=$n kept_nonlink=$keep fail=$fail\n";
echo shell_exec("ls -la $sf/v/$id.mp4");

// verify headers
$h=shell_exec("curl -skI --max-time 15 -H 'Host: sitefile.bahman.ir' -H 'Origin: https://bahman.ir' -H 'Referer: https://bahman.ir/' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/$id.mp4'");
echo "HEADERS:\n$h\n";
echo "expose=".(stripos($h,'Access-Control-Expose-Headers')!==false?'Y':'N')."\n";
echo "allow_range=".(stripos($h,'Range')!==false?'Y':'N')."\n";

// public path via Host
echo shell_exec("curl -sk --max-time 20 -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-2047' -o /tmp/vok.bin -w 'get code=%{http_code} size=%{size_download}\\n' 'https://172.20.14.76/v/$id.mp4'");
echo shell_exec('file /tmp/vok.bin');
echo "DONE\n";
