<?php
// Application configuration
// Update APP_NAME if your project has a different title.

date_default_timezone_set('Africa/Dar_es_Salaam');

define('APP_NAME', 'Mzumbe University Voting System');

// Auto-detect base URL so the project works in XAMPP subfolders and free hosting.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('#/(admin|voter|api|setup)$#', '', $scriptDir);
$basePath = rtrim($basePath, '/');

define('APP_URL', $scheme . '://' . $host . $basePath);

function app_url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}
