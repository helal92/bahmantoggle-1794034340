<?php

namespace PHPMaker2020\adoura_base_api;

// PHPMaker is not PHP 8.1+/8.2-clean; hide deprecations so they don't break headers/session.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '1');

// FAST_MODE: serve cached /sales HTML before session/CMS (reversible via .FAST_MODE flag).
$bahmanFastMode = is_file(__DIR__ . '/.FAST_MODE')
	|| is_file('/var/www/vhosts/siteapi.bahman.ir/httpdocs/file_manager/.FAST_MODE');
$bahmanPageCacheFile = null;
$bahmanTmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);

// Product mega-menu fragment: always cache 5 minutes (public catalog).
if (isset($_GET['include']) && $_GET['include'] === 'product_menu') {
	$bahmanPageCacheFile = $bahmanTmpDir . '/bahman_product_menu_fragment_v2.html';
	$bahmanPageCacheTtl = 300;
	if (is_file($bahmanPageCacheFile) && (time() - (int) filemtime($bahmanPageCacheFile)) < $bahmanPageCacheTtl) {
		header('Content-Type: text/html; charset=UTF-8');
		header('X-Bahman-Cache: HIT');
		header('Cache-Control: public, max-age=300');
		readfile($bahmanPageCacheFile);
		exit;
	}
}

// Homepage + sales HTML cache (FAST_MODE): skip session/CMS on HIT.
if (isset($_GET['include']) && ($_GET['include'] === 'sales' || $_GET['include'] === 'index')) {
	$bahmanPageCacheFile = $bahmanTmpDir . '/bahman_' . ($_GET['include'] === 'index' ? 'home' : 'sales') . '_page_v4.html';
	$bahmanPageCacheTtl = 90;
	$allowPageCache = $bahmanFastMode || $_GET['include'] === 'index';
	if ($allowPageCache && is_file($bahmanPageCacheFile) && (time() - (int) filemtime($bahmanPageCacheFile)) < $bahmanPageCacheTtl) {
		header('Content-Type: text/html; charset=UTF-8');
		header('X-Bahman-Cache: HIT');
		header('Cache-Control: public, max-age=60');
		readfile($bahmanPageCacheFile);
		exit;
	}
	if (!$allowPageCache) {
		$bahmanPageCacheFile = null;
	}
}

// Autoload
include_once "autoload.php";

// Session
if (session_status() !== PHP_SESSION_ACTIVE)
	\Delight\Cookie\Session::start(Config("COOKIE_SAMESITE")); // Init session data

use Smarty\Smarty;

$smarty = new Smarty();
$smarty->setTemplateDir('templates');
$smarty->setConfigDir('config');
$smarty->setCompileDir('templates_c');
$smarty->setCacheDir('cache');
$smarty->registerPlugin(
    'modifier',
    'json_decode',
    function ($string, $assoc = true) {
        return json_decode($string, $assoc);
    }
);
$smarty->registerPlugin(
    'modifier',
    'is_url',
    function ($string) {
        if (!is_string($string)) {
            return false;
        }
        
        $trimmed = trim($string);
        
        // بررسی دقیق‌تر برای URL
        return (bool)preg_match('/^https?:\/\/[^\s]+$/', $trimmed) ||
               (strpos($string, 'http://') !== false) ||
               (strpos($string, 'https://') !== false);
    }
);
// در فایل bootstrap یا جایی که plugins رو ثبت می‌کنید
$smarty->registerPlugin(
    'modifier',
    'detect_file_type',
    function ($url) {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return 'unknown';
        }
        
        // کش ساده برای جلوگیری از درخواست‌های مکرر
        static $cache = [];
        $cacheKey = md5($url);
        
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }
        
        $result = 'unknown';
        
        try {
            // استفاده از cURL برای HEAD request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_NOBODY => true,          // HEAD request
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => 3,           // تایم‌اوت ۳ ثانیه
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_USERAGENT => 'Mozilla/5.0',
                CURLOPT_SSL_VERIFYPEER => false // اگر SSL مشکل دارد
            ]);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                // اگر خطا داشت، سعی کن GET کنی (آهسته‌تر)
                curl_setopt($ch, CURLOPT_NOBODY, false);
                curl_setopt($ch, CURLOPT_RANGE, '0-100'); // فقط 100 بایت اول
                $response = curl_exec($ch);
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            
            curl_close($ch);
            
            if ($httpCode === 200 && !empty($contentType)) {
                if (stripos($contentType, 'image/') === 0) {
                    $result = 'image';
                } elseif (stripos($contentType, 'video/') === 0) {
                    $result = 'video';
                } elseif (stripos($contentType, 'application/pdf') !== false) {
                    $result = 'pdf';
                } elseif (stripos($contentType, 'application/msword') !== false || 
                         stripos($contentType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') !== false) {
                    $result = 'doc';
                } elseif (stripos($contentType, 'text/') === 0) {
                    $result = 'text';
                } else {
                    $result = 'file';
                }
            }
        } catch (Exception $e) {
            // خطا را نادیده بگیر
        }
        
        $cache[$cacheKey] = $result;
        return $result;
    }
);


// $smarty->escape_html = false;

$template = '';

include_once "libs/jdf.php";
include_once "libs/myfunc.php";
include_once "libs/myconf.php";
include_once "include/admin_setting.php";

// Per-template legacy redirects (optional). Example: templates/bahman/legacy_redirects.php
$legacyRedirectsFile = __DIR__ . '/templates/' . TEMPLATE_NAME . '/legacy_redirects.php';
if (is_file($legacyRedirectsFile)) {
	include_once $legacyRedirectsFile;
	$legacyApplyFn = __NAMESPACE__ . '\\' . TEMPLATE_NAME . '_apply_legacy_redirects';
	if (function_exists($legacyApplyFn)) {
		$legacyApplyFn();
	}
}

include_once "include/cms_contents.php";

$smarty->registerPlugin(
    'modifier',
    'jdate',
    function ($date, $format = 'Y/m/d') {
        if ($date === null || $date === '') {
            return '';
        }
        $timestamp = is_numeric($date) ? (int) $date : strtotime((string) $date);
        if (!$timestamp) {
            return '';
        }
        return jdate($format, $timestamp, '', 'Asia/Tehran', 'fa');
    }
);

require_once('templates/' . TEMPLATE_NAME . '/config.php');

$smarty->registerPlugin(
    'modifier',
    'date_only',
    function ($value) {
        return bahman_cms_directive_start_date_label($value);
    }
);

if ($bahmanPageCacheFile && ($template === 'sales' || $template === 'product_menu_fragment' || $template === 'index')) {
	ob_start();
	$smarty->display('templates/' . TEMPLATE_NAME . '/' . $template . '.tpl');
	$html = ob_get_clean();
	@file_put_contents($bahmanPageCacheFile, $html, LOCK_EX);
	header('Content-Type: text/html; charset=UTF-8');
	header('X-Bahman-Cache: MISS');
	header('Cache-Control: public, max-age=' . ($template === 'product_menu_fragment' ? '300' : '60'));
	echo $html;
	exit;
}

if ($template === 'product_menu_fragment') {
	header('Content-Type: text/html; charset=UTF-8');
	header('Cache-Control: public, max-age=300');
	$smarty->display('templates/' . TEMPLATE_NAME . '/' . $template . '.tpl');
	exit;
}

$smarty->display('templates/' . TEMPLATE_NAME . '/' . $template . '.tpl');
