<?php
header('Content-Type: application/json; charset=utf-8');
require 'pdo.php';          // 你的 PDO 连接
$uid = $_GET['uid'] ?? '';
$pos = (int)($_GET['pos'] ?? 0);
if (!$uid || !$pos)  exit(json_encode(['success'=>false]));

$stmt = $pdo->prepare('SELECT * FROM player_heroes WHERE uid = ? AND position = ?');
$stmt->execute([$uid, $pos]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $row = ['hp'=>0,'attack'=>0,'defense'=>0,'skill'=>0,'addattack'=>0,'adddefense'=>0,'power'=>0];
}
else {
    // 只返回需要的字段，防止多余字段影响前端
    $row = [
        'hp' => $row['hp'],
        'attack' => $row['attack'],
        'defense' => $row['defense'],
        'skill' => $row['skill'],
        'addattack' => $row['addattack'],
        'adddefense' => $row['adddefense'],
        'power' => $row['power'],
        'equips' => $row['equips'],
        'jewelrys' => $row['jewelrys'],
        'runes' => $row['runes']
    ];
}
echo json_encode(['success'=>true, 'data'=>$row]);