<?php
/**
 * On-demand builder for /sales-img/{uuid}_{w}.{ext}
 * If the static cache file is missing, fetch from sitefile, resize, save, then serve.
 * Wired by .htaccess only when the file does not already exist.
 */

$uuid = isset($_GET['uuid']) ? trim((string) $_GET['uuid']) : '';
$w = isset($_GET['w']) ? (int) $_GET['w'] : 0;
$ext = isset($_GET['ext']) ? strtolower(trim((string) $_GET['ext'])) : 'png';

if (!preg_match('/^[a-f0-9\-]{36}$/i', $uuid) || !in_array($w, [600, 960, 1920], true) || !in_array($ext, ['png', 'jpg'], true)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found';
    exit;
}

$root = dirname(__DIR__);
$dir = $root . '/sales-img';
$dest = $dir . '/' . $uuid . '_' . $w . '.' . $ext;
$destPng = $dir . '/' . $uuid . '_' . $w . '.png';

if (is_file($dest) && filesize($dest) > 500) {
    serve_file($dest, $ext);
}

if ($ext === 'jpg' && is_file($destPng) && filesize($destPng) > 500) {
    // Prefer converting existing png cache to jpg once.
    if (convert_and_save($destPng, $dest, $w, $ext)) {
        serve_file($dest, $ext);
    }
}

if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}

$tmp = sys_get_temp_dir() . '/sales_img_' . $uuid . '_' . $w . '_' . getmypid() . '.bin';
@unlink($tmp);

$sources = [
    'https://sitefile.bahman.ir/thumb/' . rawurlencode($uuid) . '/' . $w,
    'https://sitefile.bahman.ir/' . rawurlencode($uuid),
];

$raw = null;
foreach ($sources as $url) {
    $raw = fetch_url($url);
    if ($raw !== null && looks_like_image($raw)) {
        break;
    }
    $raw = null;
}

if ($raw === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found';
    exit;
}

file_put_contents($tmp, $raw);
$ok = convert_and_save($tmp, $dest, $w, $ext);
@unlink($tmp);

if (!$ok || !is_file($dest) || filesize($dest) < 500) {
    // Last resort: store original bytes with requested name.
    if ($raw !== null && looks_like_image($raw)) {
        @file_put_contents($dest, $raw);
    }
}

if (!is_file($dest) || filesize($dest) < 500) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found';
    exit;
}

@chmod($dest, 0644);
serve_file($dest, $ext);

function serve_file(string $path, string $ext): void
{
    $ctype = $ext === 'jpg' ? 'image/jpeg' : 'image/png';
    header('Content-Type: ' . $ctype);
    header('Content-Length: ' . (string) filesize($path));
    header('Cache-Control: public, max-age=86400');
    header('X-Sales-Img: hit');
    readfile($path);
    exit;
}

function fetch_url(string $url): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'bahman-sales-img/1.0',
            CURLOPT_HTTPHEADER => ['Accept: image/*,*/*'],
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 300 && is_string($body) && strlen($body) > 500) {
            return $body;
        }
        return null;
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 40,
            'follow_location' => 1,
            'header' => "Accept: image/*\r\nUser-Agent: bahman-sales-img/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if (is_string($body) && strlen($body) > 500) {
        return $body;
    }
    return null;
}

function looks_like_image(string $bin): bool
{
    if (strncmp($bin, "\x89PNG\r\n\x1a\n", 8) === 0) {
        return true;
    }
    if (strncmp($bin, "\xFF\xD8\xFF", 3) === 0) {
        return true;
    }
    if (strncmp($bin, 'RIFF', 4) === 0 && substr($bin, 8, 4) === 'WEBP') {
        return true;
    }
    // Some gateways mislabel content-type; reject obvious HTML.
    $head = ltrim(substr($bin, 0, 200));
    if (stripos($head, '<!doctype') === 0 || stripos($head, '<html') === 0) {
        return false;
    }
    return false;
}

function convert_and_save(string $srcPath, string $destPath, int $w, string $ext): bool
{
    if (!function_exists('imagecreatefromstring')) {
        return @copy($srcPath, $destPath);
    }
    $bin = @file_get_contents($srcPath);
    if ($bin === false || $bin === '') {
        return false;
    }
    $im = @imagecreatefromstring($bin);
    if ($im === false) {
        return @copy($srcPath, $destPath);
    }

    $sw = imagesx($im);
    $sh = imagesy($im);
    if ($sw < 1 || $sh < 1) {
        imagedestroy($im);
        return false;
    }

    $nw = $w;
    $nh = (int) max(1, round($sh * ($nw / $sw)));
    $out = imagecreatetruecolor($nw, $nh);
    if ($out === false) {
        imagedestroy($im);
        return false;
    }

    if ($ext === 'png') {
        imagealphablending($out, false);
        imagesavealpha($out, true);
        $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefilledrectangle($out, 0, 0, $nw, $nh, $transparent);
    } else {
        $white = imagecolorallocate($out, 255, 255, 255);
        imagefilledrectangle($out, 0, 0, $nw, $nh, $white);
    }

    imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
    imagedestroy($im);

    $ok = false;
    if ($ext === 'jpg') {
        $ok = imagejpeg($out, $destPath, 85);
    } else {
        $ok = imagepng($out, $destPath, 6);
    }
    imagedestroy($out);
    return (bool) $ok && is_file($destPath) && filesize($destPath) > 500;
}
