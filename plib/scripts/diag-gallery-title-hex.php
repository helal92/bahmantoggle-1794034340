<?php
$fm='/opt/psa/admin/bin/filemng';
function root($cmd){global $fm; return (string)shell_exec($fm.' root exec / bash -c '.escapeshellarg($cmd).' --allow-root 2>&1');}
$out = root("MYSQL_PWD=\$(cat /etc/psa/.psa.shadow) mysql -uadmin -D bmcars_cms -N -e \"SELECT v.value FROM cms_content_field_values v JOIN cms_content_type_fields f ON f.id=v.content_type_field_id WHERE v.content_id=4867 AND f.\\`key\\`='title';\" 2>&1");
$val = trim($out);
echo "raw_len=".strlen($val)."\n";
echo "hex=".bin2hex($val)."\n";
$expected = 'ویدیو';
echo "expected_hex=".bin2hex($expected)."\n";
echo "eq=".(($val===$expected)?'yes':'no')."\n";
echo "trim_eq=".((trim($val)===$expected)?'yes':'no')."\n";
echo "mb=".mb_strlen($val)."\n";
// compare char by char
$a=preg_split('//u',$val,-1,PREG_SPLIT_NO_EMPTY);
$b=preg_split('//u',$expected,-1,PREG_SPLIT_NO_EMPTY);
echo "chars_a=".json_encode($a, JSON_UNESCAPED_UNICODE)."\n";
echo "chars_b=".json_encode($b, JSON_UNESCAPED_UNICODE)."\n";
foreach ($a as $i=>$ch) {
  $bh=$b[$i]??'';
  echo "i=$i a=".bin2hex($ch)." b=".bin2hex($bh)." same=".(($ch===$bh)?'1':'0')."\n";
}
// also check image uuids are videos on sitefile
foreach (['ea8d13fd-db36-11f1-94e6-005056b972d8','9c06d09d-db3a-11f1-94e6-005056b972d8'] as $u) {
  $h=trim(shell_exec('curl -sI --max-time 10 -H "Host: sitefile.bahman.ir" "http://172.20.14.76/'.$u.'" | tr -d "\\r" | grep -iE "HTTP/|Content-Type|Content-Disposition|Content-Length"'));
  echo "file $u => $h\n";
}
echo "DONE\n";
