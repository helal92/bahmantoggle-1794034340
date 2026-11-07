#!/bin/bash
exec > /tmp/speed_bahman.log 2>&1
chmod a+r /tmp/speed_bahman.log 2>/dev/null || true
set -x
date
CONF=$(ls /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf 2>/dev/null | head -1)
echo "CONF=$CONF"
if [ -n "$CONF" ]; then
  cp -a "$CONF" "$CONF.bak.speed"
  sed -i 's/^pm.max_children = .*/pm.max_children = 36/' "$CONF"
  sed -i 's/^pm.start_servers = .*/pm.start_servers = 6/' "$CONF"
  sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 3/' "$CONF"
  sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 12/' "$CONF"
  grep -E 'pm\.(max_children|start|min_spare|max_spare)' "$CONF"
fi
# also via plesk so panel doesn't fight us
cat > /tmp/bahman_fpm_settings.ini <<'EOF'
pm.max_children = 36
pm.start_servers = 6
pm.min_spare_servers = 3
pm.max_spare_servers = 12
pm.max_requests = 500
EOF
plesk bin site --update-php-settings bahman.ir -settings /tmp/bahman_fpm_settings.ini 2>&1
plesk sbin phpinimng --generate-site bahman.ir 2>&1 | tail -5
# re-apply sed if plesk overwrote with higher
if [ -n "$CONF" ]; then
  sed -i 's/^pm.max_children = .*/pm.max_children = 36/' "$CONF"
  sed -i 's/^pm.start_servers = .*/pm.start_servers = 6/' "$CONF"
  sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 3/' "$CONF"
  sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 12/' "$CONF"
fi
for pid in $(ps -eo pid,cmd | awk '/php-fpm: master/{print $1}'); do kill -USR2 "$pid" 2>/dev/null && echo USR2:$pid; done
sleep 2
grep -E 'pm\.(max_children|start|min_spare|max_spare)' /opt/plesk/php/*/etc/php-fpm.d/bahman.ir.conf
echo "fpm_bahman=$(ps -eo cmd | grep '[p]hp-fpm: pool bahman' | wc -l)"
echo "load=$(cat /proc/loadavg)"
chmod a+r /tmp/speed_bahman.log
echo DONE
