<?php
$fm='/usr/local/psa/admin/bin/filemng';
$user=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
copy(dirname(__DIR__).'/resources/_cheval_fix.php','/tmp/_cheval_fix.php');
$dst='/var/www/vhosts/bahman.ir/httpdocs/_cheval_fix.php';
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' cp '.escapeshellarg('/tmp/_cheval_fix.php').' '.escapeshellarg($dst).' 2>&1');
echo trim((string)shell_exec('curl -sk --max-time 60 -H "Host: bahman.ir" "https://172.20.14.76/_cheval_fix.php"'))."\n";
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($user).' rm '.escapeshellarg($dst).' 2>&1');
// quick homepage check for CHEVAL still rendered
$home = (string)shell_exec('curl -sk --max-time 30 -H "Host: bahman.ir" "https://172.20.14.76/"');
$has = (stripos($home,'CHEVAL 6 HEV')!==false || mb_strpos($home,'شوال')!==false);
echo "home_len=".strlen($home)." still_has_cheval=".($has?'YES':'NO')."\n";
if ($has) {
  // show nearby context
  if (preg_match('/.{0,80}CHEVAL.{0,80}/iu',$home,$m)) echo "ctx=".$m[0]."\n";
}
