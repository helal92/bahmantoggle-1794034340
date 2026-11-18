<?php
$uuid = "47bf959c-e344-11f1-8a7c-005056b972d8";
$old = "c3f2460a-aeac-11f1-ad46-005056b972d8";
$roots = [
  "/var/www/vhosts/sitefile.bahman.ir/httpdocs",
  "/var/www/vhosts/bahman.ir/httpdocs/sitefile",
  "/var/www/vhosts/sitefile.bahman.ir",
];
foreach ($roots as $r) {
  echo "ROOT $r exists=".(is_dir($r)?"1":"0")."\n";
  if (!is_dir($r)) continue;
  echo trim((string)shell_exec("ls -la ".escapeshellarg($r)." 2>&1 | head -20"))."\n";
}
echo "\nFIND:\n";
echo trim((string)shell_exec("find /var/www/vhosts -maxdepth 4 -type d -iname '*sitefile*' 2>/dev/null | head -30"))."\n";
echo "\nUUID files:\n";
echo trim((string)shell_exec("find /var/www/vhosts -name '*47bf959c-e344-11f1-8a7c-005056b972d8*' 2>/dev/null | head -40"))."\n";
echo "\nOLD files:\n";
echo trim((string)shell_exec("find /var/www/vhosts -name '*c3f2460a-aeac-11f1-ad46-005056b972d8*' 2>/dev/null | head -40"))."\n";
