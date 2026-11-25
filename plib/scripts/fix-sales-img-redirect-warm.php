<?php
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
function sh($c){return trim((string)shell_exec($c));}

// 1) deploy sales_img.php
$dest='/var/www/vhosts/bahman.ir/httpdocs/media/sales_img.php';
$tmp='/tmp/_sales_img_php2';
file_put_contents($tmp, base64_decode('PD9waHAKLyoqCiAqIE9uLWRlbWFuZCAvc2FsZXMtaW1nL3t1dWlkfV97d30ue2V4dH0KICoKICogSWYgdGhlIHN0YXRpYyBmaWxlIGV4aXN0cyDihpIgc2VydmUgaXQuCiAqIElmIG1pc3Npbmcg4oaSIGltbWVkaWF0ZWx5IHJlZGlyZWN0IHRvIHNpdGVmaWxlIHRodW1iIChmYXN0IFVYKSwKICogdGhlbiBiZXN0LWVmZm9ydCBjYWNoZSBhIGxvY2FsIGNvcHkgZm9yIG5leHQgcmVxdWVzdHMuCiAqLwoKJHV1aWQgPSBpc3NldCgkX0dFVFsndXVpZCddKSA/IHRyaW0oKHN0cmluZykgJF9HRVRbJ3V1aWQnXSkgOiAnJzsKJHcgPSBpc3NldCgkX0dFVFsndyddKSA/IChpbnQpICRfR0VUWyd3J10gOiAwOwokZXh0ID0gaXNzZXQoJF9HRVRbJ2V4dCddKSA/IHN0cnRvbG93ZXIodHJpbSgoc3RyaW5nKSAkX0dFVFsnZXh0J10pKSA6ICdwbmcnOwoKaWYgKCFwcmVnX21hdGNoKCcvXlthLWYwLTlcLV17MzZ9JC9pJywgJHV1aWQpIHx8ICFpbl9hcnJheSgkdywgWzYwMCwgOTYwLCAxOTIwXSwgdHJ1ZSkgfHwgIWluX2FycmF5KCRleHQsIFsncG5nJywgJ2pwZyddLCB0cnVlKSkgewogICAgaHR0cF9yZXNwb25zZV9jb2RlKDQwNCk7CiAgICBoZWFkZXIoJ0NvbnRlbnQtVHlwZTogdGV4dC9wbGFpbjsgY2hhcnNldD1VVEYtOCcpOwogICAgZWNobyAnTm90IGZvdW5kJzsKICAgIGV4aXQ7Cn0KCiRyb290ID0gZGlybmFtZShfX0RJUl9fKTsKJGRpciA9ICRyb290IC4gJy9zYWxlcy1pbWcnOwokZGVzdCA9ICRkaXIgLiAnLycgLiAkdXVpZCAuICdfJyAuICR3IC4gJy4nIC4gJGV4dDsKJGRlc3RQbmcgPSAkZGlyIC4gJy8nIC4gJHV1aWQgLiAnXycgLiAkdyAuICcucG5nJzsKJHRodW1iVXJsID0gJ2h0dHBzOi8vc2l0ZWZpbGUuYmFobWFuLmlyL3RodW1iLycgLiByYXd1cmxlbmNvZGUoJHV1aWQpIC4gJy8nIC4gJHc7CiRyYXdVcmwgPSAnaHR0cHM6Ly9zaXRlZmlsZS5iYWhtYW4uaXIvJyAuIHJhd3VybGVuY29kZSgkdXVpZCk7CgppZiAoaXNfZmlsZSgkZGVzdCkgJiYgZmlsZXNpemUoJGRlc3QpID4gNTAwKSB7CiAgICBzZXJ2ZV9maWxlKCRkZXN0LCAkZXh0KTsKfQoKaWYgKCRleHQgPT09ICdqcGcnICYmIGlzX2ZpbGUoJGRlc3RQbmcpICYmIGZpbGVzaXplKCRkZXN0UG5nKSA+IDUwMCkgewogICAgaWYgKGNvbnZlcnRfYW5kX3NhdmUoJGRlc3RQbmcsICRkZXN0LCAkdywgJGV4dCkpIHsKICAgICAgICBzZXJ2ZV9maWxlKCRkZXN0LCAkZXh0KTsKICAgIH0KfQoKLy8gRmFzdCBwYXRoOiBuZXZlciBtYWtlIHRoZSBicm93c2VyIHdhaXQgb24gYSBzbG93L2ZhaWxpbmcgYnVpbGQuCi8vIENhY2hlIGxvY2FsbHkgaW4gdGhlIGJhY2tncm91bmQgb2YgdGhpcyByZXF1ZXN0IGFmdGVyIHNlbmRpbmcgcmVkaXJlY3QuCmlmICghaGVhZGVyc19zZW50KCkpIHsKICAgIGhlYWRlcignQ2FjaGUtQ29udHJvbDogbm8tc3RvcmUnKTsKICAgIGhlYWRlcignWC1TYWxlcy1JbWc6IHJlZGlyZWN0Jyk7CiAgICBoZWFkZXIoJ0xvY2F0aW9uOiAnIC4gJHRodW1iVXJsLCB0cnVlLCAzMDIpOwp9CgovLyBBdHRlbXB0IGEgcXVpY2sgbG9jYWwgY2FjaGUgZm9yIHN1YnNlcXVlbnQgaGl0cyAoaWdub3JlIGZhaWx1cmVzKS4KaWdub3JlX3VzZXJfYWJvcnQodHJ1ZSk7CkBodHRwX3Jlc3BvbnNlX2NvZGUoMzAyKTsKaWYgKGZ1bmN0aW9uX2V4aXN0cygnZmFzdGNnaV9maW5pc2hfcmVxdWVzdCcpKSB7CiAgICBAZmFzdGNnaV9maW5pc2hfcmVxdWVzdCgpOwp9CgppZiAoIWlzX2RpcigkZGlyKSkgewogICAgQG1rZGlyKCRkaXIsIDA3NTUsIHRydWUpOwp9CgokdG1wID0gc3lzX2dldF90ZW1wX2RpcigpIC4gJy9zYWxlc19pbWdfJyAuICR1dWlkIC4gJ18nIC4gJHcgLiAnXycgLiBnZXRteXBpZCgpIC4gJy5iaW4nOwpAdW5saW5rKCR0bXApOwoKJHJhdyA9IGZldGNoX3VybCgkdGh1bWJVcmwsIDgpOwppZiAoJHJhdyA9PT0gbnVsbCB8fCAhbG9va3NfbGlrZV9pbWFnZSgkcmF3KSkgewogICAgJHJhdyA9IGZldGNoX3VybCgkcmF3VXJsLCA4KTsKfQppZiAoJHJhdyAhPT0gbnVsbCAmJiBsb29rc19saWtlX2ltYWdlKCRyYXcpKSB7CiAgICBAZmlsZV9wdXRfY29udGVudHMoJHRtcCwgJHJhdyk7CiAgICAkb2sgPSBjb252ZXJ0X2FuZF9zYXZlKCR0bXAsICRkZXN0LCAkdywgJGV4dCk7CiAgICBAdW5saW5rKCR0bXApOwogICAgaWYgKCghJG9rIHx8ICFpc19maWxlKCRkZXN0KSB8fCBmaWxlc2l6ZSgkZGVzdCkgPCA1MDApICYmIGxvb2tzX2xpa2VfaW1hZ2UoJHJhdykpIHsKICAgICAgICBAZmlsZV9wdXRfY29udGVudHMoJGRlc3QsICRyYXcpOwogICAgfQogICAgaWYgKGlzX2ZpbGUoJGRlc3QpKSB7CiAgICAgICAgQGNobW9kKCRkZXN0LCAwNjQ0KTsKICAgIH0KfQpleGl0OwoKZnVuY3Rpb24gc2VydmVfZmlsZShzdHJpbmcgJHBhdGgsIHN0cmluZyAkZXh0KTogdm9pZAp7CiAgICAkY3R5cGUgPSAkZXh0ID09PSAnanBnJyA/ICdpbWFnZS9qcGVnJyA6ICdpbWFnZS9wbmcnOwogICAgaGVhZGVyKCdDb250ZW50LVR5cGU6ICcgLiAkY3R5cGUpOwogICAgaGVhZGVyKCdDb250ZW50LUxlbmd0aDogJyAuIChzdHJpbmcpIGZpbGVzaXplKCRwYXRoKSk7CiAgICBoZWFkZXIoJ0NhY2hlLUNvbnRyb2w6IHB1YmxpYywgbWF4LWFnZT04NjQwMCcpOwogICAgaGVhZGVyKCdYLVNhbGVzLUltZzogaGl0Jyk7CiAgICByZWFkZmlsZSgkcGF0aCk7CiAgICBleGl0Owp9CgpmdW5jdGlvbiBmZXRjaF91cmwoc3RyaW5nICR1cmwsIGludCAkdGltZW91dCA9IDgpOiA/c3RyaW5nCnsKICAgIGlmIChmdW5jdGlvbl9leGlzdHMoJ2N1cmxfaW5pdCcpKSB7CiAgICAgICAgJGNoID0gY3VybF9pbml0KCR1cmwpOwogICAgICAgIGN1cmxfc2V0b3B0X2FycmF5KCRjaCwgWwogICAgICAgICAgICBDVVJMT1BUX1JFVFVSTlRSQU5TRkVSID0+IHRydWUsCiAgICAgICAgICAgIENVUkxPUFRfRk9MTE9XTE9DQVRJT04gPT4gdHJ1ZSwKICAgICAgICAgICAgQ1VSTE9QVF9DT05ORUNUVElNRU9VVCA9PiBtaW4oNSwgJHRpbWVvdXQpLAogICAgICAgICAgICBDVVJMT1BUX1RJTUVPVVQgPT4gJHRpbWVvdXQsCiAgICAgICAgICAgIENVUkxPUFRfU1NMX1ZFUklGWVBFRVIgPT4gZmFsc2UsCiAgICAgICAgICAgIENVUkxPUFRfU1NMX1ZFUklGWUhPU1QgPT4gMCwKICAgICAgICAgICAgQ1VSTE9QVF9VU0VSQUdFTlQgPT4gJ2JhaG1hbi1zYWxlcy1pbWcvMS4xJywKICAgICAgICAgICAgQ1VSTE9QVF9IVFRQSEVBREVSID0+IFsnQWNjZXB0OiBpbWFnZS8qLCovKiddLAogICAgICAgIF0pOwogICAgICAgICRib2R5ID0gY3VybF9leGVjKCRjaCk7CiAgICAgICAgJGNvZGUgPSAoaW50KSBjdXJsX2dldGluZm8oJGNoLCBDVVJMSU5GT19IVFRQX0NPREUpOwogICAgICAgIGN1cmxfY2xvc2UoJGNoKTsKICAgICAgICBpZiAoJGNvZGUgPj0gMjAwICYmICRjb2RlIDwgMzAwICYmIGlzX3N0cmluZygkYm9keSkgJiYgc3RybGVuKCRib2R5KSA+IDUwMCkgewogICAgICAgICAgICByZXR1cm4gJGJvZHk7CiAgICAgICAgfQogICAgICAgIHJldHVybiBudWxsOwogICAgfQoKICAgICRjdHggPSBzdHJlYW1fY29udGV4dF9jcmVhdGUoWwogICAgICAgICdodHRwJyA9PiBbCiAgICAgICAgICAgICd0aW1lb3V0JyA9PiAkdGltZW91dCwKICAgICAgICAgICAgJ2ZvbGxvd19sb2NhdGlvbicgPT4gMSwKICAgICAgICAgICAgJ2hlYWRlcicgPT4gIkFjY2VwdDogaW1hZ2UvKlxyXG5Vc2VyLUFnZW50OiBiYWhtYW4tc2FsZXMtaW1nLzEuMVxyXG4iLAogICAgICAgIF0sCiAgICAgICAgJ3NzbCcgPT4gWwogICAgICAgICAgICAndmVyaWZ5X3BlZXInID0+IGZhbHNlLAogICAgICAgICAgICAndmVyaWZ5X3BlZXJfbmFtZScgPT4gZmFsc2UsCiAgICAgICAgXSwKICAgIF0pOwogICAgJGJvZHkgPSBAZmlsZV9nZXRfY29udGVudHMoJHVybCwgZmFsc2UsICRjdHgpOwogICAgaWYgKGlzX3N0cmluZygkYm9keSkgJiYgc3RybGVuKCRib2R5KSA+IDUwMCkgewogICAgICAgIHJldHVybiAkYm9keTsKICAgIH0KICAgIHJldHVybiBudWxsOwp9CgpmdW5jdGlvbiBsb29rc19saWtlX2ltYWdlKHN0cmluZyAkYmluKTogYm9vbAp7CiAgICBpZiAoc3RybmNtcCgkYmluLCAiXHg4OVBOR1xyXG5ceDFhXG4iLCA4KSA9PT0gMCkgewogICAgICAgIHJldHVybiB0cnVlOwogICAgfQogICAgaWYgKHN0cm5jbXAoJGJpbiwgIlx4RkZceEQ4XHhGRiIsIDMpID09PSAwKSB7CiAgICAgICAgcmV0dXJuIHRydWU7CiAgICB9CiAgICBpZiAoc3RybmNtcCgkYmluLCAnUklGRicsIDQpID09PSAwICYmIHN1YnN0cigkYmluLCA4LCA0KSA9PT0gJ1dFQlAnKSB7CiAgICAgICAgcmV0dXJuIHRydWU7CiAgICB9CiAgICAkaGVhZCA9IGx0cmltKHN1YnN0cigkYmluLCAwLCAyMDApKTsKICAgIGlmIChzdHJpcG9zKCRoZWFkLCAnPCFkb2N0eXBlJykgPT09IDAgfHwgc3RyaXBvcygkaGVhZCwgJzxodG1sJykgPT09IDApIHsKICAgICAgICByZXR1cm4gZmFsc2U7CiAgICB9CiAgICByZXR1cm4gZmFsc2U7Cn0KCmZ1bmN0aW9uIGNvbnZlcnRfYW5kX3NhdmUoc3RyaW5nICRzcmNQYXRoLCBzdHJpbmcgJGRlc3RQYXRoLCBpbnQgJHcsIHN0cmluZyAkZXh0KTogYm9vbAp7CiAgICBpZiAoIWZ1bmN0aW9uX2V4aXN0cygnaW1hZ2VjcmVhdGVmcm9tc3RyaW5nJykpIHsKICAgICAgICByZXR1cm4gQGNvcHkoJHNyY1BhdGgsICRkZXN0UGF0aCk7CiAgICB9CiAgICAkYmluID0gQGZpbGVfZ2V0X2NvbnRlbnRzKCRzcmNQYXRoKTsKICAgIGlmICgkYmluID09PSBmYWxzZSB8fCAkYmluID09PSAnJykgewogICAgICAgIHJldHVybiBmYWxzZTsKICAgIH0KICAgICRpbSA9IEBpbWFnZWNyZWF0ZWZyb21zdHJpbmcoJGJpbik7CiAgICBpZiAoJGltID09PSBmYWxzZSkgewogICAgICAgIHJldHVybiBAY29weSgkc3JjUGF0aCwgJGRlc3RQYXRoKTsKICAgIH0KCiAgICAkc3cgPSBpbWFnZXN4KCRpbSk7CiAgICAkc2ggPSBpbWFnZXN5KCRpbSk7CiAgICBpZiAoJHN3IDwgMSB8fCAkc2ggPCAxKSB7CiAgICAgICAgaW1hZ2VkZXN0cm95KCRpbSk7CiAgICAgICAgcmV0dXJuIGZhbHNlOwogICAgfQoKICAgICRudyA9ICR3OwogICAgJG5oID0gKGludCkgbWF4KDEsIHJvdW5kKCRzaCAqICgkbncgLyAkc3cpKSk7CiAgICAkb3V0ID0gaW1hZ2VjcmVhdGV0cnVlY29sb3IoJG53LCAkbmgpOwogICAgaWYgKCRvdXQgPT09IGZhbHNlKSB7CiAgICAgICAgaW1hZ2VkZXN0cm95KCRpbSk7CiAgICAgICAgcmV0dXJuIGZhbHNlOwogICAgfQoKICAgIGlmICgkZXh0ID09PSAncG5nJykgewogICAgICAgIGltYWdlYWxwaGFibGVuZGluZygkb3V0LCBmYWxzZSk7CiAgICAgICAgaW1hZ2VzYXZlYWxwaGEoJG91dCwgdHJ1ZSk7CiAgICAgICAgJHRyYW5zcGFyZW50ID0gaW1hZ2Vjb2xvcmFsbG9jYXRlYWxwaGEoJG91dCwgMCwgMCwgMCwgMTI3KTsKICAgICAgICBpbWFnZWZpbGxlZHJlY3RhbmdsZSgkb3V0LCAwLCAwLCAkbncsICRuaCwgJHRyYW5zcGFyZW50KTsKICAgIH0gZWxzZSB7CiAgICAgICAgJHdoaXRlID0gaW1hZ2Vjb2xvcmFsbG9jYXRlKCRvdXQsIDI1NSwgMjU1LCAyNTUpOwogICAgICAgIGltYWdlZmlsbGVkcmVjdGFuZ2xlKCRvdXQsIDAsIDAsICRudywgJG5oLCAkd2hpdGUpOwogICAgfQoKICAgIGltYWdlY29weXJlc2FtcGxlZCgkb3V0LCAkaW0sIDAsIDAsIDAsIDAsICRudywgJG5oLCAkc3csICRzaCk7CiAgICBpbWFnZWRlc3Ryb3koJGltKTsKCiAgICAkb2sgPSBmYWxzZTsKICAgIGlmICgkZXh0ID09PSAnanBnJykgewogICAgICAgICRvayA9IGltYWdlanBlZygkb3V0LCAkZGVzdFBhdGgsIDg1KTsKICAgIH0gZWxzZSB7CiAgICAgICAgJG9rID0gaW1hZ2VwbmcoJG91dCwgJGRlc3RQYXRoLCA2KTsKICAgIH0KICAgIGltYWdlZGVzdHJveSgkb3V0KTsKICAgIHJldHVybiAoYm9vbCkgJG9rICYmIGlzX2ZpbGUoJGRlc3RQYXRoKSAmJiBmaWxlc2l6ZSgkZGVzdFBhdGgpID4gNTAwOwp9Cg=='));
echo "php_cp=".sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($dest))."\n";
@unlink($tmp);
echo "php_ok=".(strpos(file_get_contents($dest),'immediately redirect')!==false?'1':'0')."\n";

// 2) rebuild ALL missing from /sales HTML
$dir='/var/www/vhosts/bahman.ir/httpdocs/sales-img';
$html=sh('curl -s --max-time 25 -H "Host: bahman.ir" -H "Cache-Control: no-cache" "http://172.20.14.76/sales?_='.time().'"');
preg_match_all('#/sales-img/([0-9a-fA-F-]{36})_(600|960|1920)\\.(png|jpg)#', $html, $m);
$needed=[];
for($i=0;$i<count($m[1]);$i++){
  $needed[]=[$m[1][$i], (int)$m[2][$i], $m[3][$i]];
}
// unique
$seen=[]; $list=[];
foreach($needed as $row){
  $k=implode('|',$row);
  if(isset($seen[$k])) continue; $seen[$k]=1; $list[]=$row;
}
echo "needed=".count($list)."\n";
$made=0; $skip=0; $fail=0;
foreach($list as [$uuid,$w,$ext]){
  $destF="$dir/{$uuid}_{$w}.$ext";
  if(is_file($destF) && filesize($destF)>500){ $skip++; continue; }
  $tmpF="/tmp/saleswarm_{$uuid}_{$w}.bin";
  @unlink($tmpF);
  $meta=sh("curl -sL --max-time 25 -H 'Host: sitefile.bahman.ir' -o ".escapeshellarg($tmpF)." -w '%{http_code}:%{size_download}' ".escapeshellarg("http://172.20.14.76/thumb/{$uuid}/{$w}"));
  if(!is_file($tmpF) || filesize($tmpF)<800){
    @unlink($tmpF);
    $meta=sh("curl -sL --max-time 25 -H 'Host: sitefile.bahman.ir' -o ".escapeshellarg($tmpF)." -w '%{http_code}:%{size_download}' ".escapeshellarg("http://172.20.14.76/{$uuid}"));
  }
  if(!is_file($tmpF) || filesize($tmpF)<800){ $fail++; echo "FAIL $uuid $w $meta\n"; @unlink($tmpF); continue; }
  $out="/tmp/saleswarm_out_{$uuid}_{$w}.$ext";
  @unlink($out);
  if(sh('command -v convert')!==''){
    if($ext==='jpg') sh('convert '.escapeshellarg($tmpF).' -resize '.escapeshellarg($w.'x').' -strip -quality 85 '.escapeshellarg($out).' 2>&1');
    else sh('convert '.escapeshellarg($tmpF).' -resize '.escapeshellarg($w.'x').' -strip PNG32:'.escapeshellarg($out).' 2>&1');
  }
  $src=(is_file($out)&&filesize($out)>500)?$out:$tmpF;
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($src).' '.escapeshellarg($destF));
  @unlink($tmpF); @unlink($out);
  if(is_file($destF)&&filesize($destF)>500){ $made++; echo "OK $uuid $w ".filesize($destF)."\n"; }
  else { $fail++; echo "FAIL_CP $uuid $w\n"; }
}
echo "MADE=$made SKIP=$skip FAIL=$fail\n";

// 3) verify G9
$u='7d14bd7a-cb91-11f1-9789-005056b972d8';
echo "g9_file=".(is_file("$dir/{$u}_600.png")?filesize("$dir/{$u}_600.png"):0)."\n";
echo "g9_curl=".sh('curl -s -o /tmp/_g9.png -w "%{http_code} %{time_total} %{size_download} %{redirect_url}" --max-time 20 -H "Host: bahman.ir" "http://172.20.14.76/sales-img/'.$u.'_600.png"')."\n";
echo "g9_type=".sh('file -b /tmp/_g9.png 2>/dev/null')."\n";
echo "DONE\n";
