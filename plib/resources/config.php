<?php

namespace PHPMaker2020\adoura_base_api;

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);
$include = '';

// Cache-bust CSS/JS: bumps whenever any asset file changes (no manual Ctrl+F5).
$bahmanAssetsDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets';
$bahmanAssetVersion = 0;
if (is_dir($bahmanAssetsDir)) {
    foreach (array_merge(
        glob($bahmanAssetsDir . DIRECTORY_SEPARATOR . '*.css') ?: [],
        glob($bahmanAssetsDir . DIRECTORY_SEPARATOR . '*.js') ?: []
    ) as $bahmanAssetFile) {
        $mtime = @filemtime($bahmanAssetFile);
        if ($mtime !== false && $mtime > $bahmanAssetVersion) {
            $bahmanAssetVersion = $mtime;
        }
    }
}
$smarty->assign('template_version', 'v' . ($bahmanAssetVersion > 0 ? $bahmanAssetVersion : time()));
unset($bahmanAssetsDir, $bahmanAssetVersion, $bahmanAssetFile, $mtime);

/**
 * Plain-text excerpt for meta description (strip HTML, collapse spaces, truncate).
 */
function bahman_cms_meta_excerpt($raw, int $maxLen = 160): string
{
    $text = html_entity_decode(strip_tags((string) $raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? '';
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text) <= $maxLen) {
        return $text;
    }
    $cut = mb_substr($text, 0, $maxLen - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > (int) ($maxLen * 0.6)) {
        $cut = mb_substr($cut, 0, $space);
    }
    return rtrim($cut, " \t\n\r\0\x0B،,.;:") . '…';
}

/**
 * Absolute file URL for Open Graph image (CMS attachment path or full URL).
 */
function bahman_cms_absolute_media_url(?string $path, string $fileUrl, string $size = '1200'): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $fileUrl = rtrim($fileUrl, '/');
    $path = ltrim($path, '/');
    if ($size !== '') {
        return $fileUrl . '/thumb/' . $path . '/' . $size;
    }
    return $fileUrl . '/' . $path;
}

/**
 * Aparat watch URL → embed iframe src (empty if not parseable).
 */
function bahman_cms_aparat_embed_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (!preg_match('#aparat\.com/(?:v/|video/video/embed/videohash/)([A-Za-z0-9]+)#i', $url, $m)) {
        return '';
    }
    return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
}

/**
 * Playback URL for a CMS video UUID.
 * Prefer /media/{uuid}.mp4 (iOS-friendly suffix); that endpoint stream-proxies
 * or 302-redirects to sitefile without downloading the whole file in PHP.
 */
function bahman_cms_video_stream_url(?string $uuid): string
{
    $uuid = trim((string) $uuid);
    if (!preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
        return '';
    }
    $base = defined('SITE_URL') ? rtrim((string) SITE_URL, '/') : '';
    if ($base !== '') {
        return $base . '/media/' . strtolower($uuid) . '.mp4';
    }
    if (defined('FILE_URL') && FILE_URL !== '') {
        return rtrim((string) FILE_URL, '/') . '/' . $uuid;
    }
    return '';
}

/**
 * Sort CMS rows like admin list: pin → priority → publish/create date → id.
 *
 * @param list<array> $items
 * @return list<array>
 */
function bahman_cms_sort_by_list_priority(array $items): array
{
    usort($items, static function (array $a, array $b): int {
        $pinA = (int) ($a['is_pin'] ?? 0) === 1 ? 1 : 0;
        $pinB = (int) ($b['is_pin'] ?? 0) === 1 ? 1 : 0;
        if ($pinA !== $pinB) {
            return $pinB <=> $pinA;
        }

        $pa = isset($a['priority']) && $a['priority'] !== '' && $a['priority'] !== null
            ? (int) $a['priority']
            : null;
        $pb = isset($b['priority']) && $b['priority'] !== '' && $b['priority'] !== null
            ? (int) $b['priority']
            : null;

        if ($pa !== null && $pb !== null && $pa !== $pb) {
            return $pa <=> $pb;
        }
        if ($pa !== null && $pb === null) {
            return -1;
        }
        if ($pa === null && $pb !== null) {
            return 1;
        }

        $dateA = strtotime((string) ($a['date'] ?? $a['c_date'] ?? '')) ?: 0;
        $dateB = strtotime((string) ($b['date'] ?? $b['c_date'] ?? '')) ?: 0;
        if ($dateA !== $dateB) {
            return $dateB <=> $dateA;
        }

        return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
    });

    return array_values($items);
}

/**
 * Convert Persian / Arabic-Indic digits to ASCII 0-9.
 */
function bahman_cms_normalize_digits(string $value): string
{
    static $map = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    return strtr($value, $map);
}

/**
 * Normalize Persian place names for matching (آ/ا، ی/ي، ک/ك، فاصله، اعراب).
 */
function bahman_normalize_place_name(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = bahman_cms_normalize_digits($value);
    $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $value) ?? $value;
    $value = str_replace(["\u{0640}", "\u{200C}", "\u{200D}", "\u{FEFF}"], '', $value);
    $value = strtr($value, [
        'آ' => 'ا',
        'أ' => 'ا',
        'إ' => 'ا',
        'ٱ' => 'ا',
        'ي' => 'ی',
        'ى' => 'ی',
        'ك' => 'ک',
        'ة' => 'ه',
    ]);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

    return mb_strtolower(trim($value), 'UTF-8');
}

/**
 * Pick the best display label among spelling variants of a place name.
 *
 * @param array<string, int> $titleCounts
 */
function bahman_pick_place_label(array $titleCounts): string
{
    if ($titleCounts === []) {
        return '';
    }

    $best = '';
    $bestCount = -1;
    foreach ($titleCounts as $title => $count) {
        $count = (int) $count;
        if ($count > $bestCount) {
            $best = (string) $title;
            $bestCount = $count;
            continue;
        }
        if ($count === $bestCount) {
            $hasAlefMadda = mb_strpos((string) $title, 'آ', 0, 'UTF-8') !== false;
            $bestHas = mb_strpos($best, 'آ', 0, 'UTF-8') !== false;
            if ($hasAlefMadda && !$bestHas) {
                $best = (string) $title;
            }
        }
    }

    return $best;
}

/**
 * Convert ASCII digits to Persian digits for display.
 */
function bahman_cms_to_persian_digits(string $value): string
{
    if (function_exists('tr_num')) {
        return (string) tr_num($value, 'fa');
    }

    static $map = [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ];

    return strtr($value, $map);
}

/**
 * Parse directive start_date (Jalali YYYY/MM/DD or Gregorian) to unix timestamp.
 * Accepts Persian/Arabic digits in the date string (sort only — display stays Persian).
 */
function bahman_cms_directive_start_date_ts(array $item): int
{
    $raw = bahman_cms_normalize_digits(trim((string) ($item['values']['start_date'] ?? '')));
    if ($raw === '') {
        return 0;
    }

    // Jalali YYYY/MM/DD or YYYY-MM-DD (optionally with time)
    if (preg_match('/^(1[3-5]\d{2})[\/\-](\d{1,2})[\/\-](\d{1,2})/', $raw, $m)) {
        $jalali = $m[1] . '/' . (int) $m[2] . '/' . (int) $m[3];
        $converted = j2g($jalali);
        $ts = strtotime((string) $converted);
        return $ts !== false ? $ts : 0;
    }

    $ts = strtotime($raw);
    return $ts !== false ? $ts : 0;
}

/**
 * Display directive start_date without time, always with Persian digits.
 */
function bahman_cms_directive_start_date_label($raw): string
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }

    $ascii = bahman_cms_normalize_digits($raw);

    if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/u', $ascii, $match)) {
        $label = sprintf('%04d/%02d/%02d', (int) $match[1], (int) $match[2], (int) $match[3]);
        return bahman_cms_to_persian_digits($label);
    }

    $ts = strtotime($ascii);
    if ($ts !== false) {
        return bahman_cms_to_persian_digits(date('Y-m-d', $ts));
    }

    $noTime = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?$/', '', $ascii);
    return bahman_cms_to_persian_digits((string) $noTime);
}

/**
 * Lightweight check: any published directive with status=فعال.
 * Cached briefly so the header flag does not hit the DB on every request.
 */
function bahman_has_active_sale(int $cacheSeconds = 120): bool
{
    static $memo = null;
    if ($memo !== null) {
        return $memo;
    }

    $cacheFile = sys_get_temp_dir() . '/cms_cache/bahman_has_active_sale.cache';
    if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheSeconds)) {
        $cached = @unserialize((string) file_get_contents($cacheFile));
        if (is_bool($cached)) {
            return $memo = $cached;
        }
    }

    $found = ExecuteScalar("
        SELECT 1
        FROM cms_contents c
        INNER JOIN cms_content_types t
            ON t.id = c.content_type_id
           AND t.`key` = 'directive'
        INNER JOIN cms_content_field_values v
            ON v.content_id = c.id
           AND v.status = '1'
        INNER JOIN cms_content_type_fields f
            ON f.id = v.content_type_field_id
           AND f.`key` = 'status'
        WHERE c.status = '1'
          AND v.value = 'فعال'
        LIMIT 1
    ");

    $memo = !empty($found);
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    @file_put_contents($cacheFile, serialize($memo));

    return $memo;
}

/**
 * Lightweight check: company has at least one published product via product.company.
 * Cached per company id briefly to keep brand pages cheap.
 */
function bahman_company_has_products(int $companyId, int $cacheSeconds = 120): bool
{
    if ($companyId <= 0) {
        return false;
    }

    static $memo = [];
    if (array_key_exists($companyId, $memo)) {
        return $memo[$companyId];
    }

    $cacheFile = sys_get_temp_dir() . '/cms_cache/bahman_company_has_products_' . $companyId . '.cache';
    if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheSeconds)) {
        $cached = @unserialize((string) file_get_contents($cacheFile));
        if (is_bool($cached)) {
            return $memo[$companyId] = $cached;
        }
    }

    $companyKey = remove_xss((string) $companyId);
    $found = ExecuteScalar("
        SELECT 1
        FROM cms_contents c
        INNER JOIN cms_content_types t
            ON t.id = c.content_type_id
           AND t.`key` = 'product'
        INNER JOIN cms_content_field_values v
            ON v.content_id = c.id
           AND v.status = '1'
        INNER JOIN cms_content_type_fields f
            ON f.id = v.content_type_field_id
           AND f.`key` = 'company'
        WHERE c.status = '1'
          AND (
                v.value = '{$companyKey}'
             OR FIND_IN_SET('{$companyKey}', v.value)
          )
        LIMIT 1
    ");

    $has = !empty($found);
    $memo[$companyId] = $has;
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    @file_put_contents($cacheFile, serialize($has));

    return $has;
}

/**
 * Resolve a display title from CMS field values (list_tree / nested items).
 */
function bahman_cms_item_title(array $item): string
{
    $values = is_array($item['values'] ?? null) ? $item['values'] : [];
    foreach (['title', 'group_title', 'grouping_title', 'name'] as $key) {
        $title = trim((string) ($values[$key] ?? ''));
        if ($title !== '') {
            return $title;
        }
    }
    return '';
}

/**
 * Normalize a CMS link item (child type `link` or JSON link row) to title/href/attachment.
 */
function bahman_cms_parse_link_item(array $item, string $fileUrl = ''): ?array
{
    $values = is_array($item['values'] ?? null) ? $item['values'] : $item;
    if (!is_array($values)) {
        return null;
    }
    if ((int) ($values['status'] ?? 1) !== 1) {
        return null;
    }

    $title = trim((string) ($values['title'] ?? ''));
    if ($title === '') {
        return null;
    }

    $attachment = trim((string) ($values['attachment'] ?? $values['attachemnt'] ?? $values['file'] ?? ''));
    $href = trim((string) ($values['href'] ?? $values['url'] ?? $values['link'] ?? ''));

    if ($attachment !== '' && $fileUrl !== '') {
        $href = rtrim($fileUrl, '/') . '/' . ltrim($attachment, '/');
    }

    // Keep title-only rows so a single incomplete link still opens the panel.
    return [
        'title' => $title,
        'href' => $href,
        'attachment' => $attachment,
        'status' => 1,
    ];
}

/**
 * Collect active links from child type `link` and/or JSON field `link` on an item.
 */
function bahman_cms_collect_links(array $item, array $childGroups, string $fileUrl = ''): array
{
    $links = [];

    foreach ($childGroups as $typeKey => $children) {
        if (!is_array($children)) {
            continue;
        }
        // Nested link content type, or any type whose values look like links
        foreach ($children as $child) {
            if (!is_array($child)) {
                continue;
            }
            if (
                $typeKey === 'link'
                || $typeKey === 'shortlink'
                || isset($child['values']['href'])
                || isset($child['values']['attachment'])
                || isset($child['values']['url'])
                || isset($child['values']['link'])
            ) {
                $parsed = bahman_cms_parse_link_item($child, $fileUrl);
                if ($parsed !== null) {
                    $links[] = $parsed;
                }
            }
        }
    }

    $linkRaw = $item['values']['link'] ?? '';
    $jsonLinks = is_array($linkRaw) ? $linkRaw : json_decode((string) $linkRaw, true);
    if (is_array($jsonLinks)) {
        foreach ($jsonLinks as $row) {
            if (!is_array($row)) {
                continue;
            }
            $parsed = bahman_cms_parse_link_item($row, $fileUrl);
            if ($parsed !== null) {
                $links[] = $parsed;
            }
        }
    }

    return $links;
}

/**
 * Fetch CMS children of a parent, grouped by content-type key (order preserved via setParent).
 *
 * @return array<string, array>
 */
function bahman_cms_children_grouped(int $parentId): array
{
    if ($parentId <= 0) {
        return [];
    }

    $rs = Execute("
        SELECT DISTINCT t.`key` AS type_key
        FROM cms_contents c
        INNER JOIN cms_content_types t ON t.id = c.content_type_id
        WHERE c.parent_id = '{$parentId}'
          AND c.status = '1'
          AND t.`key` IS NOT NULL
          AND t.`key` <> ''
    ");

    $grouped = [];
    if (!$rs) {
        return $grouped;
    }

    while (!$rs->EOF()) {
        $typeKey = trim((string) ($rs->fields['type_key'] ?? ''));
        $rs->MoveNext();
        if ($typeKey === '') {
            continue;
        }

        $child = new CmsContent();
        $result = $child
            ->setType($typeKey)
            ->setParent($parentId)
            ->setCount(-1)
            ->execute();
        unset($child);

        if (is_array($result) && $result !== [] && $result !== 404) {
            $grouped[$typeKey] = $result;
        }
    }
    $rs->Close();

    return $grouped;
}

/**
 * Resolve a direct href from CMS item values (`link` / `href` / `file` / `attachment`).
 * File attachments always use the file-manager base URL (same as level-3 shortlinks).
 *
 * @return array{href:string,attachment:string}
 */
function bahman_cms_resolve_direct_link(array $item, string $fileUrl = ''): array
{
    $values = is_array($item['values'] ?? null) ? $item['values'] : $item;
    if (!is_array($values)) {
        return ['href' => '', 'attachment' => ''];
    }

    $attachment = trim((string) ($values['file'] ?? $values['attachment'] ?? $values['attachemnt'] ?? ''));
    $href = trim((string) ($values['link'] ?? $values['href'] ?? $values['url'] ?? ''));

    if ($attachment !== '') {
        if ($fileUrl !== '') {
            $href = rtrim($fileUrl, '/') . '/' . ltrim($attachment, '/');
        } else {
            $href = $attachment;
        }
    }

    return [
        'href' => $href,
        'attachment' => $attachment,
    ];
}

/**
 * @deprecated Use bahman_cms_resolve_direct_link()
 */
function bahman_cms_resolve_direct_href(array $item, string $fileUrl = ''): string
{
    return bahman_cms_resolve_direct_link($item, $fileUrl)['href'];
}

/**
 * Build 2-level accordion tree from company nested field `list_tree`
 * (content type `listtreeparent` → `treelist` → `shortlink`/`link`).
 *
 * Level-2 (`treelist`) with its own link/file and no child links becomes a
 * direct link row (not a nested accordion).
 *
 * @return list<array{title:string,children:list}>
 */
function bahman_build_list_tree_accordions(array $listTreeItems, string $fileUrl = ''): array
{
    $accordions = [];

    foreach ($listTreeItems as $group) {
        if (!is_array($group)) {
            continue;
        }

        $groupTitle = bahman_cms_item_title($group);
        $groupId = (int) ($group['id'] ?? 0);
        if ($groupTitle === '' || $groupId <= 0) {
            continue;
        }

        $groupChildren = bahman_cms_children_grouped($groupId);
        $subgroups = [];

        foreach ($groupChildren as $typeKey => $items) {
            // Level-3 link types only — not accordion subgroups
            if ($typeKey === 'link' || $typeKey === 'shortlink') {
                continue;
            }
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $sub) {
                if (!is_array($sub)) {
                    continue;
                }

                $subTitle = bahman_cms_item_title($sub);
                $subId = (int) ($sub['id'] ?? 0);
                if ($subTitle === '') {
                    continue;
                }

                $subChildren = $subId > 0 ? bahman_cms_children_grouped($subId) : [];
                $links = bahman_cms_collect_links($sub, $subChildren, $fileUrl);
                $direct = bahman_cms_resolve_direct_link($sub, $fileUrl);
                $directHref = $direct['href'];

                // Nested shortlinks take priority → accordion subgroup
                if ($links !== []) {
                    $subgroups[] = [
                        'id' => $subId ?: null,
                        'type' => 'group',
                        'title' => $subTitle,
                        'href' => '',
                        'attachment' => '',
                        'links' => $links,
                    ];
                    continue;
                }

                // No children, but treelist has link/file → direct link row
                if ($directHref !== '') {
                    $subgroups[] = [
                        'id' => $subId ?: null,
                        'type' => 'link',
                        'title' => $subTitle,
                        'href' => $directHref,
                        'attachment' => $direct['attachment'],
                        'links' => [],
                    ];
                }
            }
        }

        // Fallback: links directly under the group (no subgroup level filled)
        if ($subgroups === []) {
            $directLinks = bahman_cms_collect_links($group, $groupChildren, $fileUrl);
            if ($directLinks !== []) {
                $subgroups[] = [
                    'id' => null,
                    'type' => 'group',
                    'title' => $groupTitle,
                    'href' => '',
                    'links' => $directLinks,
                ];
            }
        }

        if ($subgroups === []) {
            continue;
        }

        $accordions[] = [
            'id' => $groupId,
            'title' => $groupTitle,
            'children' => $subgroups,
        ];
    }

    return $accordions;
}

/**
 * Convert CMS HTML that uses "•" bullet characters into real <ul><li> lists.
 * Handles both "• a • b • c" in one paragraph and "• a<br>• b" patterns.
 */
function bahman_cms_format_bullet_html(string $html): string
{
    $html = trim($html);
    if ($html === '' || (mb_strpos($html, '•') === false && mb_strpos($html, '●') === false)) {
        return $html;
    }

    $formatted = preg_replace_callback(
        '/<p\b[^>]*>(.*?)<\/p>/isu',
        static function (array $m): string {
            $inner = (string) ($m[1] ?? '');
            if (mb_strpos($inner, '•') === false && mb_strpos($inner, '●') === false) {
                return $m[0];
            }

            $normalized = preg_replace('/<br\s*\/?>/iu', "\n", $inner) ?? $inner;
            $normalized = html_entity_decode($normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = preg_split('/\s*[•●]\s*/u', $normalized) ?: [];
            $items = [];
            foreach ($parts as $part) {
                $part = trim(strip_tags((string) $part, '<a><strong><b><em><i><span><br>'));
                $part = trim(preg_replace('/\s+/u', ' ', $part) ?? $part);
                $part = trim($part, " \t\n\r\0\x0B.");
                if ($part === '') {
                    continue;
                }
                $items[] = $part;
            }

            if ($items === []) {
                return $m[0];
            }

            $out = '<ul class="co-prose-list">';
            foreach ($items as $item) {
                $out .= '<li>' . $item . '</li>';
            }
            $out .= '</ul>';
            return $out;
        },
        $html
    );

    return is_string($formatted) ? $formatted : $html;
}

/**
 * Parse a landing contact content_section (HTML desc with «برچسب: مقدار» lines)
 * into company-style address rows + optional map CTA.
 *
 * @return array{addresses: list<array{title:string,value:string,type:string,href:string}>, map: ?array{title:string,href:string}}
 */
function bahman_emdad_parse_contact_section(array $section): array
{
    $addresses = [];
    $map = null;
    $html = trim((string) ($section['values']['desc'] ?? ''));
    if ($html === '') {
        return ['addresses' => $addresses, 'map' => $map];
    }

    $withBreaks = preg_replace('#<\s*br\s*/?\s*>#i', "\n", $html) ?? $html;
    $withBreaks = preg_replace('#</\s*p\s*>#i', "\n", $withBreaks) ?? $withBreaks;
    $plain = trim(html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $plain = preg_replace("/[ \t\x{00A0}]+/u", ' ', $plain) ?? $plain;
    $lines = preg_split('/\n+/u', $plain) ?: [];

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }

        $title = '';
        $value = $line;
        if (preg_match('/^([^:：]{1,40})\s*[:：]\s*(.+)$/u', $line, $m)) {
            $title = trim($m[1]);
            $value = trim($m[2]);
        }
        if ($title === '' || $value === '') {
            continue;
        }

        $titleNorm = str_replace(['ي', 'ك'], ['ی', 'ک'], $title);
        if (mb_strpos($titleNorm, 'مسیریاب') !== false) {
            $map = [
                'title' => $title,
                'href' => $value !== '' ? $value : '#',
            ];
            continue;
        }

        $type = 'text';
        $href = '';
        if (mb_strpos($titleNorm, 'ایمیل') !== false || filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $type = 'email';
            $href = 'mailto:' . $value;
        } elseif (
            mb_strpos($titleNorm, 'تماس') !== false
            || mb_strpos($titleNorm, 'تلفن') !== false
        ) {
            $type = 'tel';
            $tel = preg_replace('/[^\d+]/', '', strtr($value, [
                '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
                '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            ]));
            $href = $tel !== '' ? 'tel:' . $tel : '';
        } elseif (
            preg_match('#^https?://#i', $value)
            || preg_match('#^(www\.)#i', $value)
            || mb_strpos($titleNorm, 'اینستاگرام') !== false
        ) {
            $type = 'url';
            $href = preg_match('#^https?://#i', $value)
                ? $value
                : (preg_match('#^www\.#i', $value) ? 'https://' . $value : $value);
            // Instagram handle without URL → leave as text unless it looks like a path
            if ($type === 'url' && !preg_match('#^(https?://|www\.)#i', $value) && mb_strpos($value, '/') === false) {
                $type = 'text';
                $href = '';
            }
        }

        $addresses[] = [
            'title' => $title,
            'value' => $value,
            'type' => $type,
            'href' => $href,
        ];
    }

    return ['addresses' => $addresses, 'map' => $map];
}

/**
 * Build FAQ accordion rows from content_section items keyed as faq.
 * Supports plain Q&A (title + desc) and optional nested link children.
 *
 * @param list<array> $faqSections
 * @return list<array{title:string,desc:string,children:list<array>}>
 */
function bahman_emdad_build_faq_accordions(array $faqSections, string $fileUrl = ''): array
{
    $accordions = [];
    foreach ($faqSections as $section) {
        if (!is_array($section)) {
            continue;
        }
        $title = bahman_cms_item_title($section);
        if ($title === '') {
            continue;
        }

        $desc = bahman_cms_format_bullet_html(trim((string) ($section['values']['desc'] ?? '')));
        $sectionId = (int) ($section['id'] ?? 0);
        $sectionChildren = is_array($section['child'] ?? null)
            ? $section['child']
            : ($sectionId > 0 ? bahman_cms_children_grouped($sectionId) : []);

        $children = [];
        // Nested content_section / listtree-style groups under this FAQ item
        foreach ($sectionChildren as $typeKey => $items) {
            if (!is_array($items) || $typeKey === 'seo') {
                continue;
            }
            if ($typeKey === 'link' || $typeKey === 'shortlink') {
                continue;
            }
            foreach ($items as $sub) {
                if (!is_array($sub)) {
                    continue;
                }
                $subTitle = bahman_cms_item_title($sub);
                $subId = (int) ($sub['id'] ?? 0);
                if ($subTitle === '') {
                    continue;
                }
                $subChildren = $subId > 0 ? bahman_cms_children_grouped($subId) : [];
                $links = bahman_cms_collect_links($sub, $subChildren, $fileUrl);
                $direct = bahman_cms_resolve_direct_link($sub, $fileUrl);
                if ($links !== []) {
                    $children[] = [
                        'type' => 'group',
                        'title' => $subTitle,
                        'href' => '',
                        'links' => $links,
                    ];
                } elseif (($direct['href'] ?? '') !== '') {
                    $children[] = [
                        'type' => 'link',
                        'title' => $subTitle,
                        'href' => $direct['href'],
                        'links' => [],
                    ];
                } else {
                    $subDesc = bahman_cms_format_bullet_html(trim((string) ($sub['values']['desc'] ?? '')));
                    $children[] = [
                        'type' => 'group',
                        'title' => $subTitle,
                        'href' => '',
                        'desc' => $subDesc,
                        'links' => [],
                    ];
                }
            }
        }

        // Direct links under the FAQ item
        if ($children === []) {
            $directLinks = bahman_cms_collect_links($section, $sectionChildren, $fileUrl);
            foreach ($directLinks as $link) {
                if (!is_array($link)) {
                    continue;
                }
                $children[] = [
                    'type' => 'link',
                    'title' => trim((string) ($link['title'] ?? '')),
                    'href' => trim((string) ($link['href'] ?? '')),
                    'links' => [],
                ];
            }
        }

        if ($desc === '' && $children === []) {
            continue;
        }

        $accordions[] = [
            'title' => $title,
            'desc' => $desc,
            'children' => $children,
        ];
    }

    return $accordions;
}

/**
 * Escape a user query for use inside a MySQL LIKE '%...%' literal.
 */
function bahman_sql_like_escape(string $value): string
{
    $value = remove_xss(trim($value));
    return str_replace(['\\', '%', '_', "'"], ['\\\\', '\\%', '\\_', "\\'"], $value);
}

/**
 * Plain-text window around the first match of $query in $texts (Google-style snippet).
 * Falls back to an excerpt of the first non-empty text.
 */
function bahman_search_snippet(array $texts, string $query, int $maxLen = 180): string
{
    $fallback = '';
    foreach ($texts as $raw) {
        $text = html_entity_decode(strip_tags((string) $raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        if ($text === '') {
            continue;
        }
        if ($fallback === '') {
            $fallback = $text;
        }

        $pos = mb_stripos($text, $query, 0, 'UTF-8');
        if ($pos === false) {
            continue;
        }

        $textLen = mb_strlen($text);
        $start = max(0, $pos - (int) ($maxLen / 3));
        if ($start > 0) {
            $space = mb_strpos($text, ' ', $start);
            if ($space !== false && $space < $pos) {
                $start = $space + 1;
            }
        }
        $snippet = mb_substr($text, $start, $maxLen);
        $cutAtEnd = ($start + $maxLen) < $textLen;
        if ($cutAtEnd) {
            $lastSpace = mb_strrpos($snippet, ' ');
            if ($lastSpace !== false && $lastSpace > ($pos - $start) + mb_strlen($query)) {
                $snippet = mb_substr($snippet, 0, $lastSpace);
            }
        }

        return ($start > 0 ? '…' : '') . trim($snippet) . ($cutAtEnd ? '…' : '');
    }

    return $fallback === '' ? '' : bahman_cms_meta_excerpt($fallback, $maxLen);
}

/**
 * Relevance score: exact title > title contains query > snippet contains query.
 */
function bahman_search_score(string $title, string $snippet, string $query): int
{
    $q = mb_strtolower($query, 'UTF-8');
    $titleLower = mb_strtolower($title, 'UTF-8');
    $score = 10;
    if ($titleLower === $q) {
        $score += 200;
    } elseif (mb_strpos($titleLower, $q) !== false) {
        $score += 100;
    }
    if ($snippet !== '' && mb_stripos($snippet, $query, 0, 'UTF-8') !== false) {
        $score += 20;
    }
    return $score;
}

/**
 * Visible accordion items (title + file or link) from a content_section `link` JSON value.
 */
function bahman_cms_section_link_items($raw): array
{
    if (is_string($raw)) {
        $raw = json_decode($raw, true);
    }
    if (!is_array($raw)) {
        return [];
    }

    $items = [];
    foreach ($raw as $item) {
        if (!is_array($item) || (string) ($item['status'] ?? '1') === '0') {
            continue;
        }
        $title = trim((string) ($item['title'] ?? ''));
        $attachment = trim((string) ($item['attachment'] ?? ''));
        $href = trim((string) ($item['href'] ?? ''));
        if ($title === '' || ($attachment === '' && $href === '')) {
            continue;
        }
        $items[] = ['title' => $title, 'attachment' => $attachment, 'href' => $href];
    }
    return $items;
}

/**
 * Search result for a single accordion file/link, shown with its page › section trail.
 */
function bahman_search_link_result(array $item, string $pageTitle, string $sectionTitle, string $query, string $fileUrl): array
{
    $isFile = $item['attachment'] !== '';
    $path = '';
    $href = '';
    if ($isFile) {
        $href = rtrim($fileUrl, '/') . '/' . ltrim($item['attachment'], '/');
    } elseif (strpos($item['href'], '/') === 0 && strpos($item['href'], '//') !== 0) {
        $path = $item['href'];
    } else {
        $href = $item['href'];
    }

    $trail = implode(' › ', array_filter([$pageTitle, $sectionTitle]));

    return [
        'id' => '',
        'type' => 'document',
        'type_label' => $isFile ? 'فایل' : 'لینک',
        'title' => $item['title'],
        'snippet' => $trail,
        'path' => $path,
        'href' => $href,
        'image' => '',
        'date' => '',
        'score' => bahman_search_score($item['title'], '', $query),
    ];
}

/**
 * Static landing pages (after-sales, about us, /page/{id} ...) whose own fields
 * or content_section children contain the query.
 */
function bahman_cms_search_landings(string $query): array
{
    $fixedRoutes = [
        51 => '/sales',
        252 => '/branch',
        281 => '/after-sale-services',
        402 => '/about-us',
        2936 => '/contactUs',
        3387 => '/chc',
        4200 => '/emdad',
    ];
    // Home, archive shells (their items are searched directly) and the unlinked duplicate of 1084
    $excluded = [22, 349, 1315, 2932, 2935, 3480];

    $landingTypeId = (int) ExecuteScalar("SELECT id FROM cms_content_types WHERE `key` = 'landing' LIMIT 1");
    $sectionTypeId = (int) ExecuteScalar("SELECT id FROM cms_content_types WHERE `key` = 'content_section' LIMIT 1");
    if ($landingTypeId <= 0 || $sectionTypeId <= 0) {
        return [];
    }

    $like = bahman_sql_like_escape($query);
    $excludedSql = implode(',', array_map('intval', $excluded));
    // Human-readable fields (text, textarea, rich text) plus the accordion `link` JSON;
    // href/uuid-only hits inside `link` are dropped later in PHP
    $textFieldMatch = "
        SELECT 1
        FROM cms_content_field_values v
        INNER JOIN cms_content_type_fields f ON f.id = v.content_type_field_id
        WHERE v.content_id = __CONTENT_ID__
          AND v.status = '1'
          AND (
              (f.field_type_id IN (4, 5, 27) AND f.`key` <> 'section_key')
              OR f.`key` = 'link'
          )
          AND v.value LIKE '%{$like}%'
    ";

    // Forms/surveys only have seo children, so requiring a content_section keeps them out
    $rows = ExecuteRows("
        SELECT DISTINCT IFNULL(l.old_cms_id, l.id) AS id
        FROM cms_contents l
        WHERE l.status = '1'
          AND l.content_type_id = '{$landingTypeId}'
          AND IFNULL(l.old_cms_id, l.id) NOT IN ({$excludedSql})
          AND EXISTS (
              SELECT 1 FROM cms_contents s
              WHERE s.parent_id = l.id AND s.status = '1' AND s.content_type_id = '{$sectionTypeId}'
          )
          AND (
              EXISTS (" . str_replace('__CONTENT_ID__', 'l.id', $textFieldMatch) . ")
              OR EXISTS (
                  SELECT 1 FROM cms_contents s
                  WHERE s.parent_id = l.id
                    AND s.status = '1'
                    AND s.content_type_id = '{$sectionTypeId}'
                    AND EXISTS (" . str_replace('__CONTENT_ID__', 's.id', $textFieldMatch) . ")
              )
          )
    ");

    $ids = [];
    foreach ((array) $rows as $row) {
        $id = (int) ($row['id'] ?? $row[0] ?? 0);
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    if ($ids === []) {
        return [];
    }

    $cms = new CmsContent();
    $landings = $cms
        ->setType('landing')
        ->setIds(implode(',', $ids))
        ->setFields('title,banner,content')
        ->setChild(['content_section'])
        ->setCount(-1)
        ->execute();
    unset($cms);

    if (!is_array($landings)) {
        return [];
    }

    $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
    $results = [];
    foreach ($landings as $landing) {
        if (!is_array($landing)) {
            continue;
        }
        $id = (int) ($landing['id'] ?? 0);
        $values = is_array($landing['values'] ?? null) ? $landing['values'] : [];
        $title = trim((string) ($values['title'] ?? ''));
        $title = trim((string) preg_replace('/^صفحه\s+/u', '', $title));
        if ($id <= 0 || $title === '') {
            continue;
        }

        $path = $fixedRoutes[$id] ?? ('/page/' . $id);
        $texts = [$values['content'] ?? ''];
        foreach ((array) ($landing['child']['content_section'] ?? []) as $section) {
            if (!is_array($section) || !is_array($section['values'] ?? null)) {
                continue;
            }
            $sectionValues = $section['values'];
            foreach (['title', 'overline', 'subtitle', 'desc'] as $key) {
                $texts[] = $sectionValues[$key] ?? '';
            }

            $sectionTitle = trim((string) ($sectionValues['title'] ?? ''));
            foreach (bahman_cms_section_link_items($sectionValues['link'] ?? null) as $linkItem) {
                $texts[] = $linkItem['title'];
                if (mb_stripos($linkItem['title'], $query, 0, 'UTF-8') === false) {
                    continue;
                }
                $results[] = bahman_search_link_result($linkItem, $title, $sectionTitle, $query, $fileUrl);
            }
        }

        $haystack = $title . ' ' . implode(' ', array_map('strval', $texts));
        if (mb_stripos(strip_tags($haystack), $query, 0, 'UTF-8') === false) {
            continue;
        }
        $snippet = bahman_search_snippet($texts, $query);

        $banner = trim((string) ($values['banner'] ?? ''));

        $results[] = [
            'id' => $id,
            'type' => 'page',
            'type_label' => 'صفحه',
            'title' => $title,
            'snippet' => $snippet,
            'path' => $path,
            'href' => '',
            'image' => $banner !== '' ? bahman_cms_absolute_media_url($banner, $fileUrl, '400') : '',
            'date' => '',
            'score' => bahman_search_score($title, $snippet, $query) + 5,
        ];
    }

    return $results;
}

/**
 * Authorized dealers; each result opens /branch pre-filtered by dealer code (or name).
 */
function bahman_cms_search_agencies(string $query): array
{
    $cms = new CmsContent();
    $raw = $cms
        ->setType('agency')
        ->setSearch($query)
        ->setFields('title,code,city,province,statud,agency_details')
        ->setCount(-1)
        ->execute();
    unset($cms);

    if (!is_array($raw)) {
        return [];
    }

    $results = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $values = is_array($row['values'] ?? null) ? $row['values'] : [];
        if ((string) ($values['statud'] ?? '1') === '0') {
            continue;
        }
        $title = trim((string) ($values['title'] ?? ''));
        if ($title === '') {
            continue;
        }

        $code = trim((string) ($values['code'] ?? ''));
        $place = implode('، ', array_filter([
            trim((string) ($values['city'] ?? '')),
            trim((string) ($values['province'] ?? '')),
        ]));

        $details = $values['agency_details'] ?? [];
        if (is_string($details)) {
            $details = json_decode($details, true);
        }
        $detailTexts = [];
        foreach ((array) $details as $detail) {
            if (!is_array($detail) || (int) ($detail['status'] ?? 1) !== 1) {
                continue;
            }
            $value = trim((string) ($detail['value'] ?? ''));
            if ($value === '' || preg_match('#^https?://#i', $value)) {
                continue;
            }
            $label = trim((string) ($detail['title'] ?? ''));
            $detailTexts[] = $label !== '' ? $label . ' ' . $value : $value;
        }

        $snippetParts = array_filter([
            $place,
            $code !== '' ? 'کد ' . $code : '',
            bahman_search_snippet([implode(' · ', $detailTexts)], $query, 140),
        ]);
        $snippet = implode(' · ', $snippetParts);

        $results[] = [
            'id' => $row['id'] ?? '',
            'type' => 'agency',
            'type_label' => 'عاملیت',
            'title' => $title,
            'snippet' => $snippet,
            'path' => '/branch?keyword=' . rawurlencode($code !== '' ? $code : $title),
            'href' => '',
            'image' => '',
            'date' => '',
            'score' => bahman_search_score($title, $snippet, $query),
        ];
    }

    return $results;
}

/**
 * Sales directives; results link straight to the directive file like the /sales page does.
 */
function bahman_cms_search_directives(string $query): array
{
    $cms = new CmsContent();
    $raw = $cms
        ->setType('directive')
        ->setSearch($query)
        ->setFields('title,code,desc,attachemnt,status,start_date')
        ->setCount(-1)
        ->execute();
    unset($cms);

    if (!is_array($raw)) {
        return [];
    }

    $fileUrl = defined('FILE_URL') ? rtrim((string) FILE_URL, '/') : '';
    $results = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $values = is_array($row['values'] ?? null) ? $row['values'] : [];
        $title = trim((string) ($values['title'] ?? ''));
        if ($title === '') {
            continue;
        }

        $isActive = trim((string) ($values['status'] ?? '')) === 'فعال';
        $code = trim((string) ($values['code'] ?? ''));
        $snippet = implode(' · ', array_filter([
            $isActive ? 'بخشنامه فعال' : 'بخشنامه آرشیوی',
            trim((string) ($values['start_date'] ?? '')),
            $code !== '' ? 'کد ' . $code : '',
            bahman_search_snippet([$values['desc'] ?? ''], $query, 140),
        ]));

        $attachment = trim((string) ($values['attachemnt'] ?? ''));
        $href = ($attachment !== '' && $fileUrl !== '') ? $fileUrl . '/' . ltrim($attachment, '/') : '';

        $results[] = [
            'id' => $row['id'] ?? '',
            'type' => 'directive',
            'type_label' => 'بخشنامه',
            'title' => $title,
            'snippet' => $snippet,
            'path' => '/sales',
            'href' => $href,
            'image' => '',
            'date' => '',
            'score' => bahman_search_score($title, $snippet, $query) + ($isActive ? 15 : 0),
        ];
    }

    return $results;
}

/**
 * Site-wide CMS keyword search across active public content types.
 * Returns a flat list of result cards sorted by relevance.
 */
function bahman_cms_search_site(string $query): array
{
    $query = trim($query);
    if ($query === '' || mb_strlen($query) < 2) {
        return [];
    }

    $sources = [
        [
            'type' => 'product',
            'key' => 'product',
            'label' => 'محصول',
            'path' => '/product/',
            'fields' => 'title,image,short_desc,desc',
        ],
        [
            'type' => 'blog',
            'key' => 'article',
            'label' => 'خبر',
            'path' => '/post/',
            'fields' => 'title,image,short_desc,content',
        ],
        [
            'type' => 'blog2',
            'key' => 'blog',
            'label' => 'بلاگ',
            'path' => '/blog/',
            'fields' => 'title,image,short_desc,content',
        ],
        [
            'type' => 'event',
            'key' => 'event',
            'label' => 'رویداد',
            'path' => '/event/',
            'fields' => 'title,image,desc,content',
        ],
        [
            'type' => 'video',
            'key' => 'video',
            'label' => 'ویدیو',
            'path' => '/video/',
            'fields' => 'title,image,short_desc,content',
        ],
        [
            'type' => 'company',
            'key' => 'brand',
            'label' => 'برند',
            'path' => '/brand/',
            'fields' => 'title,image,banner',
        ],
    ];

    $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
    $results = [];

    foreach ($sources as $src) {
        $cms = new CmsContent();
        $raw = $cms
            ->setType($src['type'])
            ->setSearch($query)
            ->setFields($src['fields'])
            ->setCount(-1)
            ->execute();
        unset($cms);

        if ($raw === 404 || !is_array($raw) || $raw === []) {
            continue;
        }

        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $values = is_array($row['values'] ?? null) ? $row['values'] : [];
            $title = trim((string) ($values['title'] ?? ''));
            $id = $row['id'] ?? null;
            if ($title === '' || $id === null || $id === '') {
                continue;
            }

            $snippet = bahman_search_snippet([
                $values['short_desc'] ?? '',
                $values['desc'] ?? '',
                $values['content'] ?? '',
            ], $query);

            $imagePath = trim((string) ($values['image'] ?? $values['banner'] ?? ''));

            $results[] = [
                'id' => $id,
                'type' => $src['key'],
                'type_label' => $src['label'],
                'title' => $title,
                'snippet' => $snippet,
                'path' => $src['path'] . rawurlencode((string) $id),
                'href' => '',
                'image' => $imagePath !== '' ? bahman_cms_absolute_media_url($imagePath, $fileUrl, '400') : '',
                'date' => $row['date'] ?? '',
                'score' => bahman_search_score($title, $snippet, $query) + ($src['key'] === 'product' ? 30 : 0),
            ];
        }
    }

    $results = array_merge(
        $results,
        bahman_cms_search_landings($query),
        bahman_cms_search_agencies($query),
        bahman_cms_search_directives($query)
    );

    // Ties keep CMS order (pinned/priority/newest first, accordion order for files)
    foreach ($results as $seq => &$result) {
        $result['seq'] = $seq;
    }
    unset($result);

    usort($results, static function ($a, $b) {
        $scoreCmp = ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        if ($scoreCmp !== 0) {
            return $scoreCmp;
        }
        return $a['seq'] <=> $b['seq'];
    });

    return $results;
}

if (!isset($_GET['include']) || $_GET['include'] === '') {
    $_GET['include'] = '404';
}

// برگه امداد (landing 4200) — هم از /emdad و هم از /page/4200
if (
    ($_GET['include'] ?? '') === 'show_page'
    && isset($_GET['page_id'])
    && (int) $_GET['page_id'] === 4200
) {
    $_GET['include'] = 'emdad';
}

$page_title = null;
$page_description = null;
$og_image = null;
$og_image_alt = null;
$og_type = 'website';
$canonical_url = null;

if (isset($_GET['include'])) {

    switch ($_GET['include']) {
        case 'index':
            $template = 'index';
            $page_title = 'گروه بهمن | خودروهای سواری، تجاری و خدمات';
            $page_description = 'گروه بهمن؛ تولیدکننده و عرضه‌کننده خودروهای سواری، تجاری و قطعات. محصولات، بخشنامه‌های فروش، شبکه عاملیت‌ها و خدمات پس از فروش.';
            $index = new CmsContent();
            $index
                ->setType('landing')
                ->setId(22)
                ->setChild(['content_section'])
                ->setCount(1);
            $index_page = $index->execute();
            // var_dump($index_page);
            // exit;
            if ($index_page !== 404) {
                @$smarty->assign('index_page', $index_page[0]);
            }
            unset($index);
            unset($index_page);

            $blog_category = new CmsContent();
            $blog_category
                ->setType('blog_category')
                ->setChildTable(['blog:blog_category'])
                ->setFields("title");

                // var_dump($blog_category->execute());
                // exit;
            
            $smarty->assign('blog_category', $blog_category->execute());
            unset($blog_category);
            
            $videos = new CmsContent();
            $videos
                ->setType('video')
                ->setFields('title,image,aparat_link,short_desc');

            $smarty->assign('videos', $videos->execute());
            unset($videos);

            $home_events = new CmsContent();
            $home_events
                ->setType('event')
                ->setFields('title,image,desc')
                ->setCount(2);
            $home_events_result = $home_events->execute();
            unset($home_events);
            $smarty->assign(
                'home_events',
                ($home_events_result !== 404 && is_array($home_events_result)) ? $home_events_result : []
            );
            unset($home_events_result);

            $home_blogs = new CmsContent();
            $home_blogs
                ->setType('blog2')
                ->setFields('title,image,short_desc')
                ->setCount(2);
            $home_blogs_result = $home_blogs->execute();
            unset($home_blogs);
            $smarty->assign(
                'home_blogs',
                ($home_blogs_result !== 404 && is_array($home_blogs_result)) ? $home_blogs_result : []
            );
            unset($home_blogs_result);

            break;
        case 'video':
            $template = 'video';
            $page_title = 'ویدیوها | گروه بهمن';
            $page_description = 'آرشیو ویدیوهای گروه بهمن؛ معرفی محصولات، رویدادها و محتوای چندرسانه‌ای.';

            // صفحه تنظیمات آرشیو ویدیو در ادمین (landing id=2932)
            $video_live_text = '';
            $video_live_url = '';
            $video_page_obj = new CmsContent();
            $video_page_obj
                ->setType('landing')
                ->setId(2932)
                ->setChild(['content_section'])
                ->setCount(1);
            $video_page_raw = $video_page_obj->execute();
            unset($video_page_obj);

            if ($video_page_raw !== 404 && !empty($video_page_raw[0]) && is_array($video_page_raw[0])) {
                $video_page = $video_page_raw[0];
                $smarty->assign('video_page', $video_page);

                $cmsTitle = trim((string) ($video_page['values']['title'] ?? ''));
                if ($cmsTitle !== '') {
                    $page_title = $cmsTitle . ' | گروه بهمن';
                }

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $sections = $video_page['child']['content_section'] ?? [];
                if (is_array($sections)) {
                    foreach ($sections as $section) {
                        if (!is_array($section)) {
                            continue;
                        }
                        $sectionKey = trim((string) ($section['values']['section_key'] ?? ''));
                        if ($sectionKey !== 'live') {
                            continue;
                        }

                        // سکشن از CMS فقط با status=1 می‌آید؛ لینک فعال از فیلد link (title + href)
                        $liveLinks = bahman_cms_collect_links(
                            $section,
                            is_array($section['child'] ?? null) ? $section['child'] : [],
                            $fileUrl
                        );
                        $liveLink = $liveLinks[0] ?? null;
                        if (is_array($liveLink)) {
                            $video_live_text = trim((string) ($liveLink['title'] ?? ''));
                            $video_live_url = trim((string) ($liveLink['href'] ?? ''));
                        }
                        if ($video_live_text === '') {
                            $video_live_text = trim((string) ($section['values']['title'] ?? ''));
                        }
                        break;
                    }
                }
                unset($video_page, $sections, $fileUrl);
            }
            unset($video_page_raw);

            // همه ویدیوها
            $videos_all_obj = new CmsContent();
            $videos_all_obj
                ->setType('video')
                ->setCount(-1)
                ->setFields('title,image,aparat_link,short_desc,video_category,video');
            $videos_all_result = $videos_all_obj->execute();
            $videos_all = ($videos_all_result !== 404 && is_array($videos_all_result)) ? $videos_all_result : [];
            $smarty->assign('videos_all', $videos_all);
            unset($videos_all_obj, $videos_all_result);

            // دسته‌بندی‌های ویدیو + ویدیوهای هر دسته (بر اساس فیلد video_category)
            $video_cats_obj = new CmsContent();
            $video_cats_obj
                ->setType('video_category')
                ->setCount(-1)
                ->setFields('title,image');
            $video_cats_raw = $video_cats_obj->execute();
            unset($video_cats_obj);

            $video_categories = [];
            if ($video_cats_raw !== 404 && is_array($video_cats_raw)) {
                foreach ($video_cats_raw as $cat) {
                    $cat_id = isset($cat['id']) ? (string) $cat['id'] : '';
                    if ($cat_id === '') {
                        continue;
                    }

                    $cat['videos'] = array_values(array_filter($videos_all, static function ($vid) use ($cat_id) {
                        $raw = (string) ($vid['values']['video_category'] ?? '');
                        if ($raw === '') {
                            return false;
                        }
                        $ids = array_filter(array_map('trim', explode(',', $raw)));
                        return in_array($cat_id, $ids, true);
                    }));

                    $video_categories[] = $cat;
                }
            }
            unset($video_cats_raw, $videos_all);
            $smarty->assign('video_categories', $video_categories);
            unset($video_categories);

            $smarty->assign('video_live_text', $video_live_text);
            $smarty->assign('video_live_url', $video_live_url);

            break;

        case 'show_video':
            $template = 'show_video';

            if (!isset($_GET['video_id']) || !is_numeric($_GET['video_id'])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header('HTTP/1.1 404 Not Found');
                break;
            }

            $video = new CmsContent();
            $video
                ->setType('video')
                ->setId($_GET['video_id'])
                ->setFields('title,image,aparat_link,short_desc,content,video,video_category')
                ->setCount(1);
            $show_video = $video->execute();

            if ($show_video == 404 || empty($show_video[0])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header('HTTP/1.1 404 Not Found');
            } else {
                $item = $show_video[0];
                $smarty->assign('show_video', $item);

                $videoTitle = trim((string) ($item['values']['title'] ?? ''));
                $page_title = ($videoTitle !== '' ? $videoTitle : 'ویدیو') . ' | گروه بهمن';

                $videoDesc = bahman_cms_meta_excerpt($item['values']['short_desc'] ?? '');
                if ($videoDesc === '') {
                    $videoDesc = bahman_cms_meta_excerpt($item['values']['content'] ?? '');
                }
                $page_description = $videoDesc !== ''
                    ? $videoDesc
                    : (($videoTitle !== '' ? $videoTitle . ' — ' : '') . 'ویدیو گروه بهمن');

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $videoImage = bahman_cms_absolute_media_url(
                    (string) ($item['values']['image'] ?? ''),
                    $fileUrl,
                    '1200'
                );
                if ($videoImage !== '') {
                    $og_image = $videoImage;
                    $og_image_alt = $videoTitle !== '' ? $videoTitle : 'گروه بهمن';
                }
                $og_type = 'video.other';

                $smarty->assign('show_video_src', bahman_cms_video_stream_url($item['values']['video'] ?? ''));
                $smarty->assign('show_video_aparat', bahman_cms_aparat_embed_url($item['values']['aparat_link'] ?? ''));
                $smarty->assign('show_video_poster', $videoImage);

                $related = new CmsContent();
                $related
                    ->setType('video')
                    ->setFields('title,image,aparat_link,video')
                    ->setCount(12);
                $related_videos = $related->execute();
                unset($related);

                if (!is_array($related_videos)) {
                    $related_videos = [];
                }

                $currentId = (int) $_GET['video_id'];
                $related_videos = array_values(array_filter($related_videos, static function ($row) use ($currentId) {
                    return (int) ($row['id'] ?? 0) !== $currentId;
                }));
                $smarty->assign('related_videos', array_slice($related_videos, 0, 8));
                unset($related_videos, $item);
            }

            unset($video, $show_video);
            break;

        case 'product':
            $template = 'product';
            $page_title = 'محصولات | گروه بهمن';
            $page_description = 'فهرست محصولات گروه بهمن؛ خودروهای سواری و تجاری برندهای زیرمجموعه را مشاهده کنید.';
            break;
        case 'article':
            $template = 'article';
            $page_title = 'آرشیو اخبار | گروه بهمن';
            $page_description = 'آخرین اخبار و اطلاعیه‌های گروه بهمن را در آرشیو اخبار دنبال کنید.';

            $article = new CmsContent();
            $article
                ->setType('landing')
                ->setId(1315)
                ->setChild(['content_section'])
                ->setCount(1);
            $article_page = $article->execute();
            if ($article_page !== 404) {
                @$smarty->assign('article_page', $article_page[0]);
            }
            unset($article);
            unset($article_page);

            $blog_category = new CmsContent();
            $blog_category
                ->setType('blog_category')
                ->setChildTable(['blog:blog_category'])
                ->setFields("title");
            $blog_category_list = $blog_category->execute();
            unset($blog_category);

            $blog_all_obj = new CmsContent();
            $blog_all_obj->setType('blog')->setCount(-1);
            $blog_all_result = $blog_all_obj->execute();
            unset($blog_all_obj);
            $blog_all = ($blog_all_result !== 404 && is_array($blog_all_result)) ? $blog_all_result : [];

            $smarty->assign('blog_category', $blog_category_list);
            $smarty->assign('blog_all', $blog_all);
            unset($blog_category_list);
            unset($blog_all);
            break;

        case 'blog':
            $template = 'blog';
            $page_title = 'آرشیو بلاگ | گروه بهمن';
            $page_description = 'آخرین مطالب بلاگ گروه بهمن را در آرشیو بلاگ دنبال کنید.';

            // صفحه تنظیمات آرشیو بلاگ در ادمین (landing id=3480)
            $blog_page_obj = new CmsContent();
            $blog_page_obj
                ->setType('landing')
                ->setId(3480)
                ->setChild(['content_section'])
                ->setCount(1);
            $blog_page_raw = $blog_page_obj->execute();
            unset($blog_page_obj);
            if ($blog_page_raw !== 404 && !empty($blog_page_raw[0]) && is_array($blog_page_raw[0])) {
                $blog_page = $blog_page_raw[0];
                $smarty->assign('blog_page', $blog_page);
                $cmsTitle = trim((string) ($blog_page['values']['title'] ?? ''));
                if ($cmsTitle !== '') {
                    $page_title = $cmsTitle . ' | گروه بهمن';
                }
                unset($blog_page, $cmsTitle);
            }
            unset($blog_page_raw);

            $blog2_category = new CmsContent();
            $blog2_category
                ->setType('blog_category2')
                ->setChildTable(['blog2:blog_category2'])
                ->setFields("title");
            $blog2_category_list = $blog2_category->execute();
            unset($blog2_category);

            $blog2_all_obj = new CmsContent();
            $blog2_all_obj->setType('blog2')->setCount(-1);
            $blog2_all_result = $blog2_all_obj->execute();
            unset($blog2_all_obj);
            $blog2_all = ($blog2_all_result !== 404 && is_array($blog2_all_result)) ? $blog2_all_result : [];

            $smarty->assign('blog2_category', $blog2_category_list);
            $smarty->assign('blog2_all', $blog2_all);
            unset($blog2_category_list);
            unset($blog2_all);
            break;

        case 'event':
            $template = 'event';
            $page_title = 'آرشیو رویدادها | گروه بهمن';
            $page_description = 'آخرین رویدادها و همایش‌های گروه بهمن را در آرشیو رویدادها دنبال کنید.';

            // صفحه تنظیمات آرشیو رویدادها در ادمین (landing id=2935)
            $event_page_obj = new CmsContent();
            $event_page_obj
                ->setType('landing')
                ->setId(2935)
                ->setChild(['content_section'])
                ->setCount(1);
            $event_page_raw = $event_page_obj->execute();
            unset($event_page_obj);
            if ($event_page_raw !== 404 && !empty($event_page_raw[0]) && is_array($event_page_raw[0])) {
                $event_page = $event_page_raw[0];
                $smarty->assign('event_page', $event_page);
                $cmsTitle = trim((string) ($event_page['values']['title'] ?? ''));
                if ($cmsTitle !== '') {
                    $page_title = $cmsTitle . ' | گروه بهمن';
                }
                unset($event_page, $cmsTitle);
            }
            unset($event_page_raw);

            $events_all_obj = new CmsContent();
            $events_all_obj
                ->setType('event')
                ->setFields('title,image,desc,content,gallery')
                ->setCount(-1);
            $events_all_result = $events_all_obj->execute();
            unset($events_all_obj);
            $events_all = ($events_all_result !== 404 && is_array($events_all_result)) ? $events_all_result : [];
            $smarty->assign('events_all', $events_all);
            unset($events_all_result, $events_all);
            break;

        case 'show_event':
            $template = 'show_event';

            if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header('HTTP/1.1 404 Not Found');
                break;
            }

            $event = new CmsContent();
            $event
                ->setType('event')
                ->setId($_GET['event_id'])
                ->setFields('title,image,desc,content,gallery,video')
                ->setCount(1);
            $show_event = $event->execute();

            if ($show_event == 404 || empty($show_event[0])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header('HTTP/1.1 404 Not Found');
            } else {
                $item = $show_event[0];
                $smarty->assign('show_event', $item);

                $eventTitle = trim((string) ($item['values']['title'] ?? ''));
                $page_title = ($eventTitle !== '' ? $eventTitle : 'رویداد') . ' | گروه بهمن';

                $eventDesc = bahman_cms_meta_excerpt($item['values']['desc'] ?? '');
                if ($eventDesc === '') {
                    $eventDesc = bahman_cms_meta_excerpt($item['values']['content'] ?? '');
                }
                $page_description = $eventDesc !== ''
                    ? $eventDesc
                    : (($eventTitle !== '' ? $eventTitle . ' — ' : '') . 'رویداد گروه بهمن');

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $eventImage = bahman_cms_absolute_media_url(
                    (string) ($item['values']['image'] ?? ''),
                    $fileUrl,
                    '1200'
                );
                if ($eventImage !== '') {
                    $og_image = $eventImage;
                    $og_image_alt = $eventTitle !== '' ? $eventTitle : 'گروه بهمن';
                }
                $og_type = 'article';

                $galleryRaw = (string) ($item['values']['gallery'] ?? '');
                $galleryIds = array_values(array_filter(array_map('trim', explode(',', $galleryRaw))));
                $smarty->assign('show_event_gallery', $galleryIds);

                $videoRaw = (string) ($item['values']['video'] ?? '');
                $videoIds = array_values(array_filter(array_map('trim', explode(',', $videoRaw))));
                $eventVideos = [];
                foreach ($videoIds as $videoUuid) {
                    $streamUrl = bahman_cms_video_stream_url($videoUuid);
                    if ($streamUrl !== '') {
                        $eventVideos[] = $streamUrl;
                    }
                }
                $smarty->assign('show_event_videos', $eventVideos);

                $latest = new CmsContent();
                $latest
                    ->setType('event')
                    ->setFields('title,image,desc')
                    ->setCount(16);
                $latest_events = $latest->execute();
                unset($latest);

                if (!is_array($latest_events)) {
                    $latest_events = [];
                }

                $currentId = (int) $_GET['event_id'];
                $latest_events = array_values(array_filter($latest_events, static function ($row) use ($currentId) {
                    return (int) ($row['id'] ?? 0) !== $currentId;
                }));
                $smarty->assign('latest_events', array_slice($latest_events, 0, 8));
                unset($latest_events, $item, $galleryIds, $videoIds, $eventVideos);
            }

            unset($event, $show_event);
            break;

        case 'show_article':
            $template = 'show_article';

            if (!isset($_GET['article_id']) || !is_numeric($_GET['article_id'])) {
                $template = '404';
                header("HTTP/1.1 404 Not Found");
                break;
            }

            $article = new CmsContent();
            $article
                ->setType('blog')
                ->setId($_GET['article_id'])
                ->setFields('title,image,content,short_desc')
                ->setCount(1);
            $show_article = $article->execute();

            if ($show_article == 404 || empty($show_article[0])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header("HTTP/1.1 404 Not Found");
            } else {
                @$smarty->assign('show_article', $show_article[0]);

                $articleTitle = trim((string) ($show_article[0]['values']['title'] ?? ''));
                if ($articleTitle !== '') {
                    $page_title = $articleTitle . ' | گروه بهمن';
                } else {
                    $page_title = 'خبر | گروه بهمن';
                }

                $articleDesc = bahman_cms_meta_excerpt($show_article[0]['values']['short_desc'] ?? '');
                if ($articleDesc === '') {
                    $articleDesc = bahman_cms_meta_excerpt($show_article[0]['values']['content'] ?? '');
                }
                if ($articleDesc !== '') {
                    $page_description = $articleDesc;
                } else {
                    $page_description = ($articleTitle !== '' ? $articleTitle . ' — ' : '') . 'خبر گروه بهمن';
                }

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $articleImage = bahman_cms_absolute_media_url(
                    (string) ($show_article[0]['values']['image'] ?? ''),
                    $fileUrl,
                    '1200'
                );
                if ($articleImage !== '') {
                    $og_image = $articleImage;
                    $og_image_alt = $articleTitle !== '' ? $articleTitle : 'گروه بهمن';
                }
                $og_type = 'article';

                $latest = new CmsContent();
                $latest
                    ->setType('blog')
                    ->setCount(16);
                $latest_articles = $latest->execute();
                unset($latest);

                if (!is_array($latest_articles)) {
                    $latest_articles = [];
                }

                $currentId = (int) $_GET['article_id'];
                $latest_articles = array_values(array_filter($latest_articles, function ($item) use ($currentId) {
                    return (int) ($item['id'] ?? 0) !== $currentId;
                }));

                $smarty->assign('latest_articles', array_slice($latest_articles, 0, 8));
                unset($latest_articles);
            }

            unset($article);
            unset($show_article);
            break;

        case 'show_blog':
            $template = 'show_blog';

            if (!isset($_GET['blog_id']) || !is_numeric($_GET['blog_id'])) {
                $template = '404';
                header("HTTP/1.1 404 Not Found");
                break;
            }

            $blog2 = new CmsContent();
            $blog2
                ->setType('blog2')
                ->setId($_GET['blog_id'])
                ->setFields('title,image,content,short_desc')
                ->setCount(1);
            $show_blog = $blog2->execute();

            if ($show_blog == 404 || empty($show_blog[0])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header("HTTP/1.1 404 Not Found");
            } else {
                @$smarty->assign('show_blog', $show_blog[0]);

                $blogTitle = trim((string) ($show_blog[0]['values']['title'] ?? ''));
                if ($blogTitle !== '') {
                    $page_title = $blogTitle . ' | گروه بهمن';
                } else {
                    $page_title = 'بلاگ | گروه بهمن';
                }

                $blogDesc = bahman_cms_meta_excerpt($show_blog[0]['values']['short_desc'] ?? '');
                if ($blogDesc === '') {
                    $blogDesc = bahman_cms_meta_excerpt($show_blog[0]['values']['content'] ?? '');
                }
                if ($blogDesc !== '') {
                    $page_description = $blogDesc;
                } else {
                    $page_description = ($blogTitle !== '' ? $blogTitle . ' — ' : '') . 'مطالب بلاگ گروه بهمن';
                }

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $blogImage = bahman_cms_absolute_media_url(
                    (string) ($show_blog[0]['values']['image'] ?? ''),
                    $fileUrl,
                    '1200'
                );
                if ($blogImage !== '') {
                    $og_image = $blogImage;
                    $og_image_alt = $blogTitle !== '' ? $blogTitle : 'گروه بهمن';
                }
                $og_type = 'article';

                $latest = new CmsContent();
                $latest
                    ->setType('blog2')
                    ->setCount(16);
                $latest_blogs = $latest->execute();
                unset($latest);

                if (!is_array($latest_blogs)) {
                    $latest_blogs = [];
                }

                $currentId = (int) $_GET['blog_id'];
                $latest_blogs = array_values(array_filter($latest_blogs, function ($item) use ($currentId) {
                    return (int) ($item['id'] ?? 0) !== $currentId;
                }));

                $smarty->assign('latest_blogs', array_slice($latest_blogs, 0, 8));
                unset($latest_blogs);
            }

            unset($blog2);
            unset($show_blog);
            break;

        case 'show_product':
            $template = 'show_product';

            $product = new CmsContent();
            $product
                ->setType('product')
                ->setId($_GET['product_id'])
                ->setChild(['seo', 'content_section', 'gallery', 'product_details', 'link', 'product_colors'])
                ->setCount(1);
            $show_product = $product->execute();

            if ($show_product == 404) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header("HTTP/1.1 404 Not Found");
            } else {
                $productTitle = trim((string) ($show_product[0]['values']['title'] ?? ''));
                if ($productTitle !== '') {
                    $page_title = $productTitle . ' | گروه بهمن';
                } else {
                    $page_title = 'محصول | گروه بهمن';
                }

                $productDesc = '';
                if (!empty($show_product[0]['child']['seo'][0]['values'])) {
                    $seoVals = $show_product[0]['child']['seo'][0]['values'];
                    $seoTitle = trim((string) ($seoVals['title'] ?? $seoVals['meta_title'] ?? ''));
                    if ($seoTitle !== '') {
                        $page_title = $seoTitle;
                        if (mb_strpos($page_title, 'گروه بهمن') === false) {
                            $page_title .= ' | گروه بهمن';
                        }
                    }
                    $productDesc = bahman_cms_meta_excerpt(
                        $seoVals['description'] ?? $seoVals['meta_description'] ?? $seoVals['desc'] ?? ''
                    );
                }
                if ($productDesc === '') {
                    $productDesc = bahman_cms_meta_excerpt(
                        $show_product[0]['values']['short_desc'] ?? $show_product[0]['values']['desc'] ?? ''
                    );
                }
                if ($productDesc !== '') {
                    $page_description = $productDesc;
                } else {
                    $page_description = ($productTitle !== '' ? $productTitle . ' — ' : '') . 'محصول گروه بهمن؛ مشخصات، گالری و اطلاعات فروش.';
                }

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $productImage = bahman_cms_absolute_media_url(
                    (string) ($show_product[0]['values']['image'] ?? ''),
                    $fileUrl,
                    '1200'
                );
                if ($productImage !== '') {
                    $og_image = $productImage;
                    $og_image_alt = $productTitle !== '' ? $productTitle : 'گروه بهمن';
                }
                $og_type = 'product';
            }

            // var_dump($show_product[0]);
            // exit;

            @$smarty->assign('show_product', $show_product[0]);
            unset($product);
            unset($show_product);
            break;
        case 'sales':
            $template = 'sales';
            $page_title = 'فروش | گروه بهمن';
            $page_description = 'بخشنامه‌های فروش فعال و آرشیو بخشنامه‌های گروه بهمن را مشاهده کنید.';

            $salesFastMode = is_file(dirname(__DIR__, 2) . '/.FAST_MODE')
                || is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE');
            $salesDataCacheFile = sys_get_temp_dir() . '/bahman_sales_page_data.cache';
            $salesCached = null;
            if ($salesFastMode && is_file($salesDataCacheFile) && (time() - (int) filemtime($salesDataCacheFile)) < 120) {
                $salesCached = @unserialize((string) file_get_contents($salesDataCacheFile));
            }

            if (is_array($salesCached) && isset($salesCached['sales_page'], $salesCached['groupedCirculars'], $salesCached['groupedInactiveCirculars'])) {
                if ($salesCached['sales_page'] !== null) {
                    @$smarty->assign('sales_page', $salesCached['sales_page']);
                }
                $groupedCirculars = $salesCached['groupedCirculars'];
                $groupedInactiveCirculars = $salesCached['groupedInactiveCirculars'];
                @$smarty->assign('sales_active', $groupedCirculars);
                @$smarty->assign('sales_inactive', $groupedInactiveCirculars);
                break;
            }

            $sales = new CmsContent();
            $sales
                ->setType('landing')
                ->setId(51)
                ->setChild(['content_section'])
                ->setCount(1);
            $sales_page = $sales->execute();
            $sales_page_row = null;
            if ($sales_page !== 404) {
                $sales_page_row = $sales_page[0];
                @$smarty->assign('sales_page', $sales_page_row);
            }
            unset($sales);
            unset($sales_page);

            $sales = new CmsContent();
            $sales
                ->setType('directive')
                ->setCount(-1);
            
            $sales_data = $sales->execute();
            if (!is_array($sales_data)) {
                $sales_data = [];
            }
            
            $activeItems = array_filter($sales_data, function($item) {
                return isset($item['values']['status']) && $item['values']['status'] == "فعال";
            });
            $activeItems = array_values($activeItems);
            $groupedCirculars = [];
            foreach ($activeItems as $circular) {
                $companyId = $circular['values']['company'];

                if (!isset($groupedCirculars[$companyId])) {
                    $groupedCirculars[$companyId] = [];
                }

                $groupedCirculars[$companyId][] = $circular;
            }

            // var_dump($groupedCirculars);
            // exit;

            $inactiveItems = array_filter($sales_data, function($item) {
                return !isset($item['values']['status']) || $item['values']['status'] != "فعال";
            });
            $groupedInactiveCirculars = [];
            foreach ($inactiveItems as $circular) {
                $companyId = trim((string) ($circular['values']['company'] ?? ''));
                if ($companyId === '') {
                    $companyId = '_other';
                }

                if (!isset($groupedInactiveCirculars[$companyId])) {
                    $groupedInactiveCirculars[$companyId] = [];
                }

                $groupedInactiveCirculars[$companyId][] = $circular;
            }

            foreach ($groupedInactiveCirculars as &$inactiveCirculars) {
                usort($inactiveCirculars, static function (array $a, array $b): int {
                    $cmp = bahman_cms_directive_start_date_ts($b) <=> bahman_cms_directive_start_date_ts($a);
                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
                });
            }
            unset($inactiveCirculars);

            @$smarty->assign('sales_active', $groupedCirculars);
            @$smarty->assign('sales_inactive', $groupedInactiveCirculars);
            unset($sales);

            if ($salesFastMode) {
                @file_put_contents($salesDataCacheFile, serialize([
                    'sales_page' => $sales_page_row,
                    'groupedCirculars' => $groupedCirculars,
                    'groupedInactiveCirculars' => $groupedInactiveCirculars,
                ]));
            }
            break;
        case 'after_sales':
            $template = 'after_sales';
            $page_title = 'خدمات پس از فروش | گروه بهمن';
            $page_description = 'خدمات پس از فروش گروه بهمن؛ پشتیبانی، تعمیرات و اطلاعات مرتبط با مشتریان.';

            $sales = new CmsContent();
            $sales
                ->setType('landing')
                ->setId(281)
                ->setChild(['content_section'])
                ->setCount(1);
            $sales_page = $sales->execute();
            if ($sales_page !== 404) {
                @$smarty->assign('sales_page', $sales_page[0]);
            }
            unset($sales);
            unset($sales_page);

            break;
        case 'emdad':
            $template = 'emdad';
            $page_title = 'امداد | گروه بهمن';
            $page_description = 'خدمات امداد گروه بهمن؛ معرفی، گالری، پرسش‌های متداول و راه‌های تماس.';
            $emdadLandingId = 4200;

            $emdad = new CmsContent();
            $emdad
                ->setType('landing')
                ->setId($emdadLandingId)
                ->setFields('title,banner,pic_gallery,video,content,desc')
                ->setChild(['content_section'])
                ->setCount(1);
            $emdadData = $emdad->execute();
            unset($emdad);

            if ($emdadData === 404 || empty($emdadData[0]) || !is_array($emdadData[0])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header("HTTP/1.1 404 Not Found");
                break;
            }

            $branch_item = $emdadData[0];
            unset($emdadData);

            $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
            if ($fileUrl === '' && isset($admin_setting) && is_array($admin_setting)) {
                $fileUrl = (string) ($admin_setting['file_url'] ?? '');
            }

            $branch_history = [];
            $faq_sections = [];
            $contact_section = null;
            $slider_links = [];
            $content_sections = $branch_item['child']['content_section'] ?? [];
            if (is_array($content_sections)) {
                $content_sections = bahman_cms_sort_by_list_priority($content_sections);
                foreach ($content_sections as $section) {
                    if (!is_array($section)) {
                        continue;
                    }
                    $values = is_array($section['values'] ?? null) ? $section['values'] : [];
                    $section_key = trim((string) ($values['section_key'] ?? ''));

                    if ($section_key === 'slider_link') {
                        $sectionId = (int) ($section['id'] ?? 0);
                        $sectionChildren = is_array($section['child'] ?? null)
                            ? $section['child']
                            : ($sectionId > 0 ? bahman_cms_children_grouped($sectionId) : []);
                        foreach (bahman_cms_collect_links($section, $sectionChildren, $fileUrl) as $link) {
                            if (!is_array($link)) {
                                continue;
                            }
                            $linkHref = trim((string) ($link['href'] ?? ''));
                            $linkTitle = trim((string) ($link['title'] ?? ''));
                            if ($linkTitle === '') {
                                continue;
                            }
                            $slider_links[] = [
                                'title' => $linkTitle,
                                'href' => $linkHref !== '' ? $linkHref : '#',
                                'external' => (bool) preg_match('#^https?://#i', $linkHref),
                            ];
                        }
                        continue;
                    }

                    if ($section_key === 'content') {
                        $desc = trim(strip_tags((string) ($values['desc'] ?? '')));
                        $has_image = trim((string) ($values['image'] ?? '')) !== '';
                        if ($desc !== '' || $has_image) {
                            if (isset($section['values']) && is_array($section['values'])) {
                                $section['values']['desc'] = bahman_cms_format_bullet_html(
                                    trim((string) ($section['values']['desc'] ?? ''))
                                );
                            }
                            $branch_history[] = $section;
                        }
                        continue;
                    }

                    if ($section_key === 'faq') {
                        $faq_sections[] = $section;
                        continue;
                    }

                    if ($section_key === 'contact' && $contact_section === null) {
                        $contact_section = $section;
                    }
                }
            }

            $branch_gallery = [];
            $picRaw = trim((string) ($branch_item['values']['pic_gallery'] ?? ''));
            if ($picRaw !== '') {
                $images = array_values(array_filter(array_map('trim', explode(',', $picRaw))));
                if ($images !== []) {
                    $branch_gallery[] = [
                        'id' => null,
                        'title' => 'تصاویر',
                        'images' => $images,
                    ];
                }
            }

            // Multi video field on emdad landing (comma-separated UUIDs, same as company.video)
            $branch_videos = [];
            $emdadVideoRaw = trim((string) ($branch_item['values']['video'] ?? ''));
            if ($emdadVideoRaw !== '') {
                $emdadVideoIds = array_values(array_filter(array_map('trim', explode(',', $emdadVideoRaw))));
                foreach ($emdadVideoIds as $videoUuid) {
                    $streamUrl = bahman_cms_video_stream_url($videoUuid);
                    if ($streamUrl !== '') {
                        $branch_videos[] = $streamUrl;
                    }
                }
            }

            $branch_faq = bahman_emdad_build_faq_accordions($faq_sections, $fileUrl);

            $branch_addresses = [];
            $branch_map = null;
            $branch_contact_actions = [];
            if (is_array($contact_section)) {
                $parsedContact = bahman_emdad_parse_contact_section($contact_section);
                $branch_addresses = $parsedContact['addresses'];
                $branch_map = $parsedContact['map'];
                // Fallback: raw HTML if no labeled rows were parsed
                if ($branch_addresses === [] && $branch_map === null) {
                    $rawDesc = trim((string) ($contact_section['values']['desc'] ?? ''));
                    if ($rawDesc !== '') {
                        $smarty->assign('branch_contact_html', $rawDesc);
                    }
                }

                // CMS multi-link field on contact section → CHC-style action buttons
                $contactSectionId = (int) ($contact_section['id'] ?? 0);
                $contactChildren = is_array($contact_section['child'] ?? null)
                    ? $contact_section['child']
                    : ($contactSectionId > 0 ? bahman_cms_children_grouped($contactSectionId) : []);
                foreach (bahman_cms_collect_links($contact_section, $contactChildren, $fileUrl) as $link) {
                    if (!is_array($link)) {
                        continue;
                    }
                    $linkHref = trim((string) ($link['href'] ?? ''));
                    if ($linkHref === '') {
                        continue;
                    }
                    $linkTitle = trim((string) ($link['title'] ?? ''));
                    $isRoute = mb_stripos($linkTitle, 'مسیریاب') !== false;
                    $isTel = (bool) preg_match('#^tel:#i', $linkHref);
                    $branch_contact_actions[] = [
                        'title' => $linkTitle !== '' ? $linkTitle : ($isRoute ? 'مسیریابی' : ($isTel ? 'تماس' : 'مشاهده')),
                        'href' => $linkHref,
                        'external' => (!$isTel) && (
                            $isRoute
                            || (bool) preg_match('#^(https?:)?//#i', $linkHref)
                        ),
                        'is_route' => $isRoute,
                        'is_tel' => $isTel,
                    ];
                }

                // If map was parsed from desc text and no explicit link covers it, keep as action
                if (is_array($branch_map) && trim((string) ($branch_map['href'] ?? '')) !== '') {
                    $mapHref = trim((string) $branch_map['href']);
                    $already = false;
                    foreach ($branch_contact_actions as $action) {
                        if (($action['href'] ?? '') === $mapHref) {
                            $already = true;
                            break;
                        }
                    }
                    if (!$already) {
                        array_unshift($branch_contact_actions, [
                            'title' => trim((string) ($branch_map['title'] ?? 'مسیریابی')) ?: 'مسیریابی',
                            'href' => $mapHref,
                            'external' => true,
                            'is_route' => true,
                            'is_tel' => false,
                        ]);
                    }
                }
            }

            $smarty->assign('branch', $branch_item);
            $smarty->assign('branch_history', $branch_history);
            $smarty->assign('branch_gallery', $branch_gallery);
            $smarty->assign('branch_videos', $branch_videos);
            $smarty->assign('branch_faq', $branch_faq);
            $smarty->assign('branch_addresses', $branch_addresses);
            $smarty->assign('branch_map', $branch_map);
            $smarty->assign('branch_contact_actions', $branch_contact_actions);
            $smarty->assign('slider_links', $slider_links);
            $smarty->assign('company_has_products', false);
            $smarty->assign('brand_id', null);

            $branchTitle = trim((string) ($branch_item['values']['title'] ?? ''));
            if ($branchTitle !== '') {
                $page_title = $branchTitle . ' | گروه بهمن';
                $page_description = $branchTitle . '؛ معرفی خدمات، گالری و اطلاعات تماس در وب‌سایت گروه بهمن.';
            }
            $metaDesc = bahman_cms_meta_excerpt(
                (string) ($branch_item['values']['desc'] ?? $branch_item['values']['content'] ?? '')
            );
            if ($metaDesc !== '') {
                $page_description = $metaDesc;
            }
            $emdadImage = bahman_cms_absolute_media_url(
                (string) ($branch_item['values']['banner'] ?? ''),
                $fileUrl,
                '1200'
            );
            if ($emdadImage !== '') {
                $og_image = $emdadImage;
                $og_image_alt = $branchTitle !== '' ? $branchTitle : 'امداد';
            }

            $siteUrl = defined('SITE_URL') ? rtrim((string) SITE_URL, '/') : '';
            if ($siteUrl !== '') {
                $canonical_url = $siteUrl . '/emdad';
            }

            unset($branch_item, $faq_sections, $contact_section, $content_sections);
            break;

        case 'show_page':
            if(!isset($_GET['page_id']) || !is_numeric($_GET['page_id']))
            {
                $template = '404';
                header("HTTP/1.1 404 Not Found");
                exit;
            }
            $template = 'page_accordion';

            $page = new CmsContent();
            $page
                ->setType('landing')
                ->setId($_GET['page_id'])
                ->setFields('title,banner,content,desc')
                ->setChild(['content_section'])
                ->setCount(1);
            $show_page = $page->execute();
            if ($show_page !== 404 && !empty($show_page[0])) {
                $item = $show_page[0];
                @$smarty->assign('show_page', $item);

                $pageName = trim((string) ($item['values']['title'] ?? ''));
                if ($pageName !== '') {
                    $page_title = $pageName . ' | گروه بهمن';
                }

                $pageDesc = bahman_cms_meta_excerpt($item['values']['desc'] ?? '');
                if ($pageDesc === '') {
                    $pageDesc = bahman_cms_meta_excerpt($item['values']['content'] ?? '');
                }
                if ($pageDesc === '' && !empty($item['child']['content_section']) && is_array($item['child']['content_section'])) {
                    $bits = [];
                    foreach ($item['child']['content_section'] as $sec) {
                        if (!is_array($sec)) {
                            continue;
                        }
                        $secTitle = trim((string) ($sec['values']['title'] ?? ''));
                        $secDesc = bahman_cms_meta_excerpt($sec['values']['desc'] ?? '', 120);
                        if ($secTitle !== '' && $secDesc !== '') {
                            $bits[] = $secTitle . ': ' . $secDesc;
                        } elseif ($secDesc !== '') {
                            $bits[] = $secDesc;
                        } elseif ($secTitle !== '') {
                            $bits[] = $secTitle;
                        }
                        if (mb_strlen(implode(' ', $bits)) >= 140) {
                            break;
                        }
                    }
                    $pageDesc = bahman_cms_meta_excerpt(implode(' ', $bits));
                }
                if ($pageDesc !== '') {
                    $page_description = $pageDesc;
                } elseif ($pageName !== '') {
                    $page_description = $pageName . ' — صفحه گروه بهمن';
                }

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $pageImage = bahman_cms_absolute_media_url(
                    (string) ($item['values']['banner'] ?? ''),
                    $fileUrl,
                    '1200'
                );
                if ($pageImage !== '') {
                    $og_image = $pageImage;
                    $og_image_alt = $pageName !== '' ? $pageName : 'گروه بهمن';
                }
                unset($item);
            } else {
                $template = '404';
                header("HTTP/1.1 404 Not Found");
            }
            unset($page);
            unset($show_page);

            break;
        case 'chc':
            $template = 'chc';
            $page_title = 'مرکز رضایت و خشنودی مشتریان (CHC) | گروه بهمن';
            $page_description = 'راه های ارتباطی مرکز رضایت و خشنودی مشتریان گروه بهمن؛ مرکز تماس، واحد امداد، ایمیل و اینستاگرام.';

            $chc = new CmsContent();
            $chc
                ->setType('landing')
                ->setId(3387)
                ->setChild(['content_section'])
                ->setCount(1);
            $chc_page = $chc->execute();
            unset($chc);

            $chcItem = null;
            $fileUrl = defined('FILE_URL') ? rtrim((string) FILE_URL, '/') : '';
            $chc_banner = '';
            if ($chc_page !== 404 && !empty($chc_page[0]) && is_array($chc_page[0])) {
                $chcItem = $chc_page[0];
                $smarty->assign('chc_page', $chcItem);

                $cmsTitle = trim((string) ($chcItem['values']['title'] ?? ''));
                if ($cmsTitle !== '') {
                    $page_title = $cmsTitle . ' | گروه بهمن';
                }

                $cmsDesc = bahman_cms_meta_excerpt((string) ($chcItem['values']['content'] ?? $chcItem['values']['desc'] ?? ''));
                if ($cmsDesc !== '') {
                    $page_description = $cmsDesc;
                }

                $chc_banner = bahman_cms_absolute_media_url(
                    (string) ($chcItem['values']['banner'] ?? ''),
                    $fileUrl,
                    '1920'
                );
                if ($chc_banner !== '') {
                    $og_image = bahman_cms_absolute_media_url(
                        (string) ($chcItem['values']['banner'] ?? ''),
                        $fileUrl,
                        '1200'
                    );
                    $og_image_alt = $cmsTitle !== '' ? $cmsTitle : 'مرکز رضایت و خشنودی مشتریان';
                }
            }
            $smarty->assign('chc_banner', $chc_banner);
            unset($chc_page);

            $sectionsByKey = [
                'section-1' => [],
                'section-2' => [],
                'section-3' => [],
            ];
            if ($chcItem !== null) {
                $rawSections = $chcItem['child']['content_section'] ?? [];
                if (is_array($rawSections)) {
                    foreach ($rawSections as $section) {
                        if (!is_array($section)) {
                            continue;
                        }
                        $sectionKey = trim((string) ($section['values']['section_key'] ?? ''));
                        if (!isset($sectionsByKey[$sectionKey])) {
                            continue;
                        }
                        $sectionsByKey[$sectionKey][] = $section;
                    }
                    foreach ($sectionsByKey as $sectionKey => $list) {
                        $sectionsByKey[$sectionKey] = bahman_cms_sort_by_list_priority($list);
                    }
                }
            }

            $chcIsExternalHref = static function (string $href): bool {
                return (bool) preg_match('#^(https?:)?//#i', $href)
                    || (bool) preg_match('#^(mailto:|tel:)#i', $href);
            };

            $chcNormalizeLinks = static function (array $section) use ($fileUrl, $chcIsExternalHref): array {
                $sectionId = (int) ($section['id'] ?? 0);
                $sectionChildren = is_array($section['child'] ?? null)
                    ? $section['child']
                    : ($sectionId > 0 ? bahman_cms_children_grouped($sectionId) : []);
                $links = bahman_cms_collect_links($section, $sectionChildren, $fileUrl);
                $normalized = [];
                foreach ($links as $link) {
                    if (!is_array($link)) {
                        continue;
                    }
                    $href = trim((string) ($link['href'] ?? ''));
                    $normalized[] = [
                        'title' => trim((string) ($link['title'] ?? '')),
                        'href' => $href,
                        'external' => $href !== '' && $chcIsExternalHref($href),
                    ];
                }
                return $normalized;
            };

            // Keep CMS HTML as authored (h2/h4/p/br). Only enhance plain emails/phones/URLs.
            $chcFormatChannelBody = static function (string $html, string $heading = ''): array {
                $raw = trim($html);
                if ($raw === '') {
                    return ['heading' => $heading, 'html' => '', 'lines' => []];
                }

                $out = $raw;

                // Autolink emails not already inside an attribute/tag
                $out = preg_replace_callback(
                    '/(?<!["\'>=\/])([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})(?![^<]*>)/u',
                    static function (array $m): string {
                        $email = $m[1];
                        return '<a href="mailto:' . htmlspecialchars($email, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">'
                            . htmlspecialchars($email, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</a>';
                    },
                    $out
                ) ?? $out;

                // Plain URLs (www. / http) not already linked
                $out = preg_replace_callback(
                    '#(?<!["\'>=])((?:https?://|www\.)[a-zA-Z0-9][^\s<]*)#u',
                    static function (array $m): string {
                        $display = $m[1];
                        $trailing = '';
                        if (preg_match('/^(.*?)([.,;:!?)\]]+)$/u', $display, $parts)) {
                            $display = $parts[1];
                            $trailing = $parts[2];
                        }
                        $href = html_entity_decode($display, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (!preg_match('#^https?://#i', $href)) {
                            $href = 'https://' . $href;
                        }
                        return '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                            . '" target="_blank" rel="noopener noreferrer" dir="ltr">'
                            . htmlspecialchars($display, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</a>' . $trailing;
                    },
                    $out
                ) ?? $out;

                return [
                    'heading' => $heading,
                    'html' => $out,
                    'lines' => [],
                ];
            };

            // section-1 → contact channel tabs
            $chc_channels = [];
            foreach ($sectionsByKey['section-1'] as $index => $section) {
                $title = bahman_cms_item_title($section);
                if ($title === '') {
                    continue;
                }
                $html = trim((string) ($section['values']['desc'] ?? ''));
                $heading = trim((string) ($section['values']['subtitle'] ?? $section['values']['overline'] ?? ''));
                $body = $chcFormatChannelBody($html, $heading);
                $links = $chcNormalizeLinks($section);

                $actions = [];
                foreach ($links as $link) {
                    $linkTitle = trim((string) ($link['title'] ?? ''));
                    $linkHref = trim((string) ($link['href'] ?? ''));
                    if ($linkHref === '') {
                        continue;
                    }
                    $isRoute = mb_stripos($linkTitle, 'مسیریاب') !== false;
                    $isTel = (bool) preg_match('#^tel:#i', $linkHref);
                    $actions[] = [
                        'title' => $linkTitle !== '' ? $linkTitle : ($isRoute ? 'مسیریابی' : ($isTel ? 'تماس' : 'مشاهده')),
                        'href' => $linkHref,
                        'external' => (!empty($link['external']) || $isRoute) && !$isTel,
                        'is_route' => $isRoute,
                        'is_tel' => $isTel,
                    ];
                }

                $chc_channels[] = [
                    'key' => 'chc-' . (int) ($section['id'] ?? ($index + 1)),
                    'title' => $title,
                    'heading' => $body['heading'],
                    'html' => $body['html'],
                    'lines' => $body['lines'],
                    'actions' => $actions,
                ];
            }
            $smarty->assign('chc_channels', $chc_channels);
            unset($chc_channels);

            // section-2 → service cards (CMS image, else default SVG)
            $tplBase = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost')
                ? 'http://localhost/baharcms_smarty_2025/httpdocs'
                : (defined('SITE_URL') ? rtrim((string) SITE_URL, '/') : '');
            $tplUrl = $tplBase . '/templates/' . (defined('TEMPLATE_NAME') ? TEMPLATE_NAME : 'bahman');
            $chcResolveCmsImage = static function (string $raw) use ($fileUrl): string {
                $raw = trim($raw);
                $raw = trim($raw, "\"'");
                if ($raw === '' || strcasecmp($raw, 'null') === 0 || strcasecmp($raw, 'undefined') === 0) {
                    return '';
                }
                if (preg_match('#^https?://#i', $raw)) {
                    return $raw;
                }
                // CMS file UUID → thumb
                if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $raw)) {
                    return $fileUrl !== '' ? $fileUrl . '/thumb/' . $raw . '/240' : '';
                }
                return '';
            };
            $cardImageFallbacks = [
                'images/chc/showroom.svg',
                'images/chc/demo-drive.svg',
                'images/chc/complaint.svg',
            ];

            $chc_cards = [];
            foreach ($sectionsByKey['section-2'] as $index => $section) {
                $title = bahman_cms_item_title($section);
                if ($title === '') {
                    continue;
                }
                $desc = trim(html_entity_decode(strip_tags((string) ($section['values']['desc'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $links = $chcNormalizeLinks($section);
                $firstLink = $links[0] ?? null;
                $button = is_array($firstLink) ? trim((string) ($firstLink['title'] ?? '')) : '';
                $href = is_array($firstLink) ? trim((string) ($firstLink['href'] ?? '')) : '';
                $external = is_array($firstLink) && !empty($firstLink['external']);

                $fallbackRel = $cardImageFallbacks[$index] ?? $cardImageFallbacks[0];
                $fallbackUrl = rtrim($tplUrl, '/') . '/' . ltrim($fallbackRel, '/');
                $cmsImage = $chcResolveCmsImage((string) ($section['values']['image'] ?? ''));

                $chc_cards[] = [
                    'image' => $cmsImage !== '' ? $cmsImage : $fallbackUrl,
                    'fallback' => $fallbackUrl,
                    'has_cms_image' => $cmsImage !== '',
                    'title' => $title,
                    'desc' => $desc,
                    'button' => $button !== '' ? $button : 'مشاهده',
                    'href' => $href,
                    'external' => $external,
                ];
            }
            $smarty->assign('chc_cards', $chc_cards);
            unset($chc_cards, $chcResolveCmsImage, $tplUrl, $tplBase, $cardImageFallbacks);

            // section-3 → accordion
            $chc_accordion = [];
            foreach ($sectionsByKey['section-3'] as $section) {
                $sectionTitle = bahman_cms_item_title($section);
                if ($sectionTitle === '') {
                    continue;
                }
                $chc_accordion[] = [
                    'title' => $sectionTitle,
                    'desc' => (string) ($section['values']['desc'] ?? ''),
                    'links' => $chcNormalizeLinks($section),
                ];
            }
            $smarty->assign('chc_accordion', $chc_accordion);
            unset(
                $chc_accordion,
                $chcItem,
                $fileUrl,
                $sectionsByKey,
                $chcNormalizeLinks,
                $chcIsExternalHref,
                $chcFormatChannelBody
            );

            break;
        case 'agency':
            $template = 'agency';
            $page_title = 'عاملیت‌ها | گروه بهمن';
            $page_description = 'جست‌وجوی عاملیت‌های مجاز فروش و خدمات پس از فروش گروه بهمن در سراسر کشور.';

            $agency = new CmsContent();
            $agency
                ->setType('landing')
                ->setId(252)
                ->setChild(['content_section'])
                ->setCount(1);
            $agency_page = $agency->execute();
            if ($agency_page !== 404) {
                @$smarty->assign('agency_page', $agency_page[0]);
            }
            unset($agency);
            unset($agency_page);

            $agency = new CmsContent();
            $agency
                ->setType('agency')
                ->setCount(-1);
            $agency_data = $agency->execute();
            unset($agency);

            $visibleAgencies = array_values(array_filter($agency_data, function ($item) {
                $statud = (string) ($item['values']['statud'] ?? $item['values']['status'] ?? '1');
                return $statud !== '0';
            }));

            $groupedAgencies = [];
            // نرمال‌کی → شمارش املاها + شهرها (فقط از مقادیر واردشده در ادمین)
            $provinceBuckets = [];

            foreach ($visibleAgencies as $item) {
                $province = trim((string) ($item['values']['province'] ?? ''));
                $city = trim((string) ($item['values']['city'] ?? ''));

                if ($province !== '') {
                    $provinceNorm = bahman_normalize_place_name($province);
                    if ($provinceNorm !== '') {
                        if (!isset($provinceBuckets[$provinceNorm])) {
                            $provinceBuckets[$provinceNorm] = [
                                'titles' => [],
                                'cities' => [],
                            ];
                        }
                        if (!isset($provinceBuckets[$provinceNorm]['titles'][$province])) {
                            $provinceBuckets[$provinceNorm]['titles'][$province] = 0;
                        }
                        $provinceBuckets[$provinceNorm]['titles'][$province]++;

                        if ($city !== '') {
                            $cityNorm = bahman_normalize_place_name($city);
                            if ($cityNorm !== '') {
                                if (!isset($provinceBuckets[$provinceNorm]['cities'][$cityNorm])) {
                                    $provinceBuckets[$provinceNorm]['cities'][$cityNorm] = [];
                                }
                                if (!isset($provinceBuckets[$provinceNorm]['cities'][$cityNorm][$city])) {
                                    $provinceBuckets[$provinceNorm]['cities'][$cityNorm][$city] = 0;
                                }
                                $provinceBuckets[$provinceNorm]['cities'][$cityNorm][$city]++;
                            }
                        }
                    }
                }

                $companyIds = [];
                if (!empty($item['values']['company'])) {
                    $companyIds = array_filter(array_map('trim', explode(',', $item['values']['company'])));
                }
                if (empty($companyIds)) {
                    $companyIds = ['0'];
                }

                foreach ($companyIds as $companyId) {
                    if (!isset($groupedAgencies[$companyId])) {
                        $groupedAgencies[$companyId] = [];
                    }
                    $groupedAgencies[$companyId][] = $item;
                }
            }

            $provinces = [];
            $provinceCities = [];
            foreach ($provinceBuckets as $bucket) {
                $provinceLabel = bahman_pick_place_label($bucket['titles']);
                if ($provinceLabel === '') {
                    continue;
                }
                $provinces[] = $provinceLabel;
                $citiesForProvince = [];
                foreach ($bucket['cities'] as $cityVariants) {
                    $cityLabel = bahman_pick_place_label($cityVariants);
                    if ($cityLabel !== '') {
                        $citiesForProvince[$cityLabel] = $cityLabel;
                    }
                }
                $provinceCities[$provinceLabel] = $citiesForProvince;
            }
            unset($provinceBuckets);

            // مرتب‌سازی الفبایی استان‌ها و شهرها (فارسی)
            if (class_exists('\\Collator')) {
                $faCollator = new \Collator('fa_IR');
                $faCollator->sort($provinces);
                $sortedProvinceCities = [];
                foreach ($provinces as $provName) {
                    $cityList = array_values($provinceCities[$provName] ?? []);
                    $faCollator->sort($cityList);
                    $sortedCities = [];
                    foreach ($cityList as $cityName) {
                        $sortedCities[$cityName] = $cityName;
                    }
                    $sortedProvinceCities[$provName] = $sortedCities;
                }
                $provinceCities = $sortedProvinceCities;
                unset($faCollator, $sortedProvinceCities);
            } else {
                sort($provinces, SORT_STRING);
                foreach ($provinceCities as $provKey => $citiesMap) {
                    $cityList = array_values($citiesMap);
                    sort($cityList, SORT_STRING);
                    $sortedCities = [];
                    foreach ($cityList as $cityName) {
                        $sortedCities[$cityName] = $cityName;
                    }
                    $provinceCities[$provKey] = $sortedCities;
                }
            }

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

            $product = new CmsContent();
            $product
                ->setType('product')
                ->setFields("title")
                ->setCount(-1);
            $product_list = $product->execute();
            unset($product);

            $smarty->assign('agency', $visibleAgencies);
            $smarty->assign('agency_grouped', $groupedAgencies);
            $smarty->assign('agency_companies', $agencyCompanies);
            $smarty->assign('agency_provinces', $provinces);
            $smarty->assign('agency_province_cities_json', json_encode($provinceCities, JSON_UNESCAPED_UNICODE));
            $smarty->assign('product', $product_list);

            break;
        case 'agency_request':
            $template = 'agency_request';
            $page_title = 'درخواست عاملیت | گروه بهمن';
            $page_description = 'فرم درخواست اخذ عاملیت مجاز فروش و خدمات پس از فروش گروه بهمن.';

            $bp_provinces = ExecuteRows("SELECT id, title FROM bp_province WHERE IFNULL(title, '') <> '' ORDER BY title");
            $bp_cities = ExecuteRows("SELECT id, bp_province_id, title FROM bp_city WHERE IFNULL(title, '') <> '' ORDER BY title");

            $province_list = [];
            if (is_array($bp_provinces)) {
                foreach ($bp_provinces as $row) {
                    $province_list[] = [
                        'id' => (int) $row['id'],
                        'name' => $row['title'],
                    ];
                }
            }

            $city_list = [];
            if (is_array($bp_cities)) {
                foreach ($bp_cities as $row) {
                    $city_list[] = [
                        'name' => $row['title'],
                        'province_id' => (int) $row['bp_province_id'],
                    ];
                }
            }

            $smarty->assign('bp_provinces_json', json_encode($province_list, JSON_UNESCAPED_UNICODE));
            $smarty->assign('bp_cities_json', json_encode($city_list, JSON_UNESCAPED_UNICODE));
            unset($bp_provinces, $bp_cities, $province_list, $city_list);
            break;
        case 'contact_us':
            $template = 'contact_us';
            $page_title = 'تماس با ما | گروه بهمن';
            $page_description = 'راه‌های ارتباطی با گروه بهمن؛ ارسال پیام، آدرس و اطلاعات تماس.';

            // صفحه تماس با ما در ادمین (landing id=2936) — سکشن‌ها = تب‌ها
            $contact_tabs = [];
            $contact_page_obj = new CmsContent();
            $contact_page_obj
                ->setType('landing')
                ->setId(2936)
                ->setChild(['content_section'])
                ->setCount(1);
            $contact_page_raw = $contact_page_obj->execute();
            unset($contact_page_obj);

            if ($contact_page_raw !== 404 && !empty($contact_page_raw[0]) && is_array($contact_page_raw[0])) {
                $contact_page = $contact_page_raw[0];
                $smarty->assign('contact_page', $contact_page);

                $cmsTitle = trim((string) ($contact_page['values']['title'] ?? ''));
                if ($cmsTitle !== '') {
                    $page_title = $cmsTitle . ' | گروه بهمن';
                }

                $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
                $sections = $contact_page['child']['content_section'] ?? [];
                if (is_array($sections)) {
                    foreach ($sections as $section) {
                        if (!is_array($section)) {
                            continue;
                        }
                        $sectionTitle = bahman_cms_item_title($section);
                        if ($sectionTitle === '') {
                            continue;
                        }
                        $sectionId = (int) ($section['id'] ?? 0);
                        $sectionChildren = is_array($section['child'] ?? null)
                            ? $section['child']
                            : ($sectionId > 0 ? bahman_cms_children_grouped($sectionId) : []);
                        $links = bahman_cms_collect_links($section, $sectionChildren, $fileUrl);
                        $contact_tabs[] = [
                            'id' => $sectionId,
                            'key' => 'sec' . ($sectionId > 0 ? $sectionId : count($contact_tabs) + 1),
                            'title' => $sectionTitle,
                            'desc' => (string) ($section['values']['desc'] ?? ''),
                            'links' => $links,
                        ];
                    }
                }
                unset($contact_page, $sections, $fileUrl, $cmsTitle);
            }
            unset($contact_page_raw);

            $smarty->assign('contact_tabs', $contact_tabs);
            unset($contact_tabs);
            break;
        case 'brand':
            $template = 'company';
            $page_title = 'شرکت | گروه بهمن';

            if (!isset($_GET['brand_id']) || !is_numeric($_GET['brand_id'])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header("HTTP/1.1 404 Not Found");
                break;
            }

            $brand_id = (int) $_GET['brand_id'];
            $smarty->assign('brand_id', $brand_id);

            $branch = new CmsContent();
            $branch
                ->setType('company')
                ->setId($brand_id)
                ->setFields('title,image,banner,address_details,video')
                // Field key on company is `list_tree`; nested content type key is `listtreeparent`
                ->setChild(['content_section', 'gallery', 'listtreeparent'])
                ->setCount(1);
            $branch_data = $branch->execute();
            unset($branch);

            if ($branch_data === 404 || empty($branch_data[0])) {
                $template = '404';
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
                header("HTTP/1.1 404 Not Found");
                break;
            }

            $branch_item = $branch_data[0];
            unset($branch_data);

            $branch_history = [];
            $content_sections = $branch_item['child']['content_section'] ?? [];
            if (is_array($content_sections)) {
                foreach ($content_sections as $section) {
                    $values = is_array($section['values'] ?? null) ? $section['values'] : [];
                    // Accordion items moved to list_tree — keep content_section for history only
                    $section_key = trim((string) ($values['section_key'] ?? ''));
                    if ($section_key !== '') {
                        continue;
                    }

                    $desc = trim(strip_tags((string) ($values['desc'] ?? '')));
                    $has_image = trim((string) ($values['image'] ?? '')) !== '';
                    if ($desc !== '' || $has_image) {
                        $branch_history[] = $section;
                    }
                }
            }

            $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
            if ($fileUrl === '' && isset($admin_setting) && is_array($admin_setting)) {
                $fileUrl = (string) ($admin_setting['file_url'] ?? '');
            }
            $list_tree_raw = $branch_item['child']['listtreeparent'] ?? [];
            $branch_accordions = [];
            if (is_array($list_tree_raw) && $list_tree_raw !== [] && $list_tree_raw !== 404) {
                $branch_accordions = bahman_build_list_tree_accordions($list_tree_raw, $fileUrl);
            }

            $branch_gallery = [];
            $gallery_groups = $branch_item['child']['gallery'] ?? [];
            if (is_array($gallery_groups)) {
                foreach ($gallery_groups as $group) {
                    $images_raw = (string) ($group['values']['images'] ?? '');
                    $images = array_values(array_filter(array_map('trim', explode(',', $images_raw))));
                    if (empty($images)) {
                        continue;
                    }
                    $branch_gallery[] = [
                        'id' => $group['id'] ?? null,
                        'title' => trim((string) ($group['values']['title'] ?? '')),
                        'images' => $images,
                    ];
                }
            }

            // Multi video field on company (same shape as event.video: comma-separated UUIDs)
            $branch_videos = [];
            $companyVideoRaw = (string) ($branch_item['values']['video'] ?? '');
            if ($companyVideoRaw !== '') {
                $companyVideoIds = array_values(array_filter(array_map('trim', explode(',', $companyVideoRaw))));
                foreach ($companyVideoIds as $videoUuid) {
                    $streamUrl = bahman_cms_video_stream_url($videoUuid);
                    if ($streamUrl !== '') {
                        $branch_videos[] = $streamUrl;
                    }
                }
            }

            $branch_addresses = [];
            $branch_map = null;
            $address_raw = $branch_item['values']['address_details'] ?? '';
            $address_list = is_array($address_raw) ? $address_raw : json_decode((string) $address_raw, true);
            if (is_array($address_list)) {
                foreach ($address_list as $address) {
                    if (!is_array($address)) {
                        continue;
                    }
                    if ((int) ($address['status'] ?? 1) !== 1) {
                        continue;
                    }

                    $title = trim((string) ($address['title'] ?? ''));
                    $value = trim((string) ($address['value'] ?? ''));
                    if ($title === '') {
                        continue;
                    }

                    $title_norm = str_replace(['ي', 'ك'], ['ی', 'ک'], $title);

                    if (mb_strpos($title_norm, 'مسیریاب') !== false) {
                        $branch_map = [
                            'title' => $title,
                            'href' => $value !== '' ? $value : '#',
                        ];
                        continue;
                    }

                    if ($value === '') {
                        continue;
                    }

                    $type = 'text';
                    $href = '';
                    if (mb_strpos($title_norm, 'ایمیل') !== false || filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $type = 'email';
                        $href = 'mailto:' . $value;
                    } elseif (mb_strpos($title_norm, 'تماس') !== false || mb_strpos($title_norm, 'تلفن') !== false) {
                        $type = 'tel';
                        $tel = preg_replace('/[^\d+]/', '', strtr($value, [
                            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
                            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
                        ]));
                        $href = $tel !== '' ? 'tel:' . $tel : '';
                    } elseif (
                        mb_strpos($title_norm, 'شکایت') !== false
                        || preg_match('#^https?://#i', $value)
                        || preg_match('#^[a-z0-9.-]+\.[a-z]{2,}(/.*)?$#i', $value)
                    ) {
                        $type = 'url';
                        $href = preg_match('#^https?://#i', $value) ? $value : 'https://' . $value;
                    }

                    $branch_addresses[] = [
                        'title' => $title,
                        'value' => $value,
                        'type' => $type,
                        'href' => $href,
                    ];
                }
            }

            $smarty->assign('branch', $branch_item);
            $smarty->assign('branch_history', $branch_history);
            $smarty->assign('branch_accordions', $branch_accordions);
            $smarty->assign('branch_gallery', $branch_gallery);
            $smarty->assign('branch_videos', $branch_videos);
            $smarty->assign('branch_addresses', $branch_addresses);
            $smarty->assign('branch_map', $branch_map);
            $smarty->assign('company_has_products', bahman_company_has_products($brand_id));

            $branchTitle = trim((string) ($branch_item['values']['title'] ?? ''));
            if ($branchTitle !== '') {
                $page_title = $branchTitle . ' | گروه بهمن';
                $page_description = $branchTitle . '؛ معرفی شرکت، محصولات و اطلاعات مرتبط در وب‌سایت گروه بهمن.';
            } else {
                $page_description = 'معرفی شرکت‌های زیرمجموعه گروه بهمن.';
            }
            $brandImage = bahman_cms_absolute_media_url(
                (string) ($branch_item['values']['banner'] ?? $branch_item['values']['image'] ?? ''),
                $fileUrl,
                '1200'
            );
            if ($brandImage !== '') {
                $og_image = $brandImage;
                $og_image_alt = $branchTitle !== '' ? $branchTitle : 'گروه بهمن';
            }
            unset($branch_item);
            break;
        case 'about_us':
            $template = 'about_us';
            
            $page = new CmsContent();
            $page
                ->setType('landing')
                ->setId(402)
                ->setFields('title,banner,content,desc')
                ->setChild(['content_section'])
                ->setCount(1);
            $about_us = $page->execute();

            if ($about_us == 404) {
                $template = '404';
                header("HTTP/1.1 404 Not Found");
                $page_title = 'صفحه پیدا نشد | گروه بهمن';
            } else {
                $aboutItem = $about_us[0];
                $aboutTitle = trim((string) ($aboutItem['values']['title'] ?? ''));
                $page_title = ($aboutTitle !== '' ? $aboutTitle : 'درباره ما') . ' | گروه بهمن';

                $aboutDesc = bahman_cms_meta_excerpt($aboutItem['values']['desc'] ?? '');
                if ($aboutDesc === '') {
                    $aboutDesc = bahman_cms_meta_excerpt($aboutItem['values']['content'] ?? '');
                }
                if ($aboutDesc === '' && !empty($aboutItem['child']['content_section']) && is_array($aboutItem['child']['content_section'])) {
                    $bits = [];
                    foreach ($aboutItem['child']['content_section'] as $sec) {
                        if (!is_array($sec)) {
                            continue;
                        }
                        $secTitle = trim((string) ($sec['values']['title'] ?? ''));
                        $secDesc = bahman_cms_meta_excerpt($sec['values']['desc'] ?? '', 120);
                        if ($secTitle !== '' && $secDesc !== '') {
                            $bits[] = $secTitle . ': ' . $secDesc;
                        } elseif ($secDesc !== '') {
                            $bits[] = $secDesc;
                        } elseif ($secTitle !== '') {
                            $bits[] = $secTitle;
                        }
                        if (mb_strlen(implode(' ', $bits)) >= 140) {
                            break;
                        }
                    }
                    $aboutDesc = bahman_cms_meta_excerpt(implode(' ', $bits));
                }
                if ($aboutDesc !== '') {
                    $page_description = $aboutDesc;
                } else {
                    $page_description = ($aboutTitle !== '' ? $aboutTitle . ' — ' : '') . 'آشنایی با گروه بهمن، تاریخچه و چشم‌انداز مجموعه.';
                }
                unset($aboutItem);
            }

            // var_dump($about_us[0]);
            // exit;

            @$smarty->assign('about_us', $about_us[0]);
            unset($page);
            unset($about_us);
            break;
        case 'search':
            $template = 'search';
            $search_query = trim((string) ($_GET['q'] ?? ''));
            $search_results = bahman_cms_search_site($search_query);
            $search_total = count($search_results);

            if ($search_query !== '') {
                $page_title = 'نتایج جستجو برای «' . $search_query . '» | گروه بهمن';
                $page_description = $search_total > 0
                    ? ('حدود ' . $search_total . ' نتیجه برای «' . $search_query . '» در وب‌سایت گروه بهمن.')
                    : ('نتیجه‌ای برای «' . $search_query . '» پیدا نشد.');
            } else {
                $page_title = 'جستجو | گروه بهمن';
                $page_description = 'جستجو در محصولات، اخبار، بلاگ، رویدادها، صفحات، عاملیت‌ها و بخشنامه‌های گروه بهمن.';
            }

            $type_counts = [
                'all' => $search_total,
                'product' => 0,
                'article' => 0,
                'blog' => 0,
                'event' => 0,
                'video' => 0,
                'brand' => 0,
                'page' => 0,
                'document' => 0,
                'agency' => 0,
                'directive' => 0,
            ];
            foreach ($search_results as $item) {
                $key = (string) ($item['type'] ?? '');
                if (isset($type_counts[$key])) {
                    $type_counts[$key]++;
                }
            }

            $smarty->assign('search_query', $search_query);
            $smarty->assign('search_results', $search_results);
            $smarty->assign('search_total', $search_total);
            $smarty->assign('search_type_counts', $type_counts);
            $smarty->assign('search_page_size', 8);
            unset($search_results, $type_counts, $search_query);
            break;
        case 'showroom':
            $template = 'showroom';
            $page_title = 'نمایشگاه‌های گروه بهمن | گروه بهمن';
            $page_description = 'معرفی نمایشگاه‌های گروه بهمن؛ آدرس، اطلاعات تماس و محصولات موجود در هر نمایشگاه.';

            $fileUrl = defined('FILE_URL') ? (string) FILE_URL : '';
            if ($fileUrl === '' && isset($admin_setting) && is_array($admin_setting)) {
                $fileUrl = (string) ($admin_setting['file_url'] ?? '');
            }

            $showroomCms = new CmsContent();
            $showroomCms
                ->setType('showroom')
                ->setCount(-1);
            $showroomRaw = $showroomCms->execute();
            unset($showroomCms);
            if (!is_array($showroomRaw)) {
                $showroomRaw = [];
            }

            // Products with company for tab grouping
            $productCms = new CmsContent();
            $productCms
                ->setType('product')
                ->setFields('title,image,company')
                ->setCount(-1);
            $allProducts = $productCms->execute();
            unset($productCms);
            if (!is_array($allProducts)) {
                $allProducts = [];
            }
            $allProducts = bahman_cms_sort_by_list_priority($allProducts);

            $productLookup = [];
            foreach ($allProducts as $productItem) {
                $pid = (int) ($productItem['id'] ?? 0);
                if ($pid > 0) {
                    $productLookup[$pid] = $productItem;
                }
            }
            unset($allProducts);

            $companyCms = new CmsContent();
            $companyCms
                ->setType('company')
                ->setFields('title')
                ->setCount(-1);
            $companyList = $companyCms->execute();
            unset($companyCms);
            if (!is_array($companyList)) {
                $companyList = [];
            }
            $companyList = bahman_cms_sort_by_list_priority($companyList);
            $companyTitleById = [];
            foreach ($companyList as $comp) {
                $cid = (int) ($comp['id'] ?? 0);
                if ($cid <= 0) {
                    continue;
                }
                $companyTitleById[$cid] = trim((string) ($comp['values']['title'] ?? ''));
            }

            $showrooms = [];
            foreach ($showroomRaw as $item) {
                if (!is_array($item) || empty($item['values']) || !is_array($item['values'])) {
                    continue;
                }
                $values = $item['values'];
                $title = trim((string) ($values['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $specHtml = trim((string) ($values['spec'] ?? ''));

                $sectionId = (int) ($item['id'] ?? 0);
                $sectionChildren = is_array($item['child'] ?? null)
                    ? $item['child']
                    : ($sectionId > 0 ? bahman_cms_children_grouped($sectionId) : []);
                $collectedLinks = bahman_cms_collect_links($item, is_array($sectionChildren) ? $sectionChildren : [], $fileUrl);
                $routes = [];
                foreach ($collectedLinks as $linkRow) {
                    $href = trim((string) ($linkRow['href'] ?? ''));
                    if ($href === '') {
                        continue;
                    }
                    $routes[] = [
                        'title' => trim((string) ($linkRow['title'] ?? 'لینک')),
                        'href' => $href,
                    ];
                }

                $productIds = [];
                if (!empty($values['product'])) {
                    $productIds = array_values(array_filter(array_map(static function ($id) {
                        $id = trim((string) $id);
                        return ctype_digit($id) ? (int) $id : 0;
                    }, explode(',', (string) $values['product']))));
                }

                // Bucket selected products by company
                $byCompany = [];
                foreach ($productIds as $pid) {
                    $productItem = $productLookup[$pid] ?? null;
                    if (!is_array($productItem)) {
                        continue;
                    }
                    $pTitle = trim((string) ($productItem['values']['title'] ?? ''));
                    $pImage = trim((string) ($productItem['values']['image'] ?? ''));
                    if ($pTitle === '' || $pImage === '') {
                        continue;
                    }
                    $companyRaw = trim((string) ($productItem['values']['company'] ?? ''));
                    $companyIds = array_values(array_filter(array_map(static function ($id) {
                        $id = trim((string) $id);
                        return ctype_digit($id) ? (int) $id : 0;
                    }, $companyRaw !== '' ? explode(',', $companyRaw) : [])));
                    if ($companyIds === []) {
                        $companyIds = [0];
                    }
                    $card = [
                        'id' => $pid,
                        'title' => $pTitle,
                        'image' => $pImage,
                    ];
                    foreach ($companyIds as $cid) {
                        if (!isset($byCompany[$cid])) {
                            $byCompany[$cid] = [];
                        }
                        $byCompany[$cid][] = $card;
                    }
                }

                $companiesOut = [];
                foreach ($companyList as $comp) {
                    $cid = (int) ($comp['id'] ?? 0);
                    if ($cid <= 0 || empty($byCompany[$cid])) {
                        continue;
                    }
                    $compTitle = $companyTitleById[$cid] ?? '';
                    if ($compTitle === '') {
                        continue;
                    }
                    $companiesOut[] = [
                        'id' => $cid,
                        'title' => $compTitle,
                        'products' => $byCompany[$cid],
                    ];
                    unset($byCompany[$cid]);
                }
                // Orphan products without a known company
                if (!empty($byCompany[0])) {
                    $companiesOut[] = [
                        'id' => 0,
                        'title' => 'سایر',
                        'products' => $byCompany[0],
                    ];
                }

                $galleryImages = [];
                $picRaw = trim((string) ($values['pic_gallery'] ?? ''));
                if ($picRaw !== '') {
                    foreach (explode(',', $picRaw) as $picUuid) {
                        $picUuid = trim($picUuid);
                        if ($picUuid === '') {
                            continue;
                        }
                        $galleryImages[] = $picUuid;
                    }
                }

                $galleryVideos = [];
                $videoRaw = trim((string) ($values['video_gallery'] ?? ''));
                if ($videoRaw !== '') {
                    foreach (explode(',', $videoRaw) as $videoUuid) {
                        $videoUuid = trim($videoUuid);
                        if ($videoUuid === '') {
                            continue;
                        }
                        $streamUrl = bahman_cms_video_stream_url($videoUuid);
                        if ($streamUrl === '') {
                            continue;
                        }
                        $galleryVideos[] = [
                            'uuid' => $videoUuid,
                            'src' => $streamUrl,
                        ];
                    }
                }

                $galleryPayload = [
                    'images' => $galleryImages,
                    'videos' => $galleryVideos,
                ];
                $hasGallery = $galleryImages !== [] || $galleryVideos !== [];

                $showrooms[] = [
                    'id' => $sectionId,
                    'title' => $title,
                    'image' => trim((string) ($values['image'] ?? '')),
                    'spec' => $specHtml,
                    'routes' => $routes,
                    'companies' => $companiesOut,
                    // Omit empty gallery so the CTA is never shown without media
                    'gallery' => $hasGallery ? $galleryPayload : null,
                ];
            }
            unset($showroomRaw, $productLookup, $companyList, $companyTitleById, $fileUrl);

            $smarty->assign('showrooms', $showrooms);
            $smarty->assign('showrooms_json', json_encode($showrooms, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $smarty->assign('showroom_page_size', 6);
            unset($showrooms);
            break;
        default:
            $template = '404';
            $page_title = 'صفحه پیدا نشد | گروه بهمن';
            header("HTTP/1.1 404 Not Found");
            break;
    }

    if ($page_title === null && isset($template) && $template === '404') {
        $page_title = 'صفحه پیدا نشد | گروه بهمن';
    }

    if ($page_title !== null && $page_title !== '') {
        $smarty->assign('page_title', $page_title);
    }

    if ($page_description !== null && $page_description !== '') {
        $smarty->assign('page_description', $page_description);
    }

    if ($og_image !== null && $og_image !== '') {
        $smarty->assign('og_image', $og_image);
        if ($og_image_alt !== null && $og_image_alt !== '') {
            $smarty->assign('og_image_alt', $og_image_alt);
        }
        if (strpos($og_image, '/thumb/') !== false) {
            $smarty->assign('og_image_width', 1200);
            $smarty->assign('og_image_height', 1200);
        }
    }

    $smarty->assign('og_type', $og_type);

    if ($canonical_url === null || $canonical_url === '') {
        $siteBase = defined('SITE_URL') && SITE_URL ? rtrim((string) SITE_URL, '/') : '';
        if ($siteBase === '' && !empty($admin_setting['site_url'])) {
            $siteBase = rtrim((string) $admin_setting['site_url'], '/');
        }
        $reqPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $sitePath = (string) (parse_url($siteBase, PHP_URL_PATH) ?: '');
        if ($sitePath !== '' && $sitePath !== '/' && strpos($reqPath, $sitePath) === 0) {
            $suffix = substr($reqPath, strlen($sitePath));
            if ($suffix === false || $suffix === '') {
                $suffix = '/';
            }
            $canonical_url = $siteBase . $suffix;
        } else {
            $host = (string) (parse_url($siteBase, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? ''));
            $scheme = (string) (parse_url($siteBase, PHP_URL_SCHEME) ?: ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
            $canonical_url = $scheme . '://' . $host . $reqPath;
        }
    }
    $smarty->assign('canonical_url', $canonical_url);

    $smarty->assign('is_404', isset($template) && $template === '404');

    $product_category = new CmsContent();
    $product_category
        ->setType('product_category')
        ->setChildTable(['product:product_category'])
        ->setCount(-1)
        ->setFields("title");

    $product_category_list = $product_category->execute();
    unset($product_category);
    if (!is_array($product_category_list)) {
        $product_category_list = [];
    }

    // Filter products by direct product.company field (brand_id / company query param)
    $filter_brand_id = null;
    if (isset($_GET['include']) && $_GET['include'] === 'product') {
        if (isset($_GET['brand_id']) && is_numeric($_GET['brand_id'])) {
            $filter_brand_id = (int) $_GET['brand_id'];
        } elseif (isset($_GET['company']) && is_numeric($_GET['company'])) {
            $filter_brand_id = (int) $_GET['company'];
        }
    }

    if ($filter_brand_id && is_array($product_category_list)) {
        $branch_key = (string) $filter_brand_id;

        foreach ($product_category_list as $cat_index => $category) {
            if (empty($category['child_table']['product_category']) || !is_array($category['child_table']['product_category'])) {
                unset($product_category_list[$cat_index]);
                continue;
            }

            $filtered_products = [];
            foreach ($category['child_table']['product_category'] as $product_item) {
                $company_raw = (string) ($product_item['values']['company'] ?? '');
                if ($company_raw === '') {
                    continue;
                }
                $company_ids = array_filter(array_map('trim', explode(',', $company_raw)));
                if (in_array($branch_key, $company_ids, true)) {
                    $filtered_products[] = $product_item;
                }
            }

            if (empty($filtered_products)) {
                unset($product_category_list[$cat_index]);
                continue;
            }

            $product_category_list[$cat_index]['child_table']['product_category'] = $filtered_products;
        }

        $product_category_list = array_values($product_category_list);
        $smarty->assign('filter_branch_id', $filter_brand_id);
        $smarty->assign('product_filter_branch_id', $filter_brand_id);
    }

    // Categories by category priority; products inside each category by product priority
    $product_category_list = bahman_cms_sort_by_list_priority($product_category_list);
    foreach ($product_category_list as $cat_index => $category) {
        $products = $category['child_table']['product_category'] ?? null;
        if (!is_array($products) || $products === []) {
            continue;
        }
        $product_category_list[$cat_index]['child_table']['product_category'] = bahman_cms_sort_by_list_priority($products);
    }

    $smarty->assign('product_category', $product_category_list);
    unset($product_category_list);

    $company = new CmsContent();
    $company
        ->setType('company')
        ->setFields("title,image,show_in_menu")
        ->setCount(-1);
    $company_list = $company->execute();
    // var_dump($company_list);
    // exit;
    if (!is_array($company_list)) {
        $company_list = [];
    }
    $company_list = bahman_cms_sort_by_list_priority($company_list);
    $company_menu = array_values(array_filter($company_list, function ($item) {
        $flag = $item['values']['show_in_menu'] ?? '';
        if (is_array($flag)) {
            $flag = $flag['value'] ?? $flag[0] ?? '';
        }
        $flag = trim((string) $flag);
        // CMS radio: فعال / غیرفعال — also accept common truthy encodings
        return $flag === 'فعال'
            || $flag === '1'
            || strcasecmp($flag, 'true') === 0
            || strcasecmp($flag, 'yes') === 0;
    }));
    // فوتر / برندها: فقط show_in_menu=فعال | صفحه فروش: همه شرکت‌ها
    $smarty->assign('company', $company_menu);

    // در فروش: ترتیب تب‌ها = شرکت با جدیدترین بخشنامه اول
    $company_all = $company_list;
    if (isset($template) && $template === 'sales') {
        $companyById = [];
        foreach ($company_list as $comp) {
            $companyById[(string) ($comp['id'] ?? '')] = $comp;
        }

        if (!empty($groupedCirculars)) {
            $company_all = [];
            foreach ($groupedCirculars as $companyId => $_circulars) {
                $key = (string) $companyId;
                if (isset($companyById[$key])) {
                    $company_all[] = $companyById[$key];
                }
            }
        }

        // آرشیو غیرفعال: ترتیب شرکت‌ها = همان ترتیب ادمین (pin → priority)
        $sales_inactive_company_order = [];
        if (!empty($groupedInactiveCirculars)) {
            $usedCompanyIds = [];
            foreach ($company_list as $comp) {
                $key = (string) ($comp['id'] ?? '');
                if ($key === '' || !isset($groupedInactiveCirculars[$key])) {
                    continue;
                }
                $companyRow = $comp;
                $companyRow['inactive_count'] = count($groupedInactiveCirculars[$key]);
                $sales_inactive_company_order[] = $companyRow;
                $usedCompanyIds[$key] = true;
            }
            foreach ($groupedInactiveCirculars as $companyId => $_circulars) {
                $key = (string) $companyId;
                if ($key === '_other' || isset($usedCompanyIds[$key]) || isset($companyById[$key])) {
                    continue;
                }
                // company id در لیست ادمین نیست — انتهای آرشیو
                $sales_inactive_company_order[] = [
                    'id' => $key,
                    'values' => ['title' => 'سایر'],
                    'inactive_count' => is_array($_circulars) ? count($_circulars) : 0,
                ];
            }
            if (isset($groupedInactiveCirculars['_other'])) {
                $sales_inactive_company_order[] = [
                    'id' => '_other',
                    'values' => ['title' => 'سایر'],
                    'inactive_count' => count($groupedInactiveCirculars['_other']),
                ];
            }
            $smarty->assign('sales_inactive_company_order', $sales_inactive_company_order);
        }
    }
    $smarty->assign('company_all', $company_all);
    unset($company, $company_menu, $company_all);

    if (!empty($filter_brand_id) && is_array($company_list) && isset($template) && $template === 'product') {
        foreach ($company_list as $comp) {
            if ((int) ($comp['id'] ?? 0) === (int) $filter_brand_id) {
                $company_title = trim((string) ($comp['values']['title'] ?? ''));
                if ($company_title !== '') {
                    $smarty->assign('page_title', $company_title . ' | محصولات | گروه بهمن');
                    $smarty->assign('filter_branch_title', $company_title);
                }
                break;
            }
        }
    }
    unset($company_list);
    
    
    $menu = new CmsContent();
    $menu
        ->setType('menu')
        // ->setFields("title, section_key")
        ->setChild(['menu_category']);
    
    $menu_array = $menu->execute();

    // var_dump($menu_array);
    // exit;

    $result = [];

    foreach ($menu_array as $item) {
        // استخراج section_key
        $sectionKey = $item['values']['section_key'] ?? null;
        
        if (!$sectionKey) {
            continue; // اگر section_key وجود نداشت، رد می‌شویم
        }

        $result[$sectionKey]['title'] = $item['values']['title'] ?? null;
        
        // ایجاد کلید جدید برای section_key اگر وجود نداشته باشد
        if (!isset($result[$sectionKey])) {
            $result[$sectionKey] = [];
        }
        
        // بررسی وجود menu_category
        if (isset($item['child']['menu_category']) && is_array($item['child']['menu_category'])) {
            foreach ($item['child']['menu_category'] as $category) {
                // دیکود کردن JSON لینک‌ها
                $linksJson = $category['values']['link'] ?? '';
                $links = is_array($linksJson) ? $linksJson : json_decode($linksJson, true);
                if (is_string($links)) {
                    $links = json_decode($links, true);
                }
                
                if ($links && is_array($links)) {
                    $categoryData = [
                        'id' => $category['id'] ?? null,
                        'user_name' => $category['user_name'] ?? null,
                        'date' => $category['date'] ?? null,
                        'title' => $category['values']['title'] ?? null,
                        'links' => []
                    ];
                    
                    // اگر category وجود داشته باشد، اضافه می‌کنیم
                    if (isset($category['values']['category'])) {
                        $categoryData['category'] = $category['values']['category'];
                    }
                    
                    // فقط لینک‌های فعال (status === 1) — status=0 در CMS مخفی است
                    foreach ($links as $link) {
                        if (!is_array($link)) {
                            continue;
                        }
                        if ((int) ($link['status'] ?? 1) !== 1) {
                            continue;
                        }

                        $categoryData['links'][] = [
                            'title' => $link['title'] ?? '',
                            'href' => $link['href'] ?? '',
                            'attachment' => $link['attachment'] ?? '',
                            'icon' => $link['title'] ?? '',
                            'status' => (int) ($link['status'] ?? 1),
                        ];
                    }
                    
                    // فقط اگر لینک معتبر داشت اضافه کنیم
                    if (!empty($categoryData['links'])) {
                        $result[$sectionKey][] = $categoryData;
                    }
                }
            }
        }
    }

    // var_dump($result);
    // exit;
    
    $smarty->assign('menu', $result);
    unset($menu);
    unset($menu_array);
    unset($result);
}

$smarty->assign('has_active_sale', bahman_has_active_sale());