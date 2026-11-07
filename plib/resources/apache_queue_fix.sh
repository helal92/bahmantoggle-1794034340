#!/bin/bash
echo START $(date) $(id) > /tmp/apache_queue_fix.log
sysctl -w net.core.somaxconn=8192 >> /tmp/apache_queue_fix.log 2>&1
sysctl -w net.ipv4.tcp_max_syn_backlog=16384 >> /tmp/apache_queue_fix.log 2>&1
MPM=/etc/apache2/mods-enabled/mpm_event.conf
cp -a "$MPM" /tmp/mpm_event.conf.bak.queue 2>>/tmp/apache_queue_fix.log
cat > "$MPM" <<'EOF'
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
apache2ctl configtest >> /tmp/apache_queue_fix.log 2>&1
systemctl reload apache2 >> /tmp/apache_queue_fix.log 2>&1
ss -lnt '( sport = :80 or sport = :443 )' >> /tmp/apache_queue_fix.log 2>&1
echo DONE $(date) >> /tmp/apache_queue_fix.log
