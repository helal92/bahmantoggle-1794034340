<?php
$html = shell_exec('curl -s --max-time 15 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" http://172.20.14.76/');
echo "len=".strlen($html)."\n";
echo "has brands=".(strpos($html,'class="brands"')!==false?"yes":"no")."\n";
echo "items=".substr_count($html,'brand-carousel__item')."\n";
echo "imgs_thumb70=".preg_match_all('#/thumb/[^/"\']+/70#',$html,$m)."\n";
if (preg_match('/<section[^>]*class="brands"[\s\S]*?<\/section>/',$html,$mm)) {
  echo "SECTION:\n".substr($mm[0],0,2000)."\n";
}
// sample company thumbs network
if (!empty($m[0])) {
  $u=$m[0][0];
  if (strpos($u,'http')!==0) $u='https://sitefile.bahman.ir'.$u;
  echo "sample=$u\n";
  echo trim(shell_exec('curl -s -o /dev/null -w "thumb_code=%{http_code} size=%{size_download} ttfb=%{time_starttransfer}\n" --max-time 10 '.escapeshellarg($u)));
}
echo "\nDONE\n";
