<?php
function sh($c){return trim((string)shell_exec($c));}
echo "uid=".posix_geteuid()." user=".sh('whoami')."\n";
echo "schedule help:\n".sh('plesk bin schedule --help 2>&1 | head -50')."\n---\n";
echo "task help:\n".sh('plesk bin task --help 2>&1 | head -40')."\n---\n";
// try filemng via sudo wrapper
echo "filemng:\n".sh('ls -la /usr/local/psa/admin/bin/filemng /opt/psa/admin/bin/filemng 2>&1')."\n";
echo sh('/opt/psa/admin/bin/filemng --help 2>&1 | head -20')."\n";
// try plesk daily
echo "event_handler:\n".sh('plesk bin event_handler --help 2>&1 | head -40')."\n";
echo "DONE\n";
