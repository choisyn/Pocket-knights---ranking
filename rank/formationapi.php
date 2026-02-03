<?php
header('Content-Type: application/json; charset=utf-8');
require 'pdo.php';   // 把之前连接数据库的代码拆出去

// debug
file_put_contents(
    __DIR__.'/debug.log',
    date('Y-m-d H:i:s').' '.$_SERVER['QUERY_STRING'].PHP_EOL,
    FILE_APPEND
);

$zoneStart = isset($_GET['zs']) && is_numeric($_GET['zs']) ? (int)$_GET['zs'] : 1;
$zoneEnd   = isset($_GET['ze']) && is_numeric($_GET['ze']) ? (int)$_GET['ze'] : 10;
$zones     = isset($_GET['zone']) && is_array($_GET['zone'])
                ? array_map('intval', $_GET['zone'])
                : [0];

$sql = "SELECT formation
        FROM ranking
        WHERE formation IS NOT NULL
          AND formation != ''";
$params = [];

if ($zoneStart || $zoneEnd) {
    $sql .= " AND (CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(server,'.',1),'S',-1) AS UNSIGNED) BETWEEN ? AND ?)";
    $params[] = $zoneStart;
    $params[] = $zoneEnd;
}
if ($zones) {
    $place = implode(',', array_fill(0, count($zones), '?'));
    $sql .= " AND zone IN ($place)";
    foreach ($zones as $z) $params[] = $z;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = trim($r['formation']);
}

// debug
// if (!$rows) {
//     echo json_encode([
//         'dbg' => [
//             'received' => [
//                 'zs'   => $zoneStart,
//                 'ze'   => $zoneEnd,
//                 'zone' => $zones,
//             ],
//             'sql'   => $sql,
//             'bind'  => $params,
//         ]
//     ], JSON_UNESCAPED_UNICODE);
//     exit;
// }
echo json_encode($rows, JSON_UNESCAPED_UNICODE);