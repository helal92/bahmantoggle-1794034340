<?php
$fm = '/opt/psa/admin/bin/filemng';
$user = 'bmcars.bahman.ir_vzb4o368qoo';
function sh($c){return trim((string)shell_exec($c));}

// 1) deploy sales_img.php
$destPhp = '/var/www/vhosts/bahman.ir/httpdocs/media/sales_img.php';
$tmpPhp = '/tmp/_sales_img_php';
file_put_contents($tmpPhp, base64_decode('PD9waHAKLyoqCiAqIE9uLWRlbWFuZCBidWlsZGVyIGZvciAvc2FsZXMtaW1nL3t1dWlkfV97d30ue2V4dH0KICogSWYgdGhlIHN0YXRpYyBjYWNoZSBmaWxlIGlzIG1pc3NpbmcsIGZldGNoIGZyb20gc2l0ZWZpbGUsIHJlc2l6ZSwgc2F2ZSwgdGhlbiBzZXJ2ZS4KICogV2lyZWQgYnkgLmh0YWNjZXNzIG9ubHkgd2hlbiB0aGUgZmlsZSBkb2VzIG5vdCBhbHJlYWR5IGV4aXN0LgogKi8KCiR1dWlkID0gaXNzZXQoJF9HRVRbJ3V1aWQnXSkgPyB0cmltKChzdHJpbmcpICRfR0VUWyd1dWlkJ10pIDogJyc7CiR3ID0gaXNzZXQoJF9HRVRbJ3cnXSkgPyAoaW50KSAkX0dFVFsndyddIDogMDsKJGV4dCA9IGlzc2V0KCRfR0VUWydleHQnXSkgPyBzdHJ0b2xvd2VyKHRyaW0oKHN0cmluZykgJF9HRVRbJ2V4dCddKSkgOiAncG5nJzsKCmlmICghcHJlZ19tYXRjaCgnL15bYS1mMC05XC1dezM2fSQvaScsICR1dWlkKSB8fCAhaW5fYXJyYXkoJHcsIFs2MDAsIDk2MCwgMTkyMF0sIHRydWUpIHx8ICFpbl9hcnJheSgkZXh0LCBbJ3BuZycsICdqcGcnXSwgdHJ1ZSkpIHsKICAgIGh0dHBfcmVzcG9uc2VfY29kZSg0MDQpOwogICAgaGVhZGVyKCdDb250ZW50LVR5cGU6IHRleHQvcGxhaW47IGNoYXJzZXQ9VVRGLTgnKTsKICAgIGVjaG8gJ05vdCBmb3VuZCc7CiAgICBleGl0Owp9Cgokcm9vdCA9IGRpcm5hbWUoX19ESVJfXyk7CiRkaXIgPSAkcm9vdCAuICcvc2FsZXMtaW1nJzsKJGRlc3QgPSAkZGlyIC4gJy8nIC4gJHV1aWQgLiAnXycgLiAkdyAuICcuJyAuICRleHQ7CiRkZXN0UG5nID0gJGRpciAuICcvJyAuICR1dWlkIC4gJ18nIC4gJHcgLiAnLnBuZyc7CgppZiAoaXNfZmlsZSgkZGVzdCkgJiYgZmlsZXNpemUoJGRlc3QpID4gNTAwKSB7CiAgICBzZXJ2ZV9maWxlKCRkZXN0LCAkZXh0KTsKfQoKaWYgKCRleHQgPT09ICdqcGcnICYmIGlzX2ZpbGUoJGRlc3RQbmcpICYmIGZpbGVzaXplKCRkZXN0UG5nKSA+IDUwMCkgewogICAgLy8gUHJlZmVyIGNvbnZlcnRpbmcgZXhpc3RpbmcgcG5nIGNhY2hlIHRvIGpwZyBvbmNlLgogICAgaWYgKGNvbnZlcnRfYW5kX3NhdmUoJGRlc3RQbmcsICRkZXN0LCAkdywgJGV4dCkpIHsKICAgICAgICBzZXJ2ZV9maWxlKCRkZXN0LCAkZXh0KTsKICAgIH0KfQoKaWYgKCFpc19kaXIoJGRpcikpIHsKICAgIEBta2RpcigkZGlyLCAwNzU1LCB0cnVlKTsKfQoKJHRtcCA9IHN5c19nZXRfdGVtcF9kaXIoKSAuICcvc2FsZXNfaW1nXycgLiAkdXVpZCAuICdfJyAuICR3IC4gJ18nIC4gZ2V0bXlwaWQoKSAuICcuYmluJzsKQHVubGluaygkdG1wKTsKCiRzb3VyY2VzID0gWwogICAgJ2h0dHBzOi8vc2l0ZWZpbGUuYmFobWFuLmlyL3RodW1iLycgLiByYXd1cmxlbmNvZGUoJHV1aWQpIC4gJy8nIC4gJHcsCiAgICAnaHR0cHM6Ly9zaXRlZmlsZS5iYWhtYW4uaXIvJyAuIHJhd3VybGVuY29kZSgkdXVpZCksCl07CgokcmF3ID0gbnVsbDsKZm9yZWFjaCAoJHNvdXJjZXMgYXMgJHVybCkgewogICAgJHJhdyA9IGZldGNoX3VybCgkdXJsKTsKICAgIGlmICgkcmF3ICE9PSBudWxsICYmIGxvb2tzX2xpa2VfaW1hZ2UoJHJhdykpIHsKICAgICAgICBicmVhazsKICAgIH0KICAgICRyYXcgPSBudWxsOwp9CgppZiAoJHJhdyA9PT0gbnVsbCkgewogICAgaHR0cF9yZXNwb25zZV9jb2RlKDQwNCk7CiAgICBoZWFkZXIoJ0NvbnRlbnQtVHlwZTogdGV4dC9wbGFpbjsgY2hhcnNldD1VVEYtOCcpOwogICAgZWNobyAnTm90IGZvdW5kJzsKICAgIGV4aXQ7Cn0KCmZpbGVfcHV0X2NvbnRlbnRzKCR0bXAsICRyYXcpOwokb2sgPSBjb252ZXJ0X2FuZF9zYXZlKCR0bXAsICRkZXN0LCAkdywgJGV4dCk7CkB1bmxpbmsoJHRtcCk7CgppZiAoISRvayB8fCAhaXNfZmlsZSgkZGVzdCkgfHwgZmlsZXNpemUoJGRlc3QpIDwgNTAwKSB7CiAgICAvLyBMYXN0IHJlc29ydDogc3RvcmUgb3JpZ2luYWwgYnl0ZXMgd2l0aCByZXF1ZXN0ZWQgbmFtZS4KICAgIGlmICgkcmF3ICE9PSBudWxsICYmIGxvb2tzX2xpa2VfaW1hZ2UoJHJhdykpIHsKICAgICAgICBAZmlsZV9wdXRfY29udGVudHMoJGRlc3QsICRyYXcpOwogICAgfQp9CgppZiAoIWlzX2ZpbGUoJGRlc3QpIHx8IGZpbGVzaXplKCRkZXN0KSA8IDUwMCkgewogICAgaHR0cF9yZXNwb25zZV9jb2RlKDQwNCk7CiAgICBoZWFkZXIoJ0NvbnRlbnQtVHlwZTogdGV4dC9wbGFpbjsgY2hhcnNldD1VVEYtOCcpOwogICAgZWNobyAnTm90IGZvdW5kJzsKICAgIGV4aXQ7Cn0KCkBjaG1vZCgkZGVzdCwgMDY0NCk7CnNlcnZlX2ZpbGUoJGRlc3QsICRleHQpOwoKZnVuY3Rpb24gc2VydmVfZmlsZShzdHJpbmcgJHBhdGgsIHN0cmluZyAkZXh0KTogdm9pZAp7CiAgICAkY3R5cGUgPSAkZXh0ID09PSAnanBnJyA/ICdpbWFnZS9qcGVnJyA6ICdpbWFnZS9wbmcnOwogICAgaGVhZGVyKCdDb250ZW50LVR5cGU6ICcgLiAkY3R5cGUpOwogICAgaGVhZGVyKCdDb250ZW50LUxlbmd0aDogJyAuIChzdHJpbmcpIGZpbGVzaXplKCRwYXRoKSk7CiAgICBoZWFkZXIoJ0NhY2hlLUNvbnRyb2w6IHB1YmxpYywgbWF4LWFnZT04NjQwMCcpOwogICAgaGVhZGVyKCdYLVNhbGVzLUltZzogaGl0Jyk7CiAgICByZWFkZmlsZSgkcGF0aCk7CiAgICBleGl0Owp9CgpmdW5jdGlvbiBmZXRjaF91cmwoc3RyaW5nICR1cmwpOiA/c3RyaW5nCnsKICAgIGlmIChmdW5jdGlvbl9leGlzdHMoJ2N1cmxfaW5pdCcpKSB7CiAgICAgICAgJGNoID0gY3VybF9pbml0KCR1cmwpOwogICAgICAgIGN1cmxfc2V0b3B0X2FycmF5KCRjaCwgWwogICAgICAgICAgICBDVVJMT1BUX1JFVFVSTlRSQU5TRkVSID0+IHRydWUsCiAgICAgICAgICAgIENVUkxPUFRfRk9MTE9XTE9DQVRJT04gPT4gdHJ1ZSwKICAgICAgICAgICAgQ1VSTE9QVF9DT05ORUNUVElNRU9VVCA9PiA4LAogICAgICAgICAgICBDVVJMT1BUX1RJTUVPVVQgPT4gNDAsCiAgICAgICAgICAgIENVUkxPUFRfU1NMX1ZFUklGWVBFRVIgPT4gZmFsc2UsCiAgICAgICAgICAgIENVUkxPUFRfU1NMX1ZFUklGWUhPU1QgPT4gMCwKICAgICAgICAgICAgQ1VSTE9QVF9VU0VSQUdFTlQgPT4gJ2JhaG1hbi1zYWxlcy1pbWcvMS4wJywKICAgICAgICAgICAgQ1VSTE9QVF9IVFRQSEVBREVSID0+IFsnQWNjZXB0OiBpbWFnZS8qLCovKiddLAogICAgICAgIF0pOwogICAgICAgICRib2R5ID0gY3VybF9leGVjKCRjaCk7CiAgICAgICAgJGNvZGUgPSAoaW50KSBjdXJsX2dldGluZm8oJGNoLCBDVVJMSU5GT19IVFRQX0NPREUpOwogICAgICAgIGN1cmxfY2xvc2UoJGNoKTsKICAgICAgICBpZiAoJGNvZGUgPj0gMjAwICYmICRjb2RlIDwgMzAwICYmIGlzX3N0cmluZygkYm9keSkgJiYgc3RybGVuKCRib2R5KSA+IDUwMCkgewogICAgICAgICAgICByZXR1cm4gJGJvZHk7CiAgICAgICAgfQogICAgICAgIHJldHVybiBudWxsOwogICAgfQoKICAgICRjdHggPSBzdHJlYW1fY29udGV4dF9jcmVhdGUoWwogICAgICAgICdodHRwJyA9PiBbCiAgICAgICAgICAgICd0aW1lb3V0JyA9PiA0MCwKICAgICAgICAgICAgJ2ZvbGxvd19sb2NhdGlvbicgPT4gMSwKICAgICAgICAgICAgJ2hlYWRlcicgPT4gIkFjY2VwdDogaW1hZ2UvKlxyXG5Vc2VyLUFnZW50OiBiYWhtYW4tc2FsZXMtaW1nLzEuMFxyXG4iLAogICAgICAgIF0sCiAgICAgICAgJ3NzbCcgPT4gWwogICAgICAgICAgICAndmVyaWZ5X3BlZXInID0+IGZhbHNlLAogICAgICAgICAgICAndmVyaWZ5X3BlZXJfbmFtZScgPT4gZmFsc2UsCiAgICAgICAgXSwKICAgIF0pOwogICAgJGJvZHkgPSBAZmlsZV9nZXRfY29udGVudHMoJHVybCwgZmFsc2UsICRjdHgpOwogICAgaWYgKGlzX3N0cmluZygkYm9keSkgJiYgc3RybGVuKCRib2R5KSA+IDUwMCkgewogICAgICAgIHJldHVybiAkYm9keTsKICAgIH0KICAgIHJldHVybiBudWxsOwp9CgpmdW5jdGlvbiBsb29rc19saWtlX2ltYWdlKHN0cmluZyAkYmluKTogYm9vbAp7CiAgICBpZiAoc3RybmNtcCgkYmluLCAiXHg4OVBOR1xyXG5ceDFhXG4iLCA4KSA9PT0gMCkgewogICAgICAgIHJldHVybiB0cnVlOwogICAgfQogICAgaWYgKHN0cm5jbXAoJGJpbiwgIlx4RkZceEQ4XHhGRiIsIDMpID09PSAwKSB7CiAgICAgICAgcmV0dXJuIHRydWU7CiAgICB9CiAgICBpZiAoc3RybmNtcCgkYmluLCAnUklGRicsIDQpID09PSAwICYmIHN1YnN0cigkYmluLCA4LCA0KSA9PT0gJ1dFQlAnKSB7CiAgICAgICAgcmV0dXJuIHRydWU7CiAgICB9CiAgICAvLyBTb21lIGdhdGV3YXlzIG1pc2xhYmVsIGNvbnRlbnQtdHlwZTsgcmVqZWN0IG9idmlvdXMgSFRNTC4KICAgICRoZWFkID0gbHRyaW0oc3Vic3RyKCRiaW4sIDAsIDIwMCkpOwogICAgaWYgKHN0cmlwb3MoJGhlYWQsICc8IWRvY3R5cGUnKSA9PT0gMCB8fCBzdHJpcG9zKCRoZWFkLCAnPGh0bWwnKSA9PT0gMCkgewogICAgICAgIHJldHVybiBmYWxzZTsKICAgIH0KICAgIHJldHVybiBmYWxzZTsKfQoKZnVuY3Rpb24gY29udmVydF9hbmRfc2F2ZShzdHJpbmcgJHNyY1BhdGgsIHN0cmluZyAkZGVzdFBhdGgsIGludCAkdywgc3RyaW5nICRleHQpOiBib29sCnsKICAgIGlmICghZnVuY3Rpb25fZXhpc3RzKCdpbWFnZWNyZWF0ZWZyb21zdHJpbmcnKSkgewogICAgICAgIHJldHVybiBAY29weSgkc3JjUGF0aCwgJGRlc3RQYXRoKTsKICAgIH0KICAgICRiaW4gPSBAZmlsZV9nZXRfY29udGVudHMoJHNyY1BhdGgpOwogICAgaWYgKCRiaW4gPT09IGZhbHNlIHx8ICRiaW4gPT09ICcnKSB7CiAgICAgICAgcmV0dXJuIGZhbHNlOwogICAgfQogICAgJGltID0gQGltYWdlY3JlYXRlZnJvbXN0cmluZygkYmluKTsKICAgIGlmICgkaW0gPT09IGZhbHNlKSB7CiAgICAgICAgcmV0dXJuIEBjb3B5KCRzcmNQYXRoLCAkZGVzdFBhdGgpOwogICAgfQoKICAgICRzdyA9IGltYWdlc3goJGltKTsKICAgICRzaCA9IGltYWdlc3koJGltKTsKICAgIGlmICgkc3cgPCAxIHx8ICRzaCA8IDEpIHsKICAgICAgICBpbWFnZWRlc3Ryb3koJGltKTsKICAgICAgICByZXR1cm4gZmFsc2U7CiAgICB9CgogICAgJG53ID0gJHc7CiAgICAkbmggPSAoaW50KSBtYXgoMSwgcm91bmQoJHNoICogKCRudyAvICRzdykpKTsKICAgICRvdXQgPSBpbWFnZWNyZWF0ZXRydWVjb2xvcigkbncsICRuaCk7CiAgICBpZiAoJG91dCA9PT0gZmFsc2UpIHsKICAgICAgICBpbWFnZWRlc3Ryb3koJGltKTsKICAgICAgICByZXR1cm4gZmFsc2U7CiAgICB9CgogICAgaWYgKCRleHQgPT09ICdwbmcnKSB7CiAgICAgICAgaW1hZ2VhbHBoYWJsZW5kaW5nKCRvdXQsIGZhbHNlKTsKICAgICAgICBpbWFnZXNhdmVhbHBoYSgkb3V0LCB0cnVlKTsKICAgICAgICAkdHJhbnNwYXJlbnQgPSBpbWFnZWNvbG9yYWxsb2NhdGVhbHBoYSgkb3V0LCAwLCAwLCAwLCAxMjcpOwogICAgICAgIGltYWdlZmlsbGVkcmVjdGFuZ2xlKCRvdXQsIDAsIDAsICRudywgJG5oLCAkdHJhbnNwYXJlbnQpOwogICAgfSBlbHNlIHsKICAgICAgICAkd2hpdGUgPSBpbWFnZWNvbG9yYWxsb2NhdGUoJG91dCwgMjU1LCAyNTUsIDI1NSk7CiAgICAgICAgaW1hZ2VmaWxsZWRyZWN0YW5nbGUoJG91dCwgMCwgMCwgJG53LCAkbmgsICR3aGl0ZSk7CiAgICB9CgogICAgaW1hZ2Vjb3B5cmVzYW1wbGVkKCRvdXQsICRpbSwgMCwgMCwgMCwgMCwgJG53LCAkbmgsICRzdywgJHNoKTsKICAgIGltYWdlZGVzdHJveSgkaW0pOwoKICAgICRvayA9IGZhbHNlOwogICAgaWYgKCRleHQgPT09ICdqcGcnKSB7CiAgICAgICAgJG9rID0gaW1hZ2VqcGVnKCRvdXQsICRkZXN0UGF0aCwgODUpOwogICAgfSBlbHNlIHsKICAgICAgICAkb2sgPSBpbWFnZXBuZygkb3V0LCAkZGVzdFBhdGgsIDYpOwogICAgfQogICAgaW1hZ2VkZXN0cm95KCRvdXQpOwogICAgcmV0dXJuIChib29sKSAkb2sgJiYgaXNfZmlsZSgkZGVzdFBhdGgpICYmIGZpbGVzaXplKCRkZXN0UGF0aCkgPiA1MDA7Cn0K'));
echo "php_bytes=".filesize($tmpPhp)."\n";
echo "php_cp=".sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmpPhp).' '.escapeshellarg($destPhp).' 2>&1')."\n";
@unlink($tmpPhp);
echo "php_ok=".(is_file($destPhp) && strpos(file_get_contents($destPhp),'On-demand builder')!==false?'1':'0')."\n";

// 2) patch htaccess
$ht = '/var/www/vhosts/bahman.ir/httpdocs/.htaccess';
$hc = file_get_contents($ht);
if (strpos($hc, 'media/sales_img.php') !== false) {
  echo "HT_ALREADY\n";
} else {
  $needle = 'RewriteRule ^media/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})\\.mp4$ media/video.php?id=$1 [L,QSA]';
  $insert = $needle."\n\n    # Auto-build missing sales carousel/banner images from sitefile on first request.\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^sales-img/([0-9a-fA-F\\-]{36})_(600|960|1920)\\.(png|jpg)$ media/sales_img.php?uuid=$1&w=$2&ext=$3 [L,QSA]";
  if (strpos($hc, $needle) === false) {
    // try after RewriteEngine On
    $alt = "    RewriteEngine On\n";
    if (strpos($hc, $alt) !== false) {
      $hc = str_replace($alt, $alt."\n    # Auto-build missing sales carousel/banner images from sitefile on first request.\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^sales-img/([0-9a-fA-F\\-]{36})_(600|960|1920)\\.(png|jpg)$ media/sales_img.php?uuid=$1&w=$2&ext=$3 [L,QSA]\n", $hc);
      echo "HT_INSERTED_AFTER_ENGINE\n";
    } else {
      echo "HT_NEEDLE_MISS\n";
      echo substr($hc, 0, 400)."\n";
    }
  } else {
    $hc = str_replace($needle, $insert, $hc);
    echo "HT_INSERTED\n";
  }
  $tmpHt = '/tmp/_htaccess_salesimg';
  file_put_contents($tmpHt, $hc);
  echo "ht_cp=".sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmpHt).' '.escapeshellarg($ht).' 2>&1')."\n";
  @unlink($tmpHt);
}
echo "ht_has=".(strpos(file_get_contents($ht),'media/sales_img.php')!==false?'1':'0')."\n";

// 3) patch sales.tpl onerror
$tpl = '/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/sales.tpl';
$c = file_get_contents($tpl);
$oldSlider = <<<'OLD'
                                                    <img class="image"
                                                        src="/sales-img/{$pro.values.image}_600.png"
                                                        alt="{$pro.values.title|default: ''}"
                                                        width="600"
                                                        height="400"
                                                        loading="lazy"
                                                        decoding="async"
                                                        data-title="{$pro.values.title|default: ''|escape:'html'}"
                                                        data-desc="{$pro.values.desc|default: ''|escape:'html'}"
                                                        {if isset($pro.values.attachemnt) and $pro.values.attachemnt neq ''}data-show-link="{$admin_setting.file_url}/{$pro.values.attachemnt}"{/if}
                                                        {if isset($pro.values.product) and $pro.values.product neq ''}data-product-link="{$admin_setting.site_url}/product/{$pro.values.product}"{/if}
                                                        {if isset($pro.values.sales_link) and $pro.values.sales_link neq ''}data-buy-link="{$pro.values.sales_link}"{/if}>
OLD;
$newSlider = <<<'NEW'
                                                    <img class="image"
                                                        src="/sales-img/{$pro.values.image}_600.png"
                                                        alt="{$pro.values.title|default: ''}"
                                                        width="600"
                                                        height="400"
                                                        loading="lazy"
                                                        decoding="async"
                                                        onerror="this.onerror=null;this.src='{$admin_setting.file_url}/thumb/{$pro.values.image}/600';"
                                                        data-title="{$pro.values.title|default: ''|escape:'html'}"
                                                        data-desc="{$pro.values.desc|default: ''|escape:'html'}"
                                                        {if isset($pro.values.attachemnt) and $pro.values.attachemnt neq ''}data-show-link="{$admin_setting.file_url}/{$pro.values.attachemnt}"{/if}
                                                        {if isset($pro.values.product) and $pro.values.product neq ''}data-product-link="{$admin_setting.site_url}/product/{$pro.values.product}"{/if}
                                                        {if isset($pro.values.sales_link) and $pro.values.sales_link neq ''}data-buy-link="{$pro.values.sales_link}"{/if}>
NEW;
if (strpos($c, "thumb/{\$pro.values.image}/600") !== false) {
  echo "SLIDER_ALREADY\n";
} elseif (strpos($c, $oldSlider) === false) {
  echo "SLIDER_MISS\n";
  $p = strpos($c, 'class="image"');
  echo substr($c, max(0,$p-40), 420)."\n";
} else {
  $c = str_replace($oldSlider, $newSlider, $c);
  echo "SLIDER_PATCHED\n";
}
$oldB = 'src="/sales-img/{$sales_page.values.banner}_1920.jpg" class="img" alt="{$sales_page.values.title}" width="1920" height="480" decoding="async" fetchpriority="high">';
$newB = 'src="/sales-img/{$sales_page.values.banner}_1920.jpg" class="img" alt="{$sales_page.values.title}" width="1920" height="480" decoding="async" fetchpriority="high" onerror="this.onerror=null;this.src=\'{$admin_setting.file_url}/thumb/{$sales_page.values.banner}/1920\'">';
if (strpos($c, "thumb/{\$sales_page.values.banner}/1920") !== false) {
  echo "BANNER_ALREADY\n";
} elseif (strpos($c, $oldB) === false) {
  echo "BANNER_MISS\n";
} else {
  $c = str_replace($oldB, $newB, $c);
  echo "BANNER_PATCHED\n";
}
$tmpTpl = '/tmp/_sales_tpl_auto';
file_put_contents($tmpTpl, $c);
echo "tpl_cp=".sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmpTpl).' '.escapeshellarg($tpl).' 2>&1')."\n";
@unlink($tmpTpl);
$chk = file_get_contents($tpl);
echo "tpl_slider=".(strpos($chk,"thumb/{\$pro.values.image}/600")!==false?'1':'0')."\n";
echo "tpl_banner=".(strpos($chk,"thumb/{\$sales_page.values.banner}/1920")!==false?'1':'0')."\n";

// 4) clear smarty + sales page caches
$n=0;
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*.php') ?: [] as $f) {
  $t=@file_get_contents($f);
  if ($t && (strpos($t,'sales-img')!==false || strpos($t,'sales.tpl')!==false)) { @unlink($f); $n++; }
}
echo "cleared_compile=$n\n";
foreach ([
  '/var/www/vhosts/bahman.ir/httpdocs/tmp/bahman_sales_page_v4.html',
  '/var/www/vhosts/bahman.ir/httpdocs/tmp/bahman_sales_page_data.cache',
  '/tmp/bahman_sales_page_v4.html',
] as $f) {
  if (is_file($f)) { @unlink($f); echo "rm=$f\n"; }
}
// also wipe common FAST_MODE cache names
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/tmp/*sales*') ?: [] as $f) { @unlink($f); echo "rm2=".basename($f)."\n"; }

// 5) functional test: delete a temp name? use a fake missing uuid that exists on sitefile
// Re-request a known uuid file via generator path by temporarily renaming? Better: request a non-existing size then check.
$testUuid = '2b8db709-b4c4-11f1-ad46-005056b972d8';
$testPath = '/var/www/vhosts/bahman.ir/httpdocs/sales-img/'.$testUuid.'_960.png';
// ensure missing then hit via apache
if (is_file($testPath)) {
  @rename($testPath, $testPath.'.baktest');
  echo "renamed_for_test=1\n";
}
$curl = sh('curl -s -o /tmp/_gen_test.png -w "%{http_code} %{size_download} %{content_type}" --max-time 40 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$testUuid.'_960.png"');
echo "gen_curl=$curl\n";
echo "gen_file=".(is_file($testPath)?filesize($testPath):0)."\n";
echo "gen_type=".sh('file -b /tmp/_gen_test.png 2>/dev/null')."\n";
if (is_file($testPath.'.baktest') && !is_file($testPath)) {
  @rename($testPath.'.baktest', $testPath);
}
@page = sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/sales');
echo "page_onerror=".(strpos($page,'onerror=')!==false?'1':'0')."\n";
echo "DONE\n";
