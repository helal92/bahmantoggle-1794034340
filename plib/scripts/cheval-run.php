<?php
echo "syn before=".trim(shell_exec("ss -tn state syn-recv | wc -l"))."\n";
echo "nginx -R:\n".shell_exec('/usr/local/psa/admin/bin/nginx_control -R 2>&1');
echo "nginx -r:\n".shell_exec('/usr/local/psa/admin/bin/nginx_control -r 2>&1');
echo "httpd reload:\n".shell_exec('/usr/local/psa/admin/bin/httpdmng --reload 2>&1');
echo "plesk service nginx restart:\n".shell_exec('plesk bin service --restart nginx 2>&1 | head -10');
echo "plesk service web restart:\n".shell_exec('plesk bin service --restart web 2>&1 | head -10');
sleep(3);
echo "syn after=".trim(shell_exec("ss -tn state syn-recv | wc -l"))." load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "apache: ".trim(shell_exec("pgrep -c apache2"))." nginx: ".trim(shell_exec("pgrep -c nginx"))."\n";
foreach (['a9b88900-aead-11f1-ad46-005056b972d8','61491e0c-bfc8-11f1-9600-005056b972d8'] as $id) {
  echo shell_exec("curl -sk --max-time 8 -o /dev/null -w '$id %{http_code} %{time_total}\\n' -H 'Host: sitefile.bahman.ir' -H 'Range: bytes=0-1023' 'https://172.20.14.76/v/$id.mp4'");
}
echo shell_exec("curl -sk --max-time 8 -o /dev/null -w 'home %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/'");
echo shell_exec("curl -sk --max-time 8 -o /dev/null -w 'css %{http_code} %{time_total}\\n' -H 'Host: bahman.ir' 'https://172.20.14.76/templates/bahman/assets/index.css'");
echo "DONE\n";
