<?php
echo "TIME ".date('c')."\n";
echo "load ".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "synrecv=".trim(shell_exec("ss -tn state syn-recv | wc -l"))." timewait=".trim(shell_exec("ss -tn state time-wait | wc -l"))."\n";
echo "SYN from:\n".shell_exec("ss -tn state syn-recv | awk 'NR>1{print \$4}' | sed 's/\\[::ffff://;s/\\]//' | cut -d: -f1 | sort | uniq -c | sort -rn | head -10");
echo "apache cpu:\n".shell_exec("ps -eo pcpu,rss,stat,args --sort=-pcpu | grep -E 'apache2|php-fpm: pool' | grep -v grep | head -20");
echo "workers: bahman=".trim(shell_exec("pgrep -cf 'pool bahman.ir'"))." sitefile=".trim(shell_exec("pgrep -cf 'pool sitefile'"))."\n";

// Quick timings
foreach ([
  ['home','bahman.ir','https://172.20.14.76/'],
  ['index.css','bahman.ir','https://172.20.14.76/templates/bahman/assets/index.css'],
  ['nav.css','bahman.ir','https://172.20.14.76/templates/bahman/assets/navigation.css'],
  ['v-mp4','sitefile.bahman.ir','https://172.20.14.76/v/a9b88900-aead-11f1-ad46-005056b972d8.mp4'],
  ['thumb','sitefile.bahman.ir','https://172.20.14.76/thumb/85f7b2b5-d753-11f1-b563-005056b972d8/960'],
] as $t) {
  [$n,$h,$u]=$t;
  $extra = ($n==='v-mp4') ? "-H 'Range: bytes=0-1023'" : '';
  echo "$n: ".trim((string)shell_exec("curl -sk --max-time 15 -o /dev/null -w '%{http_code} ttfb=%{time_starttransfer} total=%{time_total} size=%{size_download}' -H 'Host: $h' $extra '$u'"))."\n";
}

// What does homepage HTML request for assets?
$html=shell_exec('curl -sk --max-time 20 -H "Host: bahman.ir" "https://172.20.14.76/"');
echo "home_len=".strlen($html)." cache_hdr check via -I:\n";
echo shell_exec('curl -skI --max-time 10 -H "Host: bahman.ir" "https://172.20.14.76/" | head -15');
// count css/js/video links
preg_match_all('#https?://[^"\']+\.css[^"\']*#',$html,$m); echo "css_urls=".count(array_unique($m[0]))."\n";
preg_match_all('#https?://sitefile[^"\']+#',$html,$m2); echo "sitefile_refs=".count($m2[0])."\n";
foreach (array_slice(array_unique($m2[0]??[]),0,10) as $u) echo "  $u\n";
preg_match_all('#<(link|script|video)[^>]+>#i',$html,$tags);
echo "link_script_video_tags=".count($tags[0])."\n";
// hero video data-src still present?
if (preg_match_all('/data-src="([^"]+)"/',$html,$dm)) { echo "data-src:\n"; foreach($dm[1] as $d) echo "  $d\n"; }
echo "DONE\n";
