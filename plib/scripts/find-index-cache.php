<?php
function sh($c){return trim((string)shell_exec($c));}
$idx='/var/www/vhosts/bahman.ir/httpdocs/index.php';
$c=file_get_contents($idx);
// extract all bahman_*.html cache path constructions
if (preg_match_all('/\$bahman[A-Za-z]*\s*=\s*[^;]*bahman[^;]*\.html[^;]*;/',$c,$m)) {
  foreach ($m[0] as $x) echo "ASGN $x\n";
}
if (preg_match_all('/[\'\"][^\'\"]*bahman[^\'\"]*\.html[^\'\"]*[\'\"]/',$c,$m)) {
  foreach (array_unique($m[0]) as $x) echo "STR $x\n";
}
// show index cache related block
$p=strpos($c,"template === 'index'");
echo "ctx=\n".substr($c,max(0,$p-400),900)."\n---\n";

// find all html cache files on system related to bahman
echo "find=\n".sh('find /tmp /var/tmp /var/www/vhosts/bahman.ir -name "bahman*.html" 2>/dev/null')."\n";
echo "find2=\n".sh('find /tmp /var/tmp -name "*index*" 2>/dev/null | head -40')."\n";
echo "tmp=".sys_get_temp_dir()."\n";
echo "ls_tmp_bahman=\n".sh('ls -la '.escapeshellarg(sys_get_temp_dir()).'/bahman* 2>/dev/null | head -50')."\n";

// Maybe cache is under open_basedir different tmp for php-fpm
echo "fpm_tmp=\n".sh('grep -R "sys_temp_dir\|upload_tmp\|TMPDIR" /var/www/vhosts/system/bahman.ir/etc /var/www/vhosts/bahman.ir/etc 2>/dev/null | head -20')."\n";
echo "phpini=\n".sh('ls /var/www/vhosts/system/bahman.ir/etc/php* 2>/dev/null; grep -R "sys_temp_dir\|session.save_path" /var/www/vhosts/system/bahman.ir/etc 2>/dev/null | head')."\n";

// read from running php via a web hit that dumps cache path - or parse index for FAST_MODE cache file name
if (preg_match('/FAST_MODE[\s\S]{0,1200}bahmanPageCacheFile[\s\S]{0,400}/',$c,$m)) echo "FAST:\n".$m[0]."\n";
echo "DONE\n";
