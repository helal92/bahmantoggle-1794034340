<?php
echo "load=".trim(@file_get_contents('/proc/loadavg'))."\n";
echo "syn_recv=".trim(shell_exec("ss -ant state syn-recv 2>/dev/null | wc -l"))."\n";
echo "syn_by_port=\n".trim(shell_exec("ss -ant state syn-recv 2>/dev/null | awk 'NR>1{print $4}' | sed 's/.*://' | sort | uniq -c | sort -rn | head"))."\n";
echo "syn_by_src=\n".trim(shell_exec("ss -ant state syn-recv 2>/dev/null | awk 'NR>1{print $5}' | sed 's/:.*//' | sort | uniq -c | sort -rn | head"))."\n";
echo "estab=\n".trim(shell_exec("ss -ant state established '( sport = :443 or sport = :80 )' 2>/dev/null | wc -l"))."\n";
echo "listen=\n".trim(shell_exec("ss -lnt '( sport = :443 or sport = :80 or sport = :7081 or sport = :7080 )' 2>/dev/null"))."\n";
echo "somaxconn=".trim(@file_get_contents('/proc/sys/net/core/somaxconn'))."\n";
echo "tcp_max_syn_backlog=".trim(@file_get_contents('/proc/sys/net/ipv4/tcp_max_syn_backlog'))."\n";
echo "tcp_syncookies=".trim(@file_get_contents('/proc/sys/net/ipv4/tcp_syncookies'))."\n";
echo "apache_busy=\n".trim(shell_exec("apachectl fullstatus 2>/dev/null | head -40"))."\n";
echo "httpd_threads=".trim(shell_exec("ps -eo cmd | grep -E '[h]ttpd|[a]pache2' | wc -l"))."\n";
echo "mpm=\n".trim(shell_exec("httpd -V 2>/dev/null | grep -i mpm; apache2 -V 2>/dev/null | grep -i mpm"))."\n";
# Try raise backlog temporarily
echo shell_exec('sysctl -w net.core.somaxconn=4096 net.ipv4.tcp_max_syn_backlog=8192 2>&1');
# Check nginx in front?
echo "nginx=".trim(shell_exec('ps -eo cmd | grep "[n]ginx" | wc -l'))."\n";
echo "forti_hint clients on 10.18.8.2 established to 443: ".trim(shell_exec("ss -ant state established dst 10.18.8.2 or src 10.18.8.2 2>/dev/null | wc -l"))."\n";
# Sample ages of syn-recv
echo "syn_timer_sample=\n".trim(shell_exec("ss -antoe state syn-recv 2>/dev/null | head -15"))."\n";
# Can we accept more? restart not yet - just report MaxRequestWorkers
echo "apache_prefork=\n".trim(shell_exec("grep -R 'MaxRequestWorkers\\|ServerLimit\\|ListenBacklog\\|ThreadsPerChild\\|MaxClients' /etc/httpd /etc/apache2 /etc/httpd/conf.modules.d 2>/dev/null | head -40"))."\n";
echo "DONE\n";
