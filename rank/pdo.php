<?php
// pdo.php

// 请根据您的实际情况修改以下数据库连接信息
$db_host = 'localhost';     // 数据库主机
$db_name = 'rank_ddata'; // 数据库名
$db_user = 'rank_ddata'; // 数据库用户名
$db_pass = ''; // 数据库密码
$db_charset = 'utf8mb4';    // 数据库字符集

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // 在生产环境中，不应将详细错误信息暴露给用户
    // 可以记录错误日志，并显示一个通用的错误消息
    error_log($e->getMessage());
    // exit('数据库连接失败，请稍后再试。');
    throw new \PDOException($e->getMessage(), (int)$e->getCode());

}
