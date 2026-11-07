<?php
$u='https://sitefile.bahman.ir/thumb/b2841745-6948-11f1-9b58-005056b972d8/70';
echo "ext=".trim(shell_exec('curl -sk -o /dev/null -w "%{http_code} %{size_download} %{time_starttransfer}" --max-time 10 -H "User-Agent: Mozilla/5.0" '.escapeshellarg($u)))."\n";
echo "local=".trim(shell_exec('curl -s -o /dev/null -w "%{http_code} %{size_download}" --max-time 10 -H "Host: sitefile.bahman.ir" http://172.20.14.76/thumb/b2841745-6948-11f1-9b58-005056b972d8/70'))."\n";
echo "js=".trim(shell_exec('ls -la /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/brand-slider.js 2>&1'))."\n";
echo "head=".trim(shell_exec('sed -n "50,60p" /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/brand-slider.js'))."\n";
echo "indexjs_import=".trim(shell_exec('grep -n brand-slider /var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets/index.js 2>&1 | head'))."\n";
echo "DONE\n";
