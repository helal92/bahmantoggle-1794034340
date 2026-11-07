<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$hdr="$bh/templates/bahman/includes/header_include.tpl";
$lines=file($hdr);

$replacement = <<<'TPL'
{if isset($smarty.get.include) and $smarty.get.include eq 'index'}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-media-fix.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/hero-video-control.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
{else}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
{if isset($smarty.get.include) and $smarty.get.include eq 'show_product'}
TPL;

// Replace lines 80-92 (1-based) which is index 79-91 inclusive, keeping the show_product line from original as start of else-if chain
// Original structure:
// 80-85 always
// 86 if index ... 91
// 92 else if show_product
// We want: if index { critical+defer } else { old 80-85 + continue show_product branch }

// So replace indices 79..91 (lines 80-92) where line 92 becomes part of our else ending with show_product condition already in replacement... 
// Careful: replacement ends with `{if isset show_product}` but original line 92 is `{else if isset show_product}`
// Fix replacement to use else if for show_product and not duplicate.

$replacement = <<<'TPL'
{if isset($smarty.get.include) and $smarty.get.include eq 'index'}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-media-fix.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/hero-video-control.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
{else}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
{if isset($smarty.get.include) and $smarty.get.include eq 'show_product'}
TPL;

// Remove lines 80-92 (index 79-91), insert replacement (which includes show_product if opening)
$before = array_slice($lines, 0, 79); // lines 1-79
$after = array_slice($lines, 92); // from line 93 onward (old show_product body)
// Wait old line 92 is else if show_product - we included that in replacement as `{if show_product}` but then after starts at 93 which is fancybox - WRONG nesting.
// Better approach: replace only 80-91, keep 92+ intact, and wrap 80-85 into the if/else.

$replacement = <<<'TPL'
{if isset($smarty.get.include) and $smarty.get.include eq 'index'}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-media-fix.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/hero-video-control.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
{else}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
{if isset($smarty.get.include) and $smarty.get.include eq 'show_product'}
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/fancybox.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/circular.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product-hero.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-media-fix.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product-gallery.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/demo-drive.css?v={$template_version}">
{else if isset($smarty.get.include) and ($smarty.get.include eq 'brand' or $smarty.get.include eq 'emdad')}
TPL;

// Replace lines 80-101 (through brand/emdad condition line), keep from 102 style block
$before = array_slice($lines, 0, 79);
$after = array_slice($lines, 101); // from old line 102 (style inside brand)
$newContent = implode('', $before) . $replacement . implode('', $after);

// Fix: brand branch originally had `{else if brand}` then style - we ended replacement with same else if brand - good.
// But we need to close the outer `{else}` before final `{/if}` of the big chain.
// Original ends with `{/if}` at line 127 closing the if index / else if show_product / else if brand / else if sales...
// Our structure:
// {if index} ... 
// {else}
//   always css
//   {if show_product}...
//   {else if brand} ... (continues with after which has style and more else ifs)
//   ...
//   {/if}  <- line 127 closes inner chain starting at show_product
// MISSING {/if} for outer else!

// After line 127's {/if}, we need an extra {/if} for outer. The after starts at 102, includes 127 {/if}.
// Insert {/if} after that closing.

$newContent = preg_replace(
  '/(\{\/if\}\n\{\* Shared overrides last)/',
  "{/if}\n{/if}\n$1",
  $newContent,
  1,
  $c
);
echo "extra close if=$c\n";

file_put_contents('/tmp/header_include.tpl.new', $newContent);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/header_include.tpl.new').' '.escapeshellarg($hdr).' 2>&1');

// Also defer shared typography/header-search on homepage - make them print media when index
$h=file_get_contents($hdr);
$h=str_replace(
  '<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/typography.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/header-search.css?v={$template_version}">',
  '{if isset($smarty.get.include) and $smarty.get.include eq \'index\'}
<link rel="stylesheet" media="print" onload="this.onload=null;this.media=\'all\'" crossorigin href="{$admin_setting.template_url}/assets/typography.css?v={$template_version}">
<link rel="stylesheet" media="print" onload="this.onload=null;this.media=\'all\'" crossorigin href="{$admin_setting.template_url}/assets/header-search.css?v={$template_version}">
{else}
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/typography.css?v={$template_version}">
<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/header-search.css?v={$template_version}">
{/if}',
  $h,
  $c2
);
echo "shared defer=$c2\n";
file_put_contents('/tmp/header_include.tpl.new2', $h);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/header_include.tpl.new2').' '.escapeshellarg($hdr).' 2>&1');

// Defer latest-news.css in index.tpl
$idx=file_get_contents("$bh/templates/bahman/index.tpl");
$idx=str_replace(
  '<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/latest-news.css?v={$template_version}">',
  '<link rel="stylesheet" media="print" onload="this.onload=null;this.media=\'all\'" crossorigin href="{$admin_setting.template_url}/assets/latest-news.css?v={$template_version}">',
  $idx,
  $c3
);
echo "latest-news defer=$c3\n";
file_put_contents('/tmp/index.tpl.new',$idx);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/index.tpl.new').' '.escapeshellarg("$bh/templates/bahman/index.tpl").' 2>&1');

// purge
foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p))@unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');

// Verify - homepage uses include=index via rewrite; curl / should set it
$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?nocache='.time().'"');
echo "home_len=".strlen($html)."\n";
preg_match_all('#<link[^>]+stylesheet[^>]*>#',$html,$m);
$blocking=$defer=0;
foreach ($m[0] as $tag) {
  $isPrint = strpos($tag,'media="print"')!==false;
  if ($isPrint) $defer++; else $blocking++;
  echo ($isPrint?'DEFER ':'BLOCK ').preg_replace('/\s+/',' ',$tag)."\n";
}
echo "blocking=$blocking defer=$defer\n";
echo "data-video-src=".substr_count($html,'data-video-src')." data-src sitefile video=".substr_count($html,'data-src="https://sitefile')."\n";

// apache graceful
echo shell_exec('/usr/local/psa/admin/bin/httpdmng --reload 2>&1 | head -5');
echo "syn=".trim(shell_exec("ss -tn state syn-recv | wc -l"))." load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "DONE\n";
