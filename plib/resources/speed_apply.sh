#!/bin/bash
exec > /tmp/speed_apply.log 2>&1
set -x
date
id

# 1) Kill hung Plesk wrapper probes (CPU waste)
pkill -f '/usr/local/psa/admin/sbin/wrapper' 2>/dev/null || true

# 2) Ensure FAST_MODE flag
FM_USER='bmcars-api.bahman.ir_ghgap00458p'
FM='/usr/local/psa/admin/bin/filemng'
FLAG='/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE'
echo "on $(date -Iseconds)" > /tmp/FAST_MODE.flag
$FM "$FM_USER" cp /tmp/FAST_MODE.flag "$FLAG" || cp /tmp/FAST_MODE.flag "$FLAG"

# 3) Deploy optimized file.php
if [ -f /tmp/file.php.speed ]; then
  $FM "$FM_USER" cp /tmp/file.php.speed /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php \
    || cp /tmp/file.php.speed /var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/file.php
fi

# 4) Cap sitefile PHP-FPM (was 250 children — thrashing)
cat > /tmp/sitefile_fpm_settings.ini <<'EOF'
pm.max_children = 48
pm.start_servers = 8
pm.min_spare_servers = 4
pm.max_spare_servers = 16
pm.max_requests = 500
memory_limit = 128M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 60000
opcache.revalidate_freq = 60
EOF
plesk bin site --update-php-settings sitefile.bahman.ir -settings /tmp/sitefile_fpm_settings.ini

# 5) Cap bahman.ir PHP-FPM if present / oversized
cat > /tmp/bahman_fpm_settings.ini <<'EOF'
pm.max_children = 40
pm.start_servers = 6
pm.min_spare_servers = 3
pm.max_spare_servers = 12
pm.max_requests = 500
opcache.enable = 1
EOF
plesk bin site --update-php-settings bahman.ir -settings /tmp/bahman_fpm_settings.ini || true

# 6) Apply php ini generation
plesk sbin phpinimng --generate-site sitefile.bahman.ir 2>&1 | tail -20
plesk sbin phpinimng --generate-site bahman.ir 2>&1 | tail -20

# 7) Soft-reload PHP-FPM masters
for pid in $(ps -eo pid,cmd | awk '/php-fpm: master/{print $1}'); do
  kill -USR2 "$pid" 2>/dev/null && echo USR2:$pid
done

# 8) Apache: keep workers modest (avoid queue pile-up with capped FPM)
if [ -w /etc/apache2/mods-enabled/mpm_event.conf ]; then
  cp -a /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_event.conf.bak.speed
  cat > /etc/apache2/mods-enabled/mpm_event.conf <<'EOF'
# Tuned for bahman load: fewer idle threads, enough for capped PHP-FPM
StartServers            3
MinSpareThreads         25
MaxSpareThreads         75
ThreadLimit             64
ThreadsPerChild         25
MaxRequestWorkers       200
MaxConnectionsPerChild  2500
EOF
  apache2ctl configtest 2>&1 | tail -5
  systemctl reload apache2 2>&1 || apache2ctl graceful 2>&1 || true
fi

# 9) Confirm pool values
echo '--- sitefile pool ---'
grep -E 'pm\.|memory_limit|opcache.enable' /opt/plesk/php/7.4/etc/php-fpm.d/sitefile.bahman.ir.conf 2>/dev/null | head -30
echo '--- bahman pool ---'
grep -E 'pm\.|memory_limit' /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null | head -30

sleep 2
echo "load=$(cat /proc/loadavg)"
echo "fpm_sitefile=$(ps -eo cmd | grep '[p]hp-fpm: pool sitefile' | wc -l)"
echo "fpm_bahman=$(ps -eo cmd | grep '[p]hp-fpm: pool bahman' | wc -l)"
echo "wrappers=$(ps -eo cmd | grep '[w]rapper' | wc -l)"
echo "fast=$( [ -f "$FLAG" ] && echo ON || echo OFF )"

# 10) Local timings
curl -sk -o /dev/null -w "home ttfb:%{time_starttransfer} code:%{http_code}\n" --max-time 15 -H 'Host: bahman.ir' https://172.20.14.76/
curl -sk -o /dev/null -w "css ttfb:%{time_starttransfer} code:%{http_code}\n" --max-time 15 -H 'Host: bahman.ir' https://172.20.14.76/templates/bahman/assets/index.css
for i in 1 2 3 4 5; do
  curl -sk -o /dev/null -w "thumb$i ttfb:%{time_starttransfer} code:%{http_code}\n" --max-time 15 -H 'Host: sitefile.bahman.ir' \
    'https://172.20.14.76/thumb/9ae23ac7-4e8a-11f1-9b58-005056b972d8/300'
done
date
echo DONE
