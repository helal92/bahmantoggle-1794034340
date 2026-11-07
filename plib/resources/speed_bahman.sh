#!/bin/bash
exec > /tmp/speed_bahman.log 2>&1
set -x
date
cat > /tmp/bahman_fpm_settings.ini <<'EOF'
pm.max_children = 36
pm.start_servers = 6
pm.min_spare_servers = 3
pm.max_spare_servers = 12
pm.max_requests = 500
opcache.enable = 1
EOF
plesk bin site --update-php-settings bahman.ir -settings /tmp/bahman_fpm_settings.ini
plesk sbin phpinimng --generate-site bahman.ir 2>&1 | tail -10
# show which php version bahman uses
plesk bin domain --info bahman.ir 2>&1 | grep -i php | head -10
ls -la /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf
grep -E 'pm\.(max_children|start|min_spare|max_spare)' /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf
for pid in $(ps -eo pid,cmd | awk '/php-fpm: master/{print $1}'); do kill -USR2 "$pid" 2>/dev/null && echo USR2:$pid; done
pkill -f '/usr/local/psa/admin/sbin/wrapper' 2>/dev/null || true
sleep 2
# warm caches
for i in 1 2 3; do
  curl -sk -o /dev/null -w "home$i %{time_starttransfer} %{http_code}\n" --max-time 20 -H 'Host: bahman.ir' https://172.20.14.76/
done
curl -sk -o /dev/null -w "sales %{time_starttransfer} %{http_code}\n" --max-time 15 -H 'Host: bahman.ir' https://172.20.14.76/sales
echo "fpm_bahman=$(ps -eo cmd | grep '[p]hp-fpm: pool bahman' | wc -l)"
echo "fpm_sitefile=$(ps -eo cmd | grep '[p]hp-fpm: pool sitefile' | wc -l)"
echo "load=$(cat /proc/loadavg)"
echo DONE
