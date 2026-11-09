<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';

// inspect persian digits helpers / modifiers
$cfg=file_get_contents($root.'/config.php');
if (preg_match('/function bahman_cms_to_persian_digits.*?^\}/ms',$cfg,$m)) echo "FN:\n".$m[0]."\n---\n";
echo "modifier regs:\n";
foreach (preg_split('/\n/',$cfg) as $i=>$line) {
  if (stripos($line,'persian')!==false || stripos($line,'registerPlugin')!==false || stripos($line,'date_only')!==false || stripos($line,'modifier')!==false) {
    if (strlen(trim($line))<200) echo ($i+1).": ".trim($line)."\n";
  }
}
echo "tpl usages of persian:\n".sh('grep -Rn --include="*.tpl" -e "persian" -e "fa_digit" -e "to_persian" '.$root.' 2>/dev/null | head -30')."\n";

// Patch sales.tpl count to persian digits
$tpl=$root.'/sales.tpl';
$c=file_get_contents($tpl);
$old='<span class="sales-inactive-company__count" title="تعداد بخشنامه‌ها">{$company.inactive_count|default:0}</span>';
$new='<span class="sales-inactive-company__count" title="تعداد بخشنامه‌ها">{$company.inactive_count|default:0|persian_digits}</span>';
// discover actual modifier name
$mod='persian_digits';
if (strpos($cfg,"'persian_digits'")!==false || strpos($cfg,'"persian_digits"')!==false) $mod='persian_digits';
elseif (preg_match("/registerPlugin\(\s*'modifier'\s*,\s*'([^']*persian[^']*)'/i",$cfg,$mm)) $mod=$mm[1];
elseif (preg_match('/bahman_cms_to_persian_digits/',$cfg)) {
  // may need to register; check if smarty modifier exists via function name mapping
  if (preg_match("/registerPlugin\(\s*'modifier'\s*,\s*'([^']+)'\s*,\s*'bahman_cms_to_persian_digits'/",$cfg,$mm)) $mod=$mm[1];
  else {
    // register if missing - we'll patch config
    echo "NEED_REGISTER_MODIFIER\n";
  }
}
$new='<span class="sales-inactive-company__count" title="تعداد بخشنامه‌ها">{$company.inactive_count|default:0|'.$mod.'}</span>';
echo "using_modifier=$mod\n";

if (strpos($c,'sales-inactive-company__count')===false) {
  echo "COUNT_SPAN_MISSING\n";
} elseif (strpos($c,'|'.$mod.'}')!==false || strpos($c,'|'.$mod.'"')!==false || preg_match('/sales-inactive-company__count[^>]*>\{\$company\.inactive_count\|default:0\|'.$mod.'\}/',$c)) {
  echo "TPL_ALREADY\n";
} elseif (strpos($c,$old)!==false) {
  $c=str_replace($old,$new,$c);
  $tmp='/tmp/sales.tpl.countfont';
  file_put_contents($tmp,$c);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tpl).' 2>&1');
  echo "TPL_PATCHED\n";
} else {
  echo "TPL_OLD_NOT_FOUND\n";
  if (preg_match('/sales-inactive-company__count.{0,120}/',$c,$m)) echo $m[0]."\n";
}

// Ensure modifier registered
if (strpos($cfg,"'".$mod."'")===false && strpos($cfg,'bahman_cms_to_persian_digits')!==false) {
  // find a registerPlugin block to append near
  if (preg_match("/\\\$smarty->registerPlugin\(\s*'modifier'\s*,\s*'date_only'[^;]+;/",$cfg,$m)) {
    $ins=$m[0]."\n\$smarty->registerPlugin('modifier', 'persian_digits', 'bahman_cms_to_persian_digits');";
    if (strpos($cfg,"registerPlugin('modifier', 'persian_digits'")===false) {
      $cfg2=str_replace($m[0],$ins,$cfg);
      $tmp='/tmp/config.php.countfont';
      file_put_contents($tmp,$cfg2);
      sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($root.'/config.php').' 2>&1');
      echo "CFG_REGISTERED_persian_digits\n";
      $mod='persian_digits';
    } else echo "CFG_ALREADY_HAS_MOD\n";
  } else {
    echo "date_only_register_not_found\n";
    // show nearby registerPlugin
    if (preg_match_all("/registerPlugin\([^)]+\)/",$cfg,$mm)) {
      foreach (array_slice($mm[0],0,20) as $x) echo "  $x\n";
    }
  }
}

// CSS: force dana on count (matches Persian site face; avenir makes Latin digits)
foreach ([$root.'/assets/circular.css', $root.'/base_template/assets/circular.css'] as $css) {
  if (!is_file($css)) continue;
  $cc=file_get_contents($css);
  $needle='.accordion-section .sales-inactive-company__count{flex:0 0 auto;margin-inline-start:8px;padding:1px 8px;border-radius:999px;background:transparent;border:1px solid rgba(112,112,112,.35);font-size:12px;font-weight:600;color:#78797a;line-height:1.4;white-space:nowrap}';
  $repl='.accordion-section .sales-inactive-company__count{flex:0 0 auto;margin-inline-start:8px;padding:1px 8px;border-radius:999px;background:transparent;border:1px solid rgba(112,112,112,.35);font-family:dana,sans-serif!important;font-size:12px;font-weight:600;color:#78797a;line-height:1.4;white-space:nowrap}';
  if (strpos($cc,'sales-inactive-company__count{')!==false && strpos($cc,'sales-inactive-company__count{')!==false) {
    if (strpos($cc,'sales-inactive-company__count{flex:0 0 auto;margin-inline-start:8px;padding:1px 8px;border-radius:999px;background:transparent;border:1px solid rgba(112,112,112,.35);font-family:dana')!==false) {
      echo "CSS_ALREADY ".basename(dirname($css)).'/'.basename($css)."\n";
    } elseif (strpos($cc,$needle)!==false) {
      $cc2=str_replace($needle,$repl,$cc);
      $tmp='/tmp/circular.css.countfont.'.md5($css);
      file_put_contents($tmp,$cc2);
      sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($css).' 2>&1');
      echo "CSS_PATCHED ".basename(dirname(dirname($css))).'/'.basename(dirname($css)).'/'.basename($css)."\n";
    } else {
      // fuzzy replace font-size in that rule
      $cc2=preg_replace(
        '/(\.accordion-section \.sales-inactive-company__count\{[^\}]*?)font-size:12px;/',
        '$1font-family:dana,sans-serif!important;font-size:12px;',
        $cc,
        1,
        $count
      );
      if ($count) {
        $tmp='/tmp/circular.css.countfont.'.md5($css);
        file_put_contents($tmp,$cc2);
        sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($css).' 2>&1');
        echo "CSS_FUZZY_PATCHED $css count=$count\n";
      } else {
        echo "CSS_NEEDLE_MISS $css\n";
        if (preg_match('/\.accordion-section \.sales-inactive-company__count\{[^}]+\}/',$cc,$m)) echo $m[0]."\n";
      }
    }
  }
}

// bump asset version by touching a marker if needed - circular.css mtime should bump template_version via scan
// clear compiled sales + page cache marker files
@unlink('/var/www/vhosts/bahman.ir/httpdocs/templates_c/4d43039e0f3e92cbd1e809231a795a525e5e1f13_0.file_sales.tpl.php');
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*sales*') ?: [] as $f) @unlink($f);
foreach (glob(sys_get_temp_dir().'/bahman_sales*') ?: [] as $f) @unlink($f);

// verify
$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?cf='.time().'"');
if (preg_match('/sales-inactive-company__count[^>]*>[^<]{1,12}</u',$page,$m)) echo "count_html=".$m[0]."\n";
else {
  // find بهمن موتور nearby count
  $p=strpos($page,'sales-inactive-company__count');
  echo "count_pos=".($p===false?'-1':$p)."\n";
  if ($p!==false) echo substr($page,$p,120)."\n";
}
$css=file_get_contents($root.'/assets/circular.css');
echo "css_has_dana_count=".(strpos($css,'sales-inactive-company__count{')!==false && strpos($css,'font-family:dana,sans-serif!important;font-size:12px')!==false?'1':'0')."\n";
echo "circular_v=".sh('curl -sI --max-time 10 -H "Host: bahman.ir" "http://172.20.14.76/templates/bahman/assets/circular.css" | tr -d "\\r" | grep -iE "HTTP/|last-modified|cache|length"')."\n";
echo "DONE\n";
