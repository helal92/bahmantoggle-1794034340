#!/bin/bash
exec > /tmp/log_trim_warm.log 2>&1
set -x
date
id
# Truncate huge access/error logs (keep inode for apache)
for f in \
  /var/www/vhosts/system/bahman.ir/logs/access_ssl_log \
  /var/www/vhosts/system/bahman.ir/logs/access_log \
  /var/www/vhosts/system/bahman.ir/logs/error_log \
  /var/www/vhosts/system/sitefile.bahman.ir/logs/access_ssl_log \
  /var/www/vhosts/system/sitefile.bahman.ir/logs/access_log \
  /var/www/vhosts/system/sitefile.bahman.ir/logs/error_log
 do
  if [ -f "$f" ]; then
    ls -lh "$f"
    : > "$f" && echo truncated "$f" || echo fail "$f"
  fi
 done
# Also compressed old logs if present
find /var/www/vhosts/system/bahman.ir/logs /var/www/vhosts/system/sitefile.bahman.ir/logs -type f \( -name '*.gz' -o -name '*log.*' \) -size +50M -print -delete 2>/dev/null | head -40
df -h /var
# Warm homepage a few times
for i in 1 2 3; do
  curl -sk --max-time 40 -o /dev/null -w "warm$i ttfb=%{time_starttransfer} total=%{time_total} code=%{http_code}\n" -H 'Host: bahman.ir' "https://172.20.14.76/"
done
ls -la /tmp/bahman_home* 2>/dev/null || true
echo DONE
