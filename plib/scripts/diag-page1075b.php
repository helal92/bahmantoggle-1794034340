<?php
function ascii($s){return preg_replace('/[^\x20-\x7E\n]/','?',(string)$s);}
function sh($cmd){return ascii(shell_exec($cmd));}
echo "curl:\n".sh('curl -s -o /tmp/p1075.body -w "code=%{http_code} size=%{size_download}\n" --max-time 25 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" "http://172.20.14.76/page/1075"');
echo "title: ".sh('grep -oE "<title>[^<]*" /tmp/p1075.body | head -1')."\n";
echo "h1: ".sh('grep -oE "<h1[^>]*>[^<]*" /tmp/p1075.body | head -2')."\n";
echo "snippet: ".substr(ascii(file_get_contents('/tmp/p1075.body')),0,300)."\n";
foreach ([1075,22,4200,100,500,1000,2000] as $id) {
  $c=trim(shell_exec('curl -s -o /tmp/px.body -w "%{http_code}" --max-time 12 -H "Host: bahman.ir" -H "User-Agent: Mozilla/5.0" "http://172.20.14.76/page/'.$id.'"'));
  $t=trim(sh('grep -oE "<title>[^<]*" /tmp/px.body | head -1'));
  echo "page/$id => $c $t\n";
}
echo "qs_direct:\n".sh('curl -s -o /tmp/pd.body -w "code=%{http_code} size=%{size_download}\n" --max-time 20 -H "Host: bahman.ir" "http://172.20.14.76/index.php?include=show_page&page_id=1075"');
echo "title2: ".sh('grep -oE "<title>[^<]*" /tmp/pd.body | head -1')."\n";
echo "DONE\n";
