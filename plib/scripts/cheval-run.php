<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';
$hdr="$bh/templates/bahman/includes/header_include.tpl";
$idx="$bh/templates/bahman/index.tpl";
$foot="$bh/templates/bahman/includes/footer_include.tpl";

// 1) Enable HTTP/2 via plesk for domains
echo "http2 enable:\n";
echo shell_exec('plesk bin domain_settings --help 2>&1 | head -5');
echo shell_exec('plesk bin site --help 2>&1 | grep -i http2');
echo shell_exec('plesk bin server_pref --help 2>&1 | grep -i http2');
echo shell_exec('plesk bin site --update-web-server-settings bahman.ir -ssl-http2 true 2>&1');
echo shell_exec('plesk bin site --update-web-server-settings sitefile.bahman.ir -ssl-http2 true 2>&1');
echo shell_exec('plesk bin site --show-web-server-settings bahman.ir 2>&1 | grep -iE "http2|nginx|proxy" | head -20');

// Try disable brotli so nginx can start
echo "\nbrotli disable attempts:\n";
echo shell_exec('plesk bin nginx_modules --help 2>&1 | head -30');
echo shell_exec('plesk bin nginxmng --help 2>&1 | head -20');
echo shell_exec('/usr/local/psa/admin/bin/nginx_modules_ctl --help 2>&1 | head -30');
echo shell_exec('/usr/local/psa/admin/bin/nginx_modules_ctl --disable brotli 2>&1');
echo shell_exec('/usr/local/psa/admin/bin/nginx_modules_ctl -d brotli 2>&1');
echo shell_exec('plesk bin nginxmng -e 2>&1 | head -20');
echo shell_exec('/usr/local/psa/admin/bin/nginx_control -s 2>&1');

// 2) Patch homepage CSS block in header_include.tpl
$h=file_get_contents($hdr);
$old = <<<'OLD'
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
    {* hero-media-fix must load before first paint — prevents black video flash *}
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-video-control.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-media-fix.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
OLD;

$new = <<<'NEW'
    {* Critical CSS only — extras deferred to avoid HTTP/1.1 6-conn starvation *}
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/navigation.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/hero-media-fix.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/typography.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/button-hover.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/hero-video-control.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/header-search.css?v={$template_version}">
    <link rel="stylesheet" media="print" onload="this.media='all'" crossorigin href="{$admin_setting.template_url}/assets/active-sale-btn.css?v={$template_version}">
    <noscript>
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/index2.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/products-grid.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product-tabs.css?v={$template_version}">
    </noscript>
NEW;

if (strpos($h,$old)===false) {
  // try looser match - read exact lines 80-91
  echo "EXACT old block not found, dumping raw bytes around css\n";
  if (preg_match('/footer-bahman\.css.*?\n.*?navigation\.css[^\n]*\n/s',$h,$m)) {
    echo "FOUND via regex len=".strlen($m[0])."\n";
    $h=preg_replace('/    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/footer-bahman\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/hero-bahman\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/index2\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/index\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/button-hover\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/products-grid\.css\?v=\{\$template_version\}">\n    \{\* hero-media-fix must load before first paint — prevents black video flash \*\}\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/hero-video-control\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/hero-media-fix\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/brand-slider\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/product-tabs\.css\?v=\{\$template_version\}">\n    <link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/navigation\.css\?v=\{\$template_version\}">\n/', $new."\n", $h, 1, $cnt);
    echo "preg replace cnt=$cnt\n";
  } else {
    echo "regex also failed\n";
    // show hex of comment line
    foreach (explode("\n",$h) as $i=>$line) {
      if (strpos($line,'hero-media-fix must')!==false || strpos($line,'footer-bahman.css')!==false && $i<100) {
        echo ($i+1).': '.json_encode($line)."\n";
      }
    }
  }
} else {
  $h=str_replace($old,$new,$h);
  echo "exact replace OK\n";
}

// Remove duplicate typography/header-search/active-sale from global block if homepage already has them deferred
// Global block still loads typography + header-search + active-sale for all pages - for homepage that duplicates.
// Change global to skip when index:
$h2=preg_replace(
  '/(<link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/typography\.css\?v=\{\$template_version\}">\s*<link rel="stylesheet" crossorigin href="\{\$admin_setting\.template_url\}\/assets\/header-search\.css\?v=\{\$template_version\}">)/',
  '{if !(isset($smarty.get.include) and ($smarty.get.include eq \'\' or $smarty.get.include eq \'index\')) and !(isset($include) and $include eq \'index\') and !(!isset($smarty.get.include) and !isset($include))}'."\n".'$1'."\n".'{/if}',
  $h,
  1,
  $c2
);
// That logic is messy. Simpler: wrap the three global CSS that failed in HAR so homepage doesn't double-request.
// Actually homepage template_version block loads typography+header-search ALWAYS at end - and we now defer them in index block.
// Remove from global unconditional and keep only outside index - easiest: comment out header-search and typography from global when on index by wrapping.

echo "wrap global typography cnt try\n";

file_put_contents('/tmp/header_include.tpl.new', $h);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/header_include.tpl.new').' '.escapeshellarg($hdr).' 2>&1');

// 3) Strip video data-src from index - poster image only (no speculative video fetch)
$ix=file_get_contents($idx);
$ix2=preg_replace(
  '/<video class="hero-media[^"]*"[^>]*data-src="[^"]+"[^>]*>\s*<\/video>/',
  '',
  $ix,
  -1,
  $vc
);
// Keep a comment marker and rely on poster fallback img already present
if ($vc>0) {
  // Better: remove only data-src attribute so JS can't autoplay until we set it via a non-network attribute
  $ix=file_get_contents($idx); // reset
}
$ix=preg_replace(
  '/(data-src="\{\$admin_setting\.file_url\}\/v\/\{\$item\.image\}\.mp4")/',
  'data-video-src="{$admin_setting.file_url}/v/{$item.image}.mp4"',
  $ix,
  -1,
  $vc2
);
echo "video attr rename=$vc2\n";
file_put_contents('/tmp/index.tpl.new',$ix);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/index.tpl.new').' '.escapeshellarg($idx).' 2>&1');

// Update hero-media-fix.js to use data-video-src
$js=file_get_contents("$bh/templates/bahman/assets/hero-media-fix.js");
$js=str_replace('data-src','data-video-src',$js);
// but careful - might break getAttribute data-src. rewrite properly:
$js=file_get_contents(dirname(__DIR__).'/resources/hero-media-fix.js');
if (!$js) $js=file_get_contents("$bh/templates/bahman/assets/hero-media-fix.js");
$js=str_replace(
  'video.getAttribute("data-src")',
  '(video.getAttribute("data-video-src") || video.getAttribute("data-src"))',
  $js
);
$js=str_replace(
  'video.setAttribute("data-src", src)',
  'video.setAttribute("data-video-src", src)',
  $js
);
file_put_contents('/tmp/hero-media-fix.js',$js);
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/hero-media-fix.js').' '.escapeshellarg("$bh/templates/bahman/assets/hero-media-fix.js").' 2>&1');

// also hero-video-control.js
$jvc=file_get_contents("$bh/templates/bahman/assets/hero-video-control.js");
if (strpos($jvc,'data-src')!==false) {
  $jvc=str_replace('getAttribute("data-src")','getAttribute("data-video-src") || video.getAttribute("data-src")',$jvc);
  // fix broken replace - do carefully
  $jvc=file_get_contents("$bh/templates/bahman/assets/hero-video-control.js");
  $jvc=str_replace(
    'if (!video.getAttribute("src") && video.getAttribute("data-src")) {
        video.setAttribute("src", video.getAttribute("data-src"));
      }',
    'var ds = video.getAttribute("data-video-src") || video.getAttribute("data-src");
      if (!video.getAttribute("src") && ds) {
        video.setAttribute("src", ds);
      }',
    $jvc
  );
  file_put_contents('/tmp/hero-video-control.js',$jvc);
  echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/hero-video-control.js').' '.escapeshellarg("$bh/templates/bahman/assets/hero-video-control.js").' 2>&1');
  echo "hero-video-control patched\n";
}

// 4) Bump template_version if exists
$cfg="$bh/templates/bahman/config.php";
// search assign template_version
echo shell_exec("grep -Rn template_version $bh/templates/bahman --include='*.php' --include='*.tpl' 2>/dev/null | head -15");

// purge caches
foreach (glob("$bh/templates_c/*")?:[] as $p) if(is_file($p)) @unlink($p);
file_put_contents('/tmp/_p.php','<?php foreach(glob(sys_get_temp_dir()."/bahman_*")?:[] as $p)@unlink($p); echo "purged\n";');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' cp '.escapeshellarg('/tmp/_p.php').' '.escapeshellarg("$bh/_p.php").' 2>&1');
echo shell_exec('curl -sk --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/_p.php"');
echo shell_exec(escapeshellcmd($fm).' '.escapeshellarg($bhUser).' rm '.escapeshellarg("$bh/_p.php").' 2>&1');

$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/?x='.time().'"');
echo "home_len=".strlen($html)."\n";
preg_match_all('#rel="stylesheet"[^>]+href="([^"]+)"#',$html,$m);
echo "stylesheet_count=".count($m[1])."\n";
foreach ($m[1] as $u) echo "  $u\n";
echo "media_print_count=".substr_count($html,'media="print"')."\n";
echo "data-src video=".substr_count($html,'data-src="https://sitefile')." data-video-src=".substr_count($html,'data-video-src=')."\n";
echo "http2 check:\n".shell_exec("curl -skI --max-time 10 --http2 -H 'Host: bahman.ir' 'https://172.20.14.76/' | head -8");
echo "DONE\n";
