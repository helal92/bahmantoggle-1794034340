<?php
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$base='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman/assets';
$files=[
  'brand-slider.js' =>  + base64.b64encode(js).decode() + r,
  'brand-slider.css' =>  + base64.b64encode(css).decode() + r,
];
foreach ($files as $name => $b64) {
  $path = "$base/$name";
  $tmp = "/tmp/_bahman_$name";
  file_put_contents($tmp, base64_decode($b64));
  echo "write $name bytes=".filesize($tmp)."
";
  // copy as domain user
  echo trim(shell_exec("$fm ".escapeshellarg($user)." cp ".escapeshellarg($tmp)." ".escapeshellarg($path)." 2>&1"))."
";
  @unlink($tmp);
  echo "mtime ".trim(shell_exec("stat -c '%y %s' ".escapeshellarg($path)))."
";
  echo "sign ".trim(shell_exec("grep -n 'translate3d' ".escapeshellarg($path)))."
";
}
// clear homepage HTML cache
$cacheGlob='/var/www/vhosts/bahman.ir/httpdocs/cache/*home*';
echo "cache=".trim(shell_exec("ls -la /var/www/vhosts/bahman.ir/httpdocs/cache/ 2>/dev/null | head -20"))."
";
echo trim(shell_exec("$fm root exec / bash -c 'rm -f /var/www/vhosts/bahman.ir/httpdocs/cache/*home* /var/www/vhosts/bahman.ir/httpdocs/cache/bahman_home* /tmp/bahman_home* 2>/dev/null; find /var/www/vhosts/bahman.ir/httpdocs -maxdepth 3 -name "*home*page*" -type f 2>/dev/null | head; find /tmp -name "bahman_home*" 2>/dev/null | head' --allow-root"))."
";
echo "verify=".trim(shell_exec("grep -n 'translate3d' $base/brand-slider.js"))."
";
echo "DONE
";
