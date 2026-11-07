<?php
$html = (string)shell_exec('curl -s --max-time 15 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/');
file_put_contents('/tmp/home-brands.html', $html);
echo "len=".strlen($html)."\n";
echo "has_brands=".(strpos($html,'class="brands"')!==false?'yes':'no')."\n";
echo "items=".substr_count($html,'brand-carousel__item')."\n";
echo "track=".substr_count($html,'brand-carousel__track')."\n";
preg_match_all('#src="([^"]*thumb/[^"]+/70)"#',$html,$m);
echo "thumb_count=".count($m[1])."\n";
if ($m[1]) {
  echo "thumb0=".preg_replace('/[^\x20-\x7E]/','?',$m[1][0])."\n";
  $u=$m[1][0];
  echo trim(shell_exec('curl -s -o /dev/null -w "code=%{http_code} size=%{size_download}\n" --max-time 10 -H "User-Agent: Mozilla/5.0" '.escapeshellarg($u)))."\n";
}
// extract brands markup ascii-safe
if (preg_match('/<section[^>]*class="brands"[\s\S]*?<\/section>/',$html,$mm)) {
  $s=preg_replace('/[^\x20-\x7E\n]/','?',$mm[0]);
  echo "SECTION_ASCII:\n".substr($s,0,1800)."\n";
}
// brand-slider asset version on page
preg_match_all('#brand-slider\.(js|css)[^"\']*#',$html,$a);
echo "assets=".json_encode($a[0])."\n";
echo "DONE\n";
