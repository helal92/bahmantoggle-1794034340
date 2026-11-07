<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';
$conn = Conn();

// Confirm DB has no CHEVAL slide
$rs = $conn->Execute("SELECT value FROM cms_content_field_values WHERE content_id=23 AND content_type_field_id=61");
$val = $rs ? ($rs->fields['value'] ?? $rs->fields[0]) : '';
echo "db_has_cheval=".(stripos($val,'CHEVAL')!==false?'YES':'NO')." len=".strlen($val)."\n";
echo "db_preview=".substr($val,0,200)."\n";

// Find tmp dir from index.php
$idx = file_get_contents(__DIR__.'/index.php');
if (preg_match('/bahmanTmpDir\s*=\s*([^;]+);/', $idx, $m)) {
  echo "tmp_assign=".$m[1]."\n";
}
if (preg_match('/\$bahmanTmpDir\s*=\s*[\'\"]([^\'\"]+)[\'\"]/', $idx, $m)) {
  echo "tmp_literal=".$m[1]."\n";
}
// show surrounding lines
if (preg_match('/.{0,200}bahmanTmpDir.{0,300}/s', $idx, $m)) {
  echo "ctx=\n".$m[0]."\n";
}

$candidates = [
  sys_get_temp_dir(),
  '/tmp',
  '/var/www/vhosts/bahman.ir/tmp',
  '/var/www/vhosts/bahman.ir/httpdocs/tmp',
  __DIR__.'/tmp',
  '/var/tmp',
];
foreach ($candidates as $d) {
  $f = rtrim($d,'/').'/bahman_home_page_v4.html';
  echo "check $f exists=".is_file($f)."\n";
  if (is_file($f)) {
    echo "  size=".filesize($f)." mtime=".date('c',filemtime($f))."\n";
    unlink($f);
    echo "  DELETED\n";
  }
  foreach (glob(rtrim($d,'/').'/bahman_*page*.html') ?: [] as $p) {
    echo "  also $p size=".filesize($p)."\n";
    unlink($p);
    echo "  DELETED\n";
  }
}

// find anywhere
$found = trim((string)shell_exec("find /var/www/vhosts/bahman.ir /tmp /var/tmp -name 'bahman_*page*.html' 2>/dev/null"));
echo "find_result=\n$found\n";
foreach (preg_split('/\n/', $found) as $p) {
  $p=trim($p); if ($p===''||!is_file($p)) continue;
  echo "unlink $p\n"; unlink($p);
}
echo "DONE\n";
