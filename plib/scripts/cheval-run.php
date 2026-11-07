<?php
$bh='/var/www/vhosts/bahman.ir/httpdocs';
foreach (['home-product-slider.js','brand-slider.js','product-menu-lazy.js'] as $f) {
  echo "===== $f =====\n";
  echo file_get_contents("$bh/templates/bahman/assets/$f");
  echo "\n\n";
}
// how many products in menu json
echo shell_exec("curl -sk --max-time 30 -H 'Host: bahman.ir' 'https://172.20.14.76/product-menu-data' -o /tmp/pmd.json -w 'menu %{http_code} %{time_total} size=%{size_download}\\n'");
echo shell_exec("python3 - <<'PY'\nimport json\nd=json.load(open('/tmp/pmd.json'))\ncats=d.get('categories') or []\nn=0\nfor c in cats:\n  ps=c.get('products') or []\n  n+=len(ps)\n  print(c.get('title'), len(ps))\nprint('TOTAL_PRODUCTS',n)\nPY");
