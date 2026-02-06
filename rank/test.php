<?php
/* 1. 建立 PDO 连接（与之前详情页里完全一致） */
$dsn  = 'mysql:host=localhost;dbname=rank_ddata;charset=utf8mb4';
$user = 'rank_ddata';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

/* 2. 一次性归档历史战力 */
/* 2026-01-01 至 2026-01-04 之间、按天去重写入 power_log */
// $sql = "
// INSERT INTO power_log (uid, power, log_time)
// SELECT r.uid, r.power, DATE(r.uptime)
// FROM   ranking  AS r
// LEFT JOIN (
//         /* 已经有的 (uid, 日期) 组合 */
//         SELECT uid, DATE(log_time) AS d
//         FROM   power_log
//         GROUP  BY uid, d
// ) AS p  ON r.uid = p.uid AND DATE(r.uptime) = p.d
// WHERE  r.uptime >= '2026-01-01'
//   AND  r.uptime <  '2026-01-05'   -- 含 1 月 4 号全天
//   AND  p.d IS NULL                -- 去掉已存在同一天的记录
// ";

// $rows = $pdo->exec($sql);
// echo "成功写入 {$rows} 条战力日志（2026-01-01 ~ 2026-01-04，按天去重）。\n";
