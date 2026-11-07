#!/bin/bash
exec > /tmp/speed_finish.log 2>&1
set -x
date
# ensure flag
printf 'on\n' > /tmp/FAST_MODE.flag
/usr/local/psa/admin/bin/filemng bmcars-api.bahman.ir_ghgap00458p cp /tmp/FAST_MODE.flag /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE || true

# confirm pools
grep -E 'pm\.(max_children|start|min_spare|max_spare)' /opt/plesk/php/7.4/etc/php-fpm.d/sitefile.bahman.ir.conf
grep -E 'pm\.(max_children|start|min_spare|max_spare)' /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null | head -20

# regenerate + reload fpm
plesk sbin phpinimng --generate-site sitefile.bahman.ir 2>&1 | tail -5
plesk sbin phpinimng --generate-site bahman.ir 2>&1 | tail -5
for pid in $(ps -eo pid,cmd | awk '/php-fpm: master/{print $1}'); do kill -USR2 "$pid" 2>/dev/null && echo USR2:$pid; done

# apache mpm if not yet
if ! grep -q 'MaxConnectionsPerChild  2500' /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null; then
  cp -a /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_event.conf.bak.speed2
  cat > /etc/apache2/mods-enabled/mpm_event.conf <<'EOF'
StartServers            3
MinSpareThreads         25
MaxSpareThreads         75
ThreadLimit             64
ThreadsPerChild         25
MaxRequestWorkers       200
MaxConnectionsPerChild  2500
EOF
fi
apache2ctl configtest 2>&1 | tail -3
systemctl reload apache2 2>&1 || true

sleep 2
echo "load=$(cat /proc/loadavg)"
echo "fpm_sitefile=$(ps -eo cmd | grep '[p]hp-fpm: pool sitefile' | wc -l)"
echo "fpm_bahman=$(ps -eo cmd | grep '[p]hp-fpm: pool bahman' | wc -l)"
echo "wrappers=$(ps -eo cmd | grep '[w]rapper' | wc -l)"

echo '--- timings ---'
curl -sk -o /dev/null -w "home %{time_starttransfer} %{http_code}\n" --max-time 15 -H 'Host: bahman.ir' https://172.20.14.76/
curl -sk -o /dev/null -w "css %{time_starttransfer} %{http_code}\n" --max-time 15 -H 'Host: bahman.ir' https://172.20.14.76/templates/bahman/assets/index.css
curl -sk -o /dev/null -w "sales %{time_starttransfer} %{http_code}\n" --max-time 15 -H 'Host: bahman.ir' https://172.20.14.76/sales
for i in 1 2 3 4 5 6; do
  curl -sk -o /dev/null -w "thumb$i %{time_starttransfer} %{http_code}\n" --max-time 15 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300'
done
# parallel burst of thumbs
echo '--- burst ---'
for i in $(seq 1 20); do
  curl -sk -o /dev/null -w "%{time_starttransfer}\n" --max-time 10 -H 'Host: sitefile.bahman.ir' 'https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300' &
done
wait
echo DONE
date
