<?php

function base_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = str_replace('\\', '/', dirname($scriptName));
    if ($base === '/' || $base === '.') {
        return '';
    }
    return rtrim($base, '/');
}

function route_url(string $path, array $params = []): string
{
    $path = ltrim($path, '/');
    if (APP_CONFIG['use_query_routes']) {
        $query = array_merge(['route' => $path], $params);
        return base_url() . '/index.php?' . http_build_query($query);
    }
    $url = base_url() . '/' . $path;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function redirect_to(string $path, array $params = []): void
{
    header('Location: ' . route_url($path, $params));
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash_set(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }
    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $value;
}
