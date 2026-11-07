<?php
$fm='/usr/local/psa/admin/bin/filemng';
$sfUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php"));
$sf='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager';

// Create oneshot run as site user via PHP in docroot
$script=<<<'EOS'
<?php
$sf=__DIR__;
$n=$f=$k=0; $errs=[];
foreach (glob($sf.'/v/*') ?: [] as $p) {
  $base=basename($p);
  if (!is_link($p)) { $k++; continue; }
  $target=readlink($p);
  if (!is_file($target)) { @unlink($p); $f++; $errs[]="missing $base"; continue; }
  $tmp=$p.'.new';
  @unlink($tmp);
  if (@link($target, $tmp)) {
    @unlink($p);
    rename($tmp, $p);
    $n++;
  } else {
    $f++;
    $errs[]="$base link_err=".json_encode(error_get_last());
    @unlink($tmp);
  }
}
header('Content-Type: text/plain');
echo "hardlinked=$n kept=$k fail=$f\n";
echo implode("\n", array_slice($errs,0,10))."\n";
$sample='a9b88900-aead-11f1-ad46-005056b972d8.mp4';
echo "sample: "; passthru('ls -la '.escapeshellarg("$sf/v/$sample"));
EOS;
file_put_contents('/tmp/_hl.php',$script);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' cp '.escapeshellarg('/tmp/_hl.php').' '.escapeshellarg("$sf/_hl.php").' 2>&1');
echo shell_exec("curl -sk --max-time 60 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/_hl.php'");
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($sfUser).' rm '.escapeshellarg("$sf/_hl.php").' 2>&1');

// Also check same device
echo shell_exec("stat -c '%d %i %n' $sf/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4 /var/www/vhosts/siteapi.bahman.ir/httpdocs/uploads/app_1/bp_companies_1/1789219634-846274692.mp4 2>&1");
echo "DONE\n";
