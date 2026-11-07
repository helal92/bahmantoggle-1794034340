#!/bin/bash
exec > /tmp/apache_queue_fix.log 2>&1
set -x
date
id

sysctl -w net.core.somaxconn=8192 net.ipv4.tcp_max_syn_backlog=16384 || true

MPM=/etc/apache2/mods-enabled/mpm_event.conf
cp -a "$MPM" "$MPM.bak.queue.$(date +%s)"
cat > "$MPM" <<'EOF'
# Accept-queue fix: LB floods :80; raise workers + ListenBacklog
ServerLimit             16
StartServers            6
MinSpareThreads         75
MaxSpareThreads         200
ThreadLimit             64
ThreadsPerChild         25
MaxRequestWorkers       400
MaxConnectionsPerChild  2500
ListenBacklog           4096
EOF

# Also set ListenBacklog globally if supported
grep -q 'ListenBacklog' /etc/apache2/apache2.conf || echo 'ListenBacklog 4096' >> /etc/apache2/apache2.conf

apache2ctl configtest
systemctl reload apache2 || apache2ctl graceful || true
sleep 2
ss -lnt '( sport = :80 or sport = :443 )'
ss -ant state syn-recv | wc -l
ps -eo cmd | grep -E '[a]pache2' | wc -l
date
echo DONE
