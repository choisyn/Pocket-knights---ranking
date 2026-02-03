<?php
// 英雄(编号)数量统计
// 数据库配置
$db_host = 'localhost';
$db_name = 'rank_ddata';
$db_user = 'rank_ddata';
$db_pass = 'Pj5hhsH6jxWkRjfN';
$db_charset = 'utf8mb4';

// 创建数据库连接
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 查询所有非空的 formation 字段
$sql = "SELECT formation FROM ranking WHERE formation IS NOT NULL AND formation != ''";
$stmt = $pdo->query($sql);

// 用于统计编号的数组
$codeCounts = [];

// 遍历查询结果
while ($row = $stmt->fetch()) {
    $formation = trim($row['formation']);
    
    // 跳过空值
    if (empty($formation)) {
        continue;
    }
    
    // 按 "-" 分割字符串
    $codes = explode('-', $formation);
    
    // 确保有6个编号
    if (count($codes) !== 6) {
        continue;
    }
    
    // 遍历每个编号
    foreach ($codes as $code) {
        // 跳过 "0000"
        if ($code === '0000') {
            continue;
        }
        
        // 统计编号出现次数
        if (!isset($codeCounts[$code])) {
            $codeCounts[$code] = 0;
        }
        $codeCounts[$code]++;
    }
}

// 按出现次数降序排序
arsort($codeCounts);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编号统计结果</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>编号统计结果</h1>
        
        <?php if (empty($codeCounts)): ?>
            <p style="text-align: center; color: #666; font-size: 18px;">没有找到有效的编号数据</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>排名</th>
                        <th>编号</th>
                        <th>出现次数</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($codeCounts as $code => $count): 
                    ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($code); ?></td>
                            <td><?php echo number_format($count); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                总计：<?php echo count($codeCounts); ?> 种不同的编号
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// 关闭数据库连接
$pdo = null;
?>