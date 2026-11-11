<?php
function sh($c){return trim((string)shell_exec($c));}
$cfg='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/config.php';
$lines=file($cfg);
for ($i=3910;$i<3995;$i++) if (isset($lines[$i])) echo ($i+1).':'.$lines[$i];

echo "---routes---\n";
echo sh('grep -n "agency\|agents\|عاملیت" '.$cfg.' | head -40')."\n";

// try multiple URLs
foreach (['/agency','/agents','/page/agency','/?include=agency'] as $path) {
  $h=sh('curl -sI --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76'.$path.'" | tr -d "\\r" | head -15');
  $b=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76'.$path.'"');
  echo "PATH $path len=".strlen($b)."\n$h\n";
  if (preg_match('/id="filter-company"[^>]*>(.*?)<\/select>/s',$b,$m)) {
    preg_match_all('/<option value="([^"]*)">([^<]*)<\/option>/',$m[1],$opts,PREG_SET_ORDER);
    echo "  options=".count($opts)."\n";
    foreach (array_slice($opts,0,20) as $o) echo "    [{$o[1]}] {$o[2]}\n";
  } else {
    echo "  no filter-company\n";
    if (stripos($b,'Fatal')!==false) echo "  fatal\n".substr($b,0,300)."\n";
  }
}

// agency-search-select how it builds list
echo "---js---\n";
echo file_get_contents('/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/agency-search-select.js');
echo "\nDONE\n";
