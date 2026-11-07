<?php
namespace PHPMaker2020\adoura_base_api;
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');
chdir(__DIR__);
include_once 'autoload.php';
$conn = Conn();

// Find ALL rows with CHEVAL in value
$rs = $conn->Execute("SELECT content_id, content_type_field_id, CHAR_LENGTH(value) AS len, status FROM cms_content_field_values WHERE value LIKE '%CHEVAL 6 HEV%'");
echo "ALL CHEVAL 6 HEV rows:\n";
while ($rs && !$rs->EOF) {
  echo "cid=".$rs->fields['content_id']." fid=".$rs->fields['content_type_field_id']." len=".$rs->fields['len']." rowstatus=".$rs->fields['status']."\n";
  $rs->MoveNext();
}

// Check parent/landing of 23 and 3387 children section-1
foreach ([23,3411,3412,3413,3414] as $cid) {
  $rs = $conn->Execute("SELECT value FROM cms_content_field_values WHERE content_id=$cid AND value LIKE '%[%' LIMIT 5");
  while ($rs && !$rs->EOF) {
    $val = $rs->fields['value'];
    if (strpos($val, 'CHEVAL') !== false || strpos($val, 'swiper') !== false || strpos($val, 'title') !== false) {
      $arr = json_decode($val, true);
      if (is_array($arr)) {
        echo "cid=$cid items=".count($arr)."\n";
        foreach ($arr as $i=>$it) {
          if (!is_array($it)) continue;
          echo "  [$i] status=".($it['status']??'?')." content=".($it['content']??'')." title=".($it['title']??'')." image=".($it['image']??'')."\n";
        }
      }
    }
    $rs->MoveNext();
  }
}

// How does home cache work - grep index.php
$idx = file_get_contents(__DIR__.'/index.php');
if (preg_match_all('/.{0,80}home.{0,80}/i', $idx, $m)) {
  echo "index home refs:\n";
  foreach (array_slice($m[0],0,30) as $line) echo trim($line)."\n";
}

// list _cache
$cd = __DIR__.'/_cache';
echo "_cache exists=".is_dir($cd)."\n";
if (is_dir($cd)) {
  foreach (scandir($cd) as $f) {
    if ($f==='.'||$f==='..') continue;
    $p="$cd/$f";
    echo "cache $f size=".filesize($p)." mtime=".date('c', filemtime($p))."\n";
  }
}

// templates compile cache
foreach ([__DIR__.'/templates_c', __DIR__.'/templates/templates_c', __DIR__.'/var/cache'] as $d) {
  if (is_dir($d)) echo "dir $d count=".count(scandir($d))."\n";
}

// Remove CHEVAL item entirely from content 23
$rs = $conn->Execute("SELECT content_type_field_id, value FROM cms_content_field_values WHERE content_id=23 AND value LIKE '%CHEVAL%' LIMIT 1");
if ($rs && !$rs->EOF) {
  $fid = $rs->fields['content_type_field_id'];
  $arr = json_decode($rs->fields['value'], true);
  $new = [];
  foreach ($arr as $it) {
    $hay = ($it['title']??'').' '.($it['content']??'');
    if (stripos($hay,'CHEVAL')!==false || mb_strpos($hay,'شوال')!==false) {
      echo "REMOVING slide content=".($it['content']??'')."\n";
      continue;
    }
    $new[] = $it;
  }
  $ok = $conn->Execute("UPDATE cms_content_field_values SET value=".$conn->qstr(json_encode($new, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))." WHERE content_id=23 AND content_type_field_id=".(int)$fid);
  echo "remove_update=".($ok?'ok':'fail')." remaining=".count($new)."\n";
}

// nuke all bahman_home caches
if (is_dir($cd)) {
  foreach (glob("$cd/*") as $p) {
    if (stripos(basename($p),'home')!==false) { unlink($p); echo "unlink ".basename($p)."\n"; }
  }
}

// Also check opcache reset
if (function_exists('opcache_reset')) { opcache_reset(); echo "opcache_reset\n"; }
echo "DONE\n";
