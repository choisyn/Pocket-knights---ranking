<?php
//指定uid/名字模糊查询json
// 连接数据库
$servernames = 'mysql:host=localhost;dbname=rank_ddata';
$username = 'rank_ddata';
$password = 'Pj5hhsH6jxWkRjfN';
$pdo = new PDO($servernames, $username, $password);
$pdo->query('SET NAMES utf8');

// 获取 POST 请求中的查询词
$search_term = $_POST["uid"];
$mode = isset($_POST['mode']) ? $_POST['mode'] : 'fuzzy';
$otherValues = []; //返回的json数组

if (empty($search_term)) {
    $otherValues['uid'] = 0;
    echo json_encode($otherValues, JSON_UNESCAPED_UNICODE);
    exit;
}

// 从数据库中查询
if ($mode === 'exact') {
    $sql = "SELECT * FROM ranking WHERE uid = :search_term";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':search_term', $search_term);
} else {
    $sql = "SELECT * FROM ranking WHERE uid LIKE :search_term OR name LIKE :search_term ORDER BY CAST(power AS UNSIGNED) DESC";
    $stmt = $pdo->prepare($sql);
    $search_param = "%" . $search_term . "%";
    $stmt->bindParam(':search_term', $search_param);
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$num_results = count($results);


if ($num_results == 0) {
    $otherValues['uid'] = 0;
    echo json_encode($otherValues, JSON_UNESCAPED_UNICODE);
} elseif ($num_results == 1) {
    $rows = $results[0];
    $uid = $rows['uid'];
    $rzone = $rows['zone'];
    //国服排名
    $sqlr = "SELECT uid, power,
    (SELECT COUNT(DISTINCT power) + 1
     FROM ranking
     WHERE power > t1.power AND zone = :rzone) AS power_rank
    FROM ranking t1
    WHERE uid = :uid AND zone = :rzone";
    // 准备并执行查询
    $stmtr = $pdo->prepare($sqlr);
    $stmtr->bindParam(':uid', $uid, PDO::PARAM_INT);
    $stmtr->bindParam(':rzone', $rzone, PDO::PARAM_INT);
    $stmtr->execute();
    $resultr = $stmtr->fetch(PDO::FETCH_ASSOC);
    if ($resultr) {
        $otherValues['grank'] = $resultr['power_rank'];
    } else {
        $otherValues['grank'] = '全服排名获取失败！';
    }

    ///输出json
    //查询同区同排名,取更新时间最新的uid
    $sql_comp = "SELECT uid
    FROM ranking
    WHERE area = :area
    AND server = :server
    ORDER BY ABS(TIMESTAMPDIFF(SECOND, updata_time, CURRENT_TIMESTAMP()))
    LIMIT 1";

    // 使用绑定参数执行查询
    $stmt_comp = $pdo->prepare($sql_comp);
    $stmt_comp->bindParam(':area', $rows['area'], PDO::PARAM_STR);
    $stmt_comp->bindParam(':server', $rows['server'], PDO::PARAM_STR);
    $stmt_comp->execute();
    // 获取查询结果
    $result_comp = $stmt_comp->fetch(PDO::FETCH_ASSOC);

    $otherValues['uid'] = $rows['uid'];
    if ($rows['uid'] == $result_comp['uid']) {
        $otherValues['area'] = $rows['area'];
    } else {
        $otherValues['area'] = $rows['area'] . "(现8+)";
    }
    
    $otherValues['name'] = $rows['name'];
    $otherValues['power'] = $rows['power'];
    $otherValues['level'] = $rows['level'];
    $otherValues['fame'] = $rows['fame'];
    $otherValues['server'] = $rows['server'];
    $otherValues['zone'] = $rows['zone'];
    $otherValues['uptime'] = $rows['uptime'];
    $otherValues['updata_time'] = $rows['updata_time'];
    $otherValues['achieve'] = $rows['achieve'];
    $otherValues['ps'] = $rows['ps'];
    $otherValues['record'] = $rows['record'];
    $otherValues['formation'] = $rows['formation'];
    $otherValues['star'] = $rows['star'];
    $otherValues['sche'] = $rows['sche'];
    $otherValues['tow'] = $rows['tow'];
    $otherValues['eda'] = $rows['eda'];
    // 将获取到的其他值以 JSON 格式返回
    echo json_encode($otherValues, JSON_UNESCAPED_UNICODE);
} else {
    // multiple results
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
}
