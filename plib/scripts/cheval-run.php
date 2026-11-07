<?php
$root='/var/www/vhosts/bahman.ir/httpdocs';
echo "FIND CACHE:\n";
echo shell_exec("find $root -type f \\( -iname '*home*' -o -iname '*cache*' \\) 2>/dev/null | head -80");
echo "\nFIND HTML size ~58078:\n";
echo shell_exec("find $root -type f -size 56k -o -size 57k 2>/dev/null | head -40");
echo "\nExact size:\n";
echo shell_exec("find $root -type f -printf '%s %p\\n' 2>/dev/null | awk '$1==58078 || ($1>57000 && $1<59000)' | head -40");
echo "\n_cache dir:\n";
echo shell_exec("ls -la $root/_cache 2>/dev/null | head -40");
echo shell_exec("ls -la $root/tmp 2>/dev/null | head -20");
echo shell_exec("ls -la /tmp/*home* 2>/dev/null | head -20");
echo shell_exec("ls -la /var/www/vhosts/bahman.ir/tmp 2>/dev/null | head -20");
