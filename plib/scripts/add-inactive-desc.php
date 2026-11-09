<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';

// Patch sales.tpl: insert desc between title and meta
$tplPath=$root.'/sales.tpl';
$tpl=file_get_contents($tplPath);
$old = <<<'OLD'
                                                                <span class="sales-inactive-item__title">{$item.values.title|default: ''}</span>
                                                                <span class="sales-inactive-item__meta">
OLD;
$new = <<<'NEW'
                                                                <span class="sales-inactive-item__title">{$item.values.title|default: ''}</span>
                                                                {if isset($item.values.desc) and $item.values.desc neq ''}
                                                                    <span class="sales-inactive-item__desc">{$item.values.desc|escape:'html'}</span>
                                                                {/if}
                                                                <span class="sales-inactive-item__meta">
NEW;

if (strpos($tpl,'sales-inactive-item__desc')!==false) {
  echo "TPL_ALREADY\n";
} elseif (strpos($tpl,$old)===false) {
  echo "TPL_BLOCK_MISS\n";
  if (preg_match('/sales-inactive-item__title.{0,200}/s',$tpl,$m)) echo $m[0]."\n";
} else {
  $tpl=str_replace($old,$new,$tpl);
  $tmp='/tmp/sales.tpl.with_desc';
  file_put_contents($tmp,$tpl);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tplPath).' 2>&1');
  echo "TPL_PATCHED\n";
}

// Patch circular.css — both asset copies
$cssRuleAdd = '.accordion-section .sales-inactive-item__desc{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;font-size:12.5px;font-weight:400;line-height:1.55;color:#9a9b9c;word-break:break-word}'
  . '.accordion-section .sales-inactive-item:hover .sales-inactive-item__desc{color:#5c5d5e}'
  . '.accordion-section .sales-inactive-item__body{gap:5px}';

foreach ([$root.'/assets/circular.css', $root.'/base_template/assets/circular.css'] as $cssPath) {
  if (!is_file($cssPath)) continue;
  $css=file_get_contents($cssPath);
  if (strpos($css,'sales-inactive-item__desc')!==false) {
    echo "CSS_ALREADY ".basename(dirname($cssPath)).'/'.basename($cssPath)."\n";
    continue;
  }
  // insert after title rule if present
  $needle='.accordion-section .sales-inactive-item__title{font-size:14px;font-weight:400;color:inherit;word-break:break-word}';
  if (strpos($css,$needle)!==false) {
    $css=str_replace($needle, $needle.$cssRuleAdd, $css);
  } else {
    $css .= $cssRuleAdd;
  }
  // tighten body gap already in rule - also update existing body gap 4px -> keep via override above
  $tmp='/tmp/circular.css.desc.'.md5($cssPath);
  file_put_contents($tmp,$css);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($cssPath).' 2>&1');
  echo "CSS_PATCHED ".basename(dirname($cssPath)).'/'.basename($cssPath)." bytes=".strlen($css)."\n";
}

// clear sales compile + html cache
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) @unlink($f);
$tmpDir=rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
foreach (glob($tmpDir.'/bahman_*.html') ?: [] as $f) @unlink($f);
foreach (glob($tmpDir.'/bahman_sales*') ?: [] as $f) @unlink($f);

$page=sh('curl -s --max-time 30 -H "Host: bahman.ir" "http://172.20.14.76/sales?desc='.time().'"');
echo "page_len=".strlen($page)." fatal=".(stripos($page,'Fatal error')!==false?'1':'0')."\n";
echo "has_desc_class=".(strpos($page,'sales-inactive-item__desc')!==false?'1':'0')."\n";
// sample first few desc snippets
if (preg_match_all('/sales-inactive-item__desc">([^<]{0,120})/u',$page,$m)) {
  echo "samples=".json_encode(array_slice($m[1],0,5), JSON_UNESCAPED_UNICODE)."\n";
  echo "desc_count=".count($m[1])."\n";
} else {
  echo "no_desc_rendered\n";
  // check if items have empty desc in data - peek one attachment item from cache/data
}
// css file served
$cssLive=file_get_contents($root.'/assets/circular.css');
echo "css_has_desc=".(strpos($cssLive,'sales-inactive-item__desc')!==false?'1':'0')."\n";
echo "DONE\n";
