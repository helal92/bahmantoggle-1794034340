<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';

// 1) Patch agency.tpl filter to use agency_companies
$tplPath=$root.'/agency.tpl';
$tpl=file_get_contents($tplPath);
$old = <<<'OLD'
                                                <select id="filter-company" name="company">
                                                    <option value="">همه شرکت‌ها</option>
                                                    {if isset($company) and !empty($company)}
                                                        {foreach $company as $comp}
                                                            <option value="{$comp.id}">{$comp.values.title|default: ''}</option>
                                                        {/foreach}
                                                    {/if}
                                                </select>
OLD;
$new = <<<'NEW'
                                                <select id="filter-company" name="company">
                                                    <option value="">همه شرکت‌ها</option>
                                                    {if isset($agency_companies) and !empty($agency_companies)}
                                                        {foreach $agency_companies as $comp}
                                                            <option value="{$comp.id}">{$comp.values.title|default: ''}</option>
                                                        {/foreach}
                                                    {/if}
                                                </select>
NEW;
if (strpos($tpl,'filter-company')!==false && strpos($tpl,'foreach $agency_companies as $comp}')!==false && preg_match('/filter-company[\s\S]{0,400}agency_companies/',$tpl)) {
  echo "TPL_ALREADY\n";
} elseif (strpos($tpl,$old)!==false) {
  $tpl=str_replace($old,$new,$tpl);
  $tmp='/tmp/agency.tpl.companyfilter';
  file_put_contents($tmp,$tpl);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tplPath).' 2>&1');
  echo "TPL_PATCHED\n";
} else {
  echo "TPL_BLOCK_MISS\n";
  if (preg_match('/filter-company[\s\S]{0,500}/',$tpl,$m)) echo $m[0]."\n";
}

// 2) Patch config.php:
// - harden company id extraction in agency case
// - rebuild agency_companies after global company sort so order = CMS list order
$cfgPath=$root.'/config.php';
$cfg=file_get_contents($cfgPath);

$oldParse = <<<'OLD'
                $companyIds = [];
                if (!empty($item['values']['company'])) {
                    $companyIds = array_filter(array_map('trim', explode(',', $item['values']['company'])));
                }
                if (empty($companyIds)) {
                    $companyIds = ['0'];
                }
OLD;

$newParse = <<<'NEW'
                $companyIds = [];
                $companyRaw = $item['values']['company'] ?? '';
                if (is_array($companyRaw)) {
                    foreach ($companyRaw as $cid) {
                        if (is_array($cid)) {
                            $cid = $cid['value'] ?? $cid['id'] ?? $cid[0] ?? '';
                        }
                        $cid = trim((string) $cid);
                        if ($cid !== '') {
                            $companyIds[] = $cid;
                        }
                    }
                } else {
                    $companyRaw = trim((string) $companyRaw);
                    if ($companyRaw !== '') {
                        // JSON list or comma-separated ids
                        if ($companyRaw[0] === '[') {
                            $decoded = json_decode($companyRaw, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $cid) {
                                    if (is_array($cid)) {
                                        $cid = $cid['value'] ?? $cid['id'] ?? $cid[0] ?? '';
                                    }
                                    $cid = trim((string) $cid);
                                    if ($cid !== '') {
                                        $companyIds[] = $cid;
                                    }
                                }
                            }
                        }
                        if ($companyIds === []) {
                            $companyIds = array_values(array_filter(array_map('trim', explode(',', $companyRaw)), static function ($v) {
                                return $v !== '';
                            }));
                        }
                    }
                }
                $companyIds = array_values(array_unique($companyIds));
                if ($companyIds === []) {
                    $companyIds = ['0'];
                }
NEW;

if (strpos($cfg,'JSON list or comma-separated ids')!==false) {
  echo "PARSE_ALREADY\n";
} elseif (strpos($cfg,$oldParse)!==false) {
  $cfg=str_replace($oldParse,$newParse,$cfg);
  echo "PARSE_PATCHED\n";
} else {
  echo "PARSE_MISS\n";
}

// Replace local agencyCompanies build to also sort, and add post-global rebuild
$oldBuild = <<<'OLD'
            $company = new CmsContent();
            $company
                ->setType('company')
                ->setFields("title");
            $company_list = $company->execute();
            unset($company);

            $agencyCompanies = [];
            foreach ($company_list as $comp) {
                if (isset($groupedAgencies[$comp['id']]) && !empty($groupedAgencies[$comp['id']])) {
                    $agencyCompanies[] = $comp;
                }
            }
OLD;

$newBuild = <<<'NEW'
            // Provisional list; final ordered list is rebuilt after global company sort below.
            $company = new CmsContent();
            $company
                ->setType('company')
                ->setFields("title")
                ->setCount(-1);
            $company_list = $company->execute();
            unset($company);
            if (!is_array($company_list)) {
                $company_list = [];
            }
            $company_list = bahman_cms_sort_by_list_priority($company_list);

            $agencyCompanies = [];
            foreach ($company_list as $comp) {
                $key = (string) ($comp['id'] ?? '');
                if ($key !== '' && !empty($groupedAgencies[$key])) {
                    $agencyCompanies[] = $comp;
                }
            }
NEW;

if (strpos($cfg,'Provisional list; final ordered list is rebuilt')!==false) {
  echo "BUILD_ALREADY\n";
} elseif (strpos($cfg,$oldBuild)!==false) {
  $cfg=str_replace($oldBuild,$newBuild,$cfg);
  echo "BUILD_PATCHED\n";
} else {
  echo "BUILD_MISS\n";
}

// After global assign company, rebuild agency_companies from sorted $company_list
$anchor = "    \$smarty->assign('company', \$company_menu);";
$inject = <<<'INJ'
    $smarty->assign('company', $company_menu);

    // Agency filter/tabs: only companies that have at least one agency, CMS list order.
    if (isset($template) && $template === 'agency' && isset($groupedAgencies) && is_array($groupedAgencies)) {
        $agencyCompanies = [];
        foreach ($company_list as $comp) {
            $key = (string) ($comp['id'] ?? '');
            if ($key !== '' && !empty($groupedAgencies[$key])) {
                $agencyCompanies[] = $comp;
            }
        }
        $smarty->assign('agency_companies', $agencyCompanies);
    }
INJ;

if (strpos($cfg,'Agency filter/tabs: only companies that have at least one agency')!==false) {
  echo "INJECT_ALREADY\n";
} elseif (strpos($cfg,$anchor)!==false) {
  $cfg=str_replace($anchor,$inject,$cfg);
  echo "INJECT_PATCHED\n";
} else {
  echo "INJECT_MISS\n";
}

$tmp='/tmp/config.php.agencyfilter';
file_put_contents($tmp,$cfg);
$php='/opt/plesk/php/8.3/bin/php';
echo "lint=".sh(escapeshellarg($php).' -l '.escapeshellarg($tmp))."\n";
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($cfgPath).' 2>&1');
echo "cfg_deployed\n";

// clear compiles
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*agency*') ?: [] as $f) @unlink($f);

$page=sh('curl -s --max-time 45 -H "Host: bahman.ir" "http://172.20.14.76/?include=agency&cf='.time().'"');
echo "page_len=".strlen($page)." fatal=".(stripos($page,'Fatal error')!==false?'1':'0')."\n";
if (preg_match('/id="filter-company"[^>]*>(.*?)<\/select>/s',$page,$m)) {
  preg_match_all('/<option value="([^"]*)">([^<]*)<\/option>/',$m[1],$opts,PREG_SET_ORDER);
  echo "filter_options=".count($opts)."\n";
  foreach ($opts as $o) echo "  [{$o[1]}] {$o[2]}\n";
}
// tab buttons count
preg_match_all('/class="tab-btn[^"]*" data-tab="company(\d+)"/',$page,$tm);
echo "tabs=".json_encode($tm[1])."\n";
echo "DONE\n";
