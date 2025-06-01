<?php
// config.php

if (!file_exists(__DIR__ . '/.env')) {
    die('缺少 .env 配置文件');
}

$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue; // 跳过注释行

    list($name, $value) = explode('=', $line, 2);

    $name = trim($name);
    $value = trim($value);

    if (!getenv($name)) {
        putenv("$name=$value");
    }

    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

// 定义数据库连接常量
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASSWORD'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_CHARSET', getenv('APP_CHARSET') ?: 'utf8');

// 其他配置常量
define('APP_DEBUG', filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));
define('CSRF_TOKEN_BYTES', intval(getenv('CSRF_TOKEN_BYTES')) ?: 32);