<?php
$fm='/usr/local/psa/admin/bin/filemng';
$bhUser=trim((string)shell_exec("stat -c '%U' /var/www/vhosts/bahman.ir/httpdocs/index.php"));
$bh='/var/www/vhosts/bahman.ir/httpdocs';

echo "=== header CSS/JS includes ===\n";
foreach (['includes/header_include.tpl','includes/footer_include.tpl','index.tpl'] as $rel) {
  $f="$bh/templates/bahman/$rel";
  echo "-- $rel --\n";
  $lines=file($f);
  foreach ($lines as $i=>$line) {
    if (preg_match('/stylesheet|\.css|script src|modulepreload|preconnect|data-src|video/i',$line)) {
      echo ($i+1).': '.trim($line)."\n";
    }
  }
}

echo "\n=== brotli / nginx modules ===\n";
echo shell_exec('ls -la /etc/nginx/modules.conf.d/ 2>&1');
echo shell_exec('ls -la /usr/share/nginx/nginx/modules/ 2>&1 | head -20');
echo shell_exec('cat /etc/nginx/modules.conf.d/brotli.conf 2>&1');

echo "\n=== http2? ===\n";
echo shell_exec("curl -skI --http2 --max-time 10 -H 'Host: bahman.ir' 'https://172.20.14.76/' 2>&1 | head -15");
echo shell_exec("curl -skI --http1.1 --max-time 10 -H 'Host: bahman.ir' 'https://172.20.14.76/' 2>&1 | head -10");

echo "\n=== apache mpm + protocols ===\n";
echo shell_exec("grep -RniE 'Protocols|Http2|MaxRequestWorkers|ServerLimit|ThreadsPerChild|ListenBacklog' /etc/apache2 /etc/httpd 2>/dev/null | head -40");

echo "\n=== can psaadm edit brotli? ===\n";
echo shell_exec('id; ls -la /etc/nginx/modules.conf.d/brotli.conf');
