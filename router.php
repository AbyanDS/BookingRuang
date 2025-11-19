<?php
/**
 * Router untuk PHP Built-in Server (Railway)
 * File ini menangani routing untuk PHP built-in server
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Jika file statis ada (css, js, images), serve langsung
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Jika file PHP ada, include file tersebut
if (preg_match('/\.php$/', $uri)) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        return false;
    }
}

// Default ke index.php
if ($uri === '/') {
    include __DIR__ . '/index.php';
    exit;
}

// File tidak ditemukan
http_response_code(404);
echo "404 - File Not Found";
?>
