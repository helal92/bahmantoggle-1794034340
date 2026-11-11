<?php
function sh($c){return trim((string)shell_exec($c));}
$page=sh('curl -s --max-time 40 -H "Host: bahman.ir" "http://172.20.14.76/?include=agency&v='.time().'"');
preg_match('/id="filter-product"[^>]*>(.*?)<\/select>/s',$page,$m);
preg_match_all('/<option value="([^"]*)"(?: data-company="([^"]*)")?>([^<]*)<\/option>/',$m[1],$opts,PREG_SET_ORDER);
$by=[]; $empty=0;
foreach ($opts as $o) {
  if ($o[1]==='') continue;
  $cid=$o[2] ?? '';
  if ($cid==='') { $empty++; continue; }
  foreach (explode(',',$cid) as $id) {
    $id=trim($id);
    if ($id==='') continue;
    $by[$id]=($by[$id]??0)+1;
  }
}
ksort($by, SORT_NUMERIC);
echo "products_with_company_ids=".json_encode($by)."\n";
echo "products_without_company=$empty\n";
// company filter ids
preg_match('/id="filter-company"[^>]*>(.*?)<\/select>/s',$page,$m);
preg_match_all('/<option value="([^"]+)">([^<]*)<\/option>/',$m[1],$copts,PREG_SET_ORDER);
echo "companies:\n";
foreach ($copts as $c) {
  $id=$c[1];
  echo "  {$id} {$c[2]} products=".($by[$id]??0)."\n";
}
echo "DONE\n";
