<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs';
$cfgPath=$root.'/templates/bahman/config.php';
$htPath=$root.'/.htaccess';
$cfg=file_get_contents($cfgPath);
$ht=file_get_contents($htPath);

// Helper: parse multi-id CMS fields (company/product)
$helper = <<<'PHP'

/**
 * Normalize CMS multi-reference field (comma list / JSON / array) to string ids.
 *
 * @param mixed $raw
 * @return list<string>
 */
function bahman_cms_ref_ids($raw): array
{
    $ids = [];
    if (is_array($raw)) {
        foreach ($raw as $cid) {
            if (is_array($cid)) {
                $cid = $cid['value'] ?? $cid['id'] ?? $cid[0] ?? '';
            }
            $cid = trim((string) $cid);
            if ($cid !== '') {
                $ids[] = $cid;
            }
        }
    } else {
        $raw = trim((string) $raw);
        if ($raw !== '') {
            if (isset($raw[0]) && $raw[0] === '[') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $cid) {
                        if (is_array($cid)) {
                            $cid = $cid['value'] ?? $cid['id'] ?? $cid[0] ?? '';
                        }
                        $cid = trim((string) $cid);
                        if ($cid !== '') {
                            $ids[] = $cid;
                        }
                    }
                }
            }
            if ($ids === []) {
                $ids = array_values(array_filter(array_map('trim', explode(',', $raw)), static function ($v) {
                    return $v !== '';
                }));
            }
        }
    }
    return array_values(array_unique($ids));
}

/**
 * Public agencies API payload for other Bahman websites.
 *
 * Query params (optional): company, province, city, product, q
 *
 * @param array<string,mixed> $query
 * @return array<string,mixed>
 */
function bahman_cms_agencies_api_payload(array $query = []): array
{
    $companyFilter = trim((string) ($query['company'] ?? ''));
    $provinceFilter = trim((string) ($query['province'] ?? ''));
    $cityFilter = trim((string) ($query['city'] ?? ''));
    $productFilter = trim((string) ($query['product'] ?? ''));
    $keyword = trim((string) ($query['q'] ?? $query['keyword'] ?? ''));

    $cacheKey = 'bahman_agencies_api_v1_' . md5(json_encode([
        $companyFilter, $provinceFilter, $cityFilter, $productFilter, $keyword,
    ], JSON_UNESCAPED_UNICODE));
    $cacheFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/' . $cacheKey . '.cache';
    $ttl = 120;
    if (is_file($cacheFile) && (time() - (int) filemtime($cacheFile)) < $ttl) {
        $cached = @unserialize((string) file_get_contents($cacheFile));
        if (is_array($cached) && isset($cached['ok'])) {
            return $cached;
        }
    }

    $agency = new CmsContent();
    $agencyData = $agency
        ->setType('agency')
        ->setFields('title,code,city,province,statud,status,company,product,agency_details,scores,isqi_score')
        ->setCount(-1)
        ->execute();
    unset($agency);
    if (!is_array($agencyData)) {
        $agencyData = [];
    }

    $company = new CmsContent();
    $companyList = $company
        ->setType('company')
        ->setFields('title')
        ->setCount(-1)
        ->execute();
    unset($company);
    if (!is_array($companyList)) {
        $companyList = [];
    }
    $companyList = bahman_cms_sort_by_list_priority($companyList);
    $companyById = [];
    foreach ($companyList as $comp) {
        if (!is_array($comp)) {
            continue;
        }
        $key = (string) ($comp['id'] ?? '');
        if ($key === '') {
            continue;
        }
        $companyById[$key] = [
            'id' => (int) $key,
            'title' => trim((string) ($comp['values']['title'] ?? '')),
        ];
    }

    $usedCompanyIds = [];
    $agencies = [];
    foreach ($agencyData as $item) {
        if (!is_array($item)) {
            continue;
        }
        $values = is_array($item['values'] ?? null) ? $item['values'] : [];
        $statud = (string) ($values['statud'] ?? $values['status'] ?? '1');
        if ($statud === '0') {
            continue; // inactive/hidden
        }

        $title = trim((string) ($values['title'] ?? ''));
        if ($title === '') {
            continue;
        }

        $companyIds = bahman_cms_ref_ids($values['company'] ?? '');
        $productIds = bahman_cms_ref_ids($values['product'] ?? '');
        $province = trim((string) ($values['province'] ?? ''));
        $city = trim((string) ($values['city'] ?? ''));
        $code = trim((string) ($values['code'] ?? ''));

        if ($companyFilter !== '' && !in_array($companyFilter, $companyIds, true)) {
            continue;
        }
        if ($provinceFilter !== '' && bahman_normalize_place_name($province) !== bahman_normalize_place_name($provinceFilter)) {
            continue;
        }
        if ($cityFilter !== '' && bahman_normalize_place_name($city) !== bahman_normalize_place_name($cityFilter)) {
            continue;
        }
        if ($productFilter !== '' && !in_array($productFilter, $productIds, true)) {
            continue;
        }
        if ($keyword !== '') {
            $hay = $title . ' ' . $code;
            if (function_exists('mb_stripos')) {
                if (mb_stripos($hay, $keyword, 0, 'UTF-8') === false) {
                    continue;
                }
            } elseif (stripos($hay, $keyword) === false) {
                continue;
            }
        }

        foreach ($companyIds as $cid) {
            $usedCompanyIds[$cid] = true;
        }

        $detailsRaw = $values['agency_details'] ?? '';
        if (is_string($detailsRaw) && $detailsRaw !== '') {
            $decoded = json_decode($detailsRaw, true);
            $detailsRaw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($detailsRaw)) {
            $detailsRaw = [];
        }
        $details = [];
        foreach ($detailsRaw as $detail) {
            if (!is_array($detail)) {
                continue;
            }
            $status = $detail['status'] ?? 1;
            if (!($status === 1 || $status === '1' || $status === true)) {
                continue;
            }
            $dTitle = trim((string) ($detail['title'] ?? ''));
            $dValue = trim((string) ($detail['value'] ?? ''));
            if ($dTitle === '' && $dValue === '') {
                continue;
            }
            $details[] = [
                'title' => $dTitle,
                'value' => $dValue,
            ];
        }

        $scoresRaw = $values['scores'] ?? '';
        if (is_string($scoresRaw) && $scoresRaw !== '') {
            $decoded = json_decode($scoresRaw, true);
            $scoresRaw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($scoresRaw)) {
            $scoresRaw = [];
        }
        $scores = [];
        foreach ($scoresRaw as $score) {
            if (!is_array($score)) {
                continue;
            }
            $status = $score['status'] ?? 1;
            if (!($status === 1 || $status === '1' || $status === true)) {
                continue;
            }
            $sTitle = trim((string) ($score['title'] ?? ''));
            $sValue = trim((string) ($score['score'] ?? ''));
            if ($sTitle === '' || $sValue === '') {
                continue;
            }
            $scores[] = [
                'title' => $sTitle,
                'score' => $sValue,
            ];
        }

        $isqi = trim((string) ($values['isqi_score'] ?? ''));
        $companies = [];
        foreach ($companyIds as $cid) {
            if (isset($companyById[$cid])) {
                $companies[] = $companyById[$cid];
            } else {
                $companies[] = ['id' => (int) $cid, 'title' => ''];
            }
        }

        $agencies[] = [
            'id' => (int) ($item['id'] ?? 0),
            'title' => $title,
            'code' => $code,
            'province' => $province,
            'city' => $city,
            'status' => $statud,
            'company_ids' => array_map('intval', $companyIds),
            'companies' => $companies,
            'product_ids' => array_map('intval', $productIds),
            'details' => $details,
            'isqi_score' => $isqi,
            'scores' => $scores,
        ];
    }

    $companiesOut = [];
    foreach ($companyList as $comp) {
        $key = (string) ($comp['id'] ?? '');
        if ($key === '' || empty($usedCompanyIds[$key])) {
            continue;
        }
        if ($companyFilter !== '' && $key !== $companyFilter) {
            continue;
        }
        $companiesOut[] = [
            'id' => (int) $key,
            'title' => trim((string) ($comp['values']['title'] ?? '')),
        ];
    }

    $payload = [
        'ok' => true,
        'count' => count($agencies),
        'companies' => $companiesOut,
        'agencies' => $agencies,
        'filters' => array_filter([
            'company' => $companyFilter !== '' ? $companyFilter : null,
            'province' => $provinceFilter !== '' ? $provinceFilter : null,
            'city' => $cityFilter !== '' ? $cityFilter : null,
            'product' => $productFilter !== '' ? $productFilter : null,
            'q' => $keyword !== '' ? $keyword : null,
        ], static function ($v) {
            return $v !== null;
        }),
        'generated_at' => date('c'),
    ];

    @file_put_contents($cacheFile, serialize($payload), LOCK_EX);
    return $payload;
}

PHP;

if (strpos($cfg,'function bahman_cms_agencies_api_payload')!==false) {
  echo "HELPER_ALREADY\n";
} else {
  // insert before first "switch ($template" or near other helpers after bahman_cms_to_persian_digits
  $anchor='function bahman_cms_to_persian_digits(string $value): string';
  $p=strpos($cfg,$anchor);
  if ($p===false) {
    echo "HELPER_ANCHOR_MISS\n";
  } else {
    // find end of that function then insert after
    $end=strpos($cfg,"\n}\n",$p);
    if ($end===false) { echo "HELPER_END_MISS\n"; }
    else {
      $end+=3;
      $cfg=substr($cfg,0,$end).$helper.substr($cfg,$end);
      echo "HELPER_INSERTED\n";
    }
  }
}

// case agency_json before case agency or after product_menu_json
$case = <<<'PHP'
        case 'agency_json':
            // Public JSON API for agencies — usable from other Bahman sites.
            $bahmanSkipSharedNavQueries = true;
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: public, max-age=120');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
                http_response_code(204);
                exit;
            }
            $payload = bahman_cms_agencies_api_payload([
                'company' => $_GET['company'] ?? '',
                'province' => $_GET['province'] ?? '',
                'city' => $_GET['city'] ?? '',
                'product' => $_GET['product'] ?? '',
                'q' => $_GET['q'] ?? ($_GET['keyword'] ?? ''),
            ]);
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;

PHP;

if (strpos($cfg,"case 'agency_json':")!==false) {
  echo "CASE_ALREADY\n";
} else {
  $needle="        case 'agency':";
  if (strpos($cfg,$needle)===false) {
    echo "CASE_ANCHOR_MISS\n";
  } else {
    $cfg=str_replace($needle,$case.$needle,$cfg);
    echo "CASE_INSERTED\n";
  }
}

$tmp='/tmp/config.php.agencies_api';
file_put_contents($tmp,$cfg);
$phpBin='/opt/plesk/php/8.3/bin/php';
echo "lint=".sh(escapeshellarg($phpBin).' -l '.escapeshellarg($tmp))."\n";
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($cfgPath).' 2>&1');
echo "cfg_deployed\n";

// htaccess rewrite — avoid .json extension
$rule='    RewriteRule ^agencies-data/?$ index.php?include=agency_json [L,QSA]';
if (strpos($ht,'agencies-data')!==false) {
  echo "HT_ALREADY\n";
} else {
  $anchor='    RewriteRule ^product-menu-data/?$ index.php?include=product_menu_json [L,QSA]';
  if (strpos($ht,$anchor)!==false) {
    $ht=str_replace($anchor,$anchor."\n".$rule,$ht);
    $tmp='/tmp/htaccess.agencies_api';
    file_put_contents($tmp,$ht);
    sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($htPath).' 2>&1');
    echo "HT_PATCHED\n";
  } else {
    echo "HT_ANCHOR_MISS\n";
  }
}

// smoke tests
$urls=[
  'http://172.20.14.76/agencies-data',
  'http://172.20.14.76/agencies-data?company=741',
  'http://172.20.14.76/?include=agency_json&company=559',
];
foreach ($urls as $u) {
  $raw=sh('curl -si --max-time 40 -H "Host: bahman.ir" '.escapeshellarg($u));
  $parts=explode("\r\n\r\n",$raw,2);
  $hdr=$parts[0]??'';
  $body=$parts[1]??'';
  echo "URL $u\n";
  echo "  status_line=".strtok($hdr,"\n")."\n";
  if (preg_match('/Access-Control-Allow-Origin:\s*(.+)/i',$hdr,$m)) echo "  cors=".trim($m[1])."\n";
  $j=@json_decode($body,true);
  if (!is_array($j)) {
    echo "  NOT_JSON len=".strlen($body)." head=".substr(preg_replace('/\s+/',' ',$body),0,180)."\n";
  } else {
    echo "  ok=".json_encode($j['ok']??null)." count=".($j['count']??'?')." companies=".count($j['companies']??[])."\n";
    if (!empty($j['companies'])) {
      $names=array_map(static function($c){return ($c['id']??'').':'.($c['title']??'');}, array_slice($j['companies'],0,6));
      echo "  companies_sample=".json_encode($names, JSON_UNESCAPED_UNICODE)."\n";
    }
    if (!empty($j['agencies'][0])) {
      $a=$j['agencies'][0];
      echo "  first=".json_encode([
        'id'=>$a['id']??null,
        'title'=>$a['title']??null,
        'code'=>$a['code']??null,
        'city'=>$a['city']??null,
        'company_ids'=>$a['company_ids']??null,
        'details_count'=>count($a['details']??[]),
      ], JSON_UNESCAPED_UNICODE)."\n";
    }
  }
}
echo "DONE\n";
