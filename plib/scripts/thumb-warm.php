<?php
$uuids=[
'61bb1145-d84f-11f1-b563-005056b972d8',
'2ec8e505-d74c-11f1-b563-005056b972d8',
'340d73a9-d74b-11f1-b563-005056b972d8',
'a21fea2c-d6bd-11f1-b563-005056b972d8',
'391782e2-d6bd-11f1-b563-005056b972d8',
'03945293-d752-11f1-b563-005056b972d8',
];
foreach ($uuids as $u) {
  foreach (['600','960','1920'] as $sz) {
    $url="https://172.20.14.76/thumb/$u/$sz";
    $cmd='curl -sk --max-time 20 -o /dev/null -w "%{http_code} %{size_download} %{time_total}" -H "Host: sitefile.bahman.ir" '.escapeshellarg($url);
    echo "$u/$sz ".trim(shell_exec($cmd))."\n";
  }
}
echo "ss_syn=".trim(shell_exec("ss -ant state syn-recv 2>/dev/null | wc -l"))."\n";
echo "ss_estab_443=".trim(shell_exec("ss -ant sport = :443 state established 2>/dev/null | wc -l"))."\n";
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "fpm=".trim(shell_exec('ps -eo cmd | grep "[p]hp-fpm: pool sitefile" | wc -l'))."\n";
# apache error for sitefile last lines
$logs=[
'/var/www/vhosts/system/sitefile.bahman.ir/logs/error_log',
'/var/www/vhosts/system/siteapi.bahman.ir/logs/error_log',
'/var/log/httpd/error_log',
];
foreach ($logs as $l) {
  if (is_file($l)) echo "LOG $l\n".trim(shell_exec('tail -n 15 '.escapeshellarg($l)))."\n";
}
echo "DONE\n";
