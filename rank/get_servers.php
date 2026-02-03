<?php
$servernames = 'mysql:host=localhost;dbname=rank_ddata';
$username = 'rank_ddata';
$password = 'Pj5hhsH6jxWkRjfN';

try {
    $pdo = new PDO($servernames, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query('SET NAMES utf8');
} catch (PDOException $e) {
    die("连接失败: " . $e->getMessage());
}

// 获取查询条件
$selectedZone = isset($_GET['zone']) ? $_GET['zone'] : 1;
$queryType = isset($_GET['query_type']) ? $_GET['query_type'] : 'player';

// 根据查询类型选择表
$tableName = ($queryType === 'team') ? 'team_rank' : 'ranking';

// 获取符合条件的 server 值
if ($selectedZone === 'all') {
    // 查询所有服的服务器（排除测试服zone=0）
    $sqlServer = "SELECT DISTINCT server FROM {$tableName} WHERE zone != 0";
    $stmtServer = $pdo->prepare($sqlServer);
    $stmtServer->execute();
} else {
    // 查询指定zone的服务器
    $sqlServer = "SELECT DISTINCT server FROM {$tableName} WHERE zone = :zone";
    $stmtServer = $pdo->prepare($sqlServer);
    $stmtServer->bindParam(':zone', $selectedZone, PDO::PARAM_INT);
    $stmtServer->execute();
}
$servers = $stmtServer->fetchAll(PDO::FETCH_COLUMN);

// 关闭数据库连接
$pdo = null;

// 返回服务器列表
echo json_encode($servers);
?>
