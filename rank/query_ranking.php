<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$servernames = 'mysql:host=localhost;dbname=rank_ddata';
$username = 'rank_ddata';
$password = 'Pj5hhsH6jxWkRjfN';

try {
    $pdo = new PDO($servernames, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query('SET NAMES utf8');
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => '数据库连接失败: ' . $e->getMessage()
    ]);
    exit;
}

// 获取查询条件
$queryType = isset($_GET['query_type']) ? $_GET['query_type'] : 'player';
$selectedZone = isset($_GET['zone']) ? $_GET['zone'] : 1;
$selectedServer = isset($_GET['server']) ? trim($_GET['server']) : '';
$showUid = isset($_GET['show_uid']) && $_GET['show_uid'] == '1';
$showLevel = isset($_GET['show_level']) && $_GET['show_level'] == '1';
$showFame = isset($_GET['show_fame']) && $_GET['show_fame'] == '1';
$showServer = isset($_GET['show_server']) && $_GET['show_server'] == '1';
$showUpdateTime = isset($_GET['show_updata_time']) && $_GET['show_updata_time'] == '1';
$abbreviate = isset($_GET['abbreviate']) && $_GET['abbreviate'] == '1';
$showFormation = isset($_GET['show_formation']) && $_GET['show_formation'] == '1';
$searchPlayer = isset($_GET['search_player']) ? trim($_GET['search_player']) : '';
$searchTeam = isset($_GET['search_team']) ? trim($_GET['search_team']) : '';

// 分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = 50;
$offset = ($page - 1) * $pageSize;

// 军团查询专用选项
$showTid = isset($_GET['show_tid']) && $_GET['show_tid'] == '1';
$showTeamLevel = isset($_GET['show_team_level']) && $_GET['show_team_level'] == '1';
$showNofpeople = isset($_GET['show_nofpeople']) && $_GET['show_nofpeople'] == '1';
$showPercapita = isset($_GET['show_percapita']) && $_GET['show_percapita'] == '1';


// 获取排序选项
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : ($queryType === 'team' ? 'popularity' : 'power');
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';

// 验证排序字段
if ($queryType === 'team') {
    $valid_sort_fields = ['popularity', 'level', 'nofpeople', 'server', 'percapita', 'teamname'];
    if (!in_array($sort_by, $valid_sort_fields)) {
        $sort_by = 'popularity';
    }
} else {
    $valid_sort_fields = ['power', 'fame', 'level', 'server', 'name', 'uid'];
    if (!in_array($sort_by, $valid_sort_fields)) {
        $sort_by = 'power';
    }
}

// 验证排序方向
if (!in_array($sort_order, ['asc', 'desc'])) {
    $sort_order = 'desc';
}

// 构建查询SQL
if ($queryType === 'team') {
    // 军团查询
    $baseSql = "FROM team_rank WHERE zone != 0";
    if ($selectedZone !== 'all') {
        $baseSql = "FROM team_rank WHERE zone = :zone";
        $params = [':zone' => (int)$selectedZone];
    } else {
        $params = [];
    }

    if (!empty($selectedServer)) {
        $baseSql .= " AND server = :server";
        $params[':server'] = $selectedServer;
    }

    if (!empty($searchTeam)) {
        $baseSql .= " AND (teamname LIKE :search OR tid LIKE :search)";
        $params[':search'] = '%' . $searchTeam . '%';
    }
} else {
    // 玩家查询
    $baseSql = "FROM ranking WHERE zone != 0";
    if ($selectedZone !== 'all') {
        $baseSql = "FROM ranking WHERE zone = :zone";
        $params = [':zone' => (int)$selectedZone];
    } else {
        $params = [];
    }
    
    if (!empty($selectedServer)) {
        $baseSql .= " AND server = :server";
        $params[':server'] = $selectedServer;
    }
    
    if (!empty($searchPlayer)) {
        $baseSql .= " AND (name LIKE :search OR uid LIKE :search)";
        $params[':search'] = '%' . $searchPlayer . '%';
    }
}
// ===== 新增：区号范围 =====
$zoneRange = isset($_GET['zone_range']) ? trim($_GET['zone_range']) : '';

/* 把 "1-20" 或 "5,7,9-12" 解析成 [min,max] 整数区间 */
function parseZoneRange(string $range): array
{
    if ($range === '') return [1, 9999];
    $set = [];
    foreach (explode(',', $range) as $v) {
        $v = trim($v);
        if (ctype_digit($v)) {                    // 单个数字
            $set[] = (int)$v;
        } elseif (preg_match('/^(\d+)-(\d+)$/', $v, $m)) { // 区间
            $start = min((int)$m[1], (int)$m[2]);
            $end   = max((int)$m[1], (int)$m[2]);
            for ($i = $start; $i <= $end; $i++) $set[] = $i;
        }
    }
    $set = array_unique($set);
    return $set ? [min($set), max($set)] : [1, 9999];
}

/* 把 BETWEEN 子句拼到已有 SQL 上 */
function applyZoneRange(string &$sql, array &$params, array $minMax): void
{
    if ($minMax[0] === 1 && $minMax[1] === 9999) return;

    // 从第二位开始取连续数字，遇到非数字即停
    $sql .= " AND CAST(
                SUBSTRING(server, 2,
                  LEAST(
                    IF(LOCATE('.', server)=0, 999, LOCATE('.', server)),
                    IF(LOCATE('-', server)=0, 999, LOCATE('-', server)),
                    LENGTH(server)
                  ) - 2
                )
              AS UNSIGNED) BETWEEN :zMin AND :zMax";
    $params[':zMin'] = $minMax[0];
    $params[':zMax'] = $minMax[1];
}

list($zMin, $zMax) = parseZoneRange($zoneRange);
applyZoneRange($baseSql, $params, [$zMin, $zMax]);
// ===== 区号范围结束 =====

// 获取总记录数用于分页
$countSql = "SELECT COUNT(*) " . $baseSql;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $pageSize);


// 构建数据查询SQL
$sql = "SELECT * " . $baseSql;
applyZoneRange($sql, $params, [$zMin, $zMax]);   // ★给数据查询用

// 构建 ORDER BY 子句
if ($sort_by === 'server') {
    // 对于服务器排序，提取服务器编号进行数字排序
    // 使用 SUBSTRING 和 LOCATE 提取 S 后面的数字部分
    $order_clause = "ORDER BY CAST(SUBSTRING(server, 2, LOCATE('.', server) - 2) AS UNSIGNED) $sort_order, server $sort_order";
} else {
    $order_clause = "ORDER BY $sort_by $sort_order";
}
$sql .= " $order_clause";

// 添加分页限制
$sql .= " LIMIT $pageSize OFFSET $offset";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 生成HTML内容
function generateTableHTML($result, $queryType, $showUid, $showLevel, $showFame, $showServer, $showUpdateTime, $abbreviate, $selectedServer, $showTid, $showTeamLevel, $showNofpeople, $showPercapita, $showFormation, $page, $pageSize, $totalRows) {
    if (!$result || count($result) == 0) {
        return '<div class="no-data">' .
               '<i class="fas fa-search"></i>' .
               '<p>没有找到符合条件的数据</p>' .
               '<small>请尝试调整筛选条件</small>' .
               '</div>';
    }
    
    $html = '<div class="table-container">';
    $html .= '<table>';
    $html .= '<thead><tr>';
    
    // 表头
    $html .= '<th class="sortable" data-column="rank"><i class="fas fa-trophy"></i> 排名</th>';
    
    if ($queryType === 'team') {
        // 军团排行榜表头
        $html .= '<th class="sortable" data-column="teamname"><i class="fas fa-shield-alt"></i> 军团名称</th>';
        $html .= '<th class="sortable" data-column="popularity"><i class="fas fa-crown"></i> 繁荣度 <i class="fas fa-sort"></i></th>';
        
        if ($showTid) $html .= '<th><i class="fas fa-id-badge"></i> 军团ID</th>';
        if ($showTeamLevel) $html .= '<th class="sortable" data-column="level"><i class="fas fa-level-up-alt"></i> 等级 <i class="fas fa-sort"></i></th>';
        if ($showNofpeople) $html .= '<th class="sortable" data-column="nofpeople"><i class="fas fa-users"></i> 人数 <i class="fas fa-sort"></i></th>';
        if ($showPercapita) $html .= '<th class="sortable" data-column="percapita"><i class="fas fa-chart-line"></i> 人均繁荣 <i class="fas fa-sort"></i></th>';
        if ($showServer) $html .= '<th><i class="fas fa-server"></i> 服务器</th>';

    } else {
        // 玩家排行榜表头
        $html .= '<th class="sortable" data-column="name"><i class="fas fa-user"></i> 玩家名称</th>';
        $html .= '<th class="sortable" data-column="power"><i class="fas fa-bolt"></i> 战力 <i class="fas fa-sort"></i></th>';
        if ($showFormation) $html .= '<th style="min-width:180px;"> <i class="fas fa-th"></i> 玩家阵容</th>';
        if ($showUid) $html .= '<th><i class="fas fa-id-card"></i> UID</th>';
        if ($showLevel) $html .= '<th class="sortable" data-column="level"><i class="fas fa-level-up-alt"></i> 等级 <i class="fas fa-sort"></i></th>';
        if ($showFame) $html .= '<th class="sortable" data-column="fame"><i class="fas fa-star"></i> 声望 <i class="fas fa-sort"></i></th>';
        if ($showServer) $html .= '<th><i class="fas fa-server"></i> 服务器</th>';
        if ($showUpdateTime) $html .= '<th><i class="fas fa-clock"></i> 更新时间</th>';
    }
    
    $html .= '</tr></thead><tbody>';
    
    $rank = ($page - 1) * $pageSize + 1;
    foreach ($result as $row) {
        // 保证$showFormation变量存在
        if (!isset($showFormation)) {
            $showFormation = false;
        }
        $html .= '<tr>';
        
        // 排名列
        $rankClass = '';
        if ($rank == 1) $rankClass = 'rank-1';
        elseif ($rank == 2) $rankClass = 'rank-2';
        elseif ($rank == 3) $rankClass = 'rank-3';
        
        $html .= '<td class="rank-cell ' . $rankClass . '">';
        if ($rank <= 3) {
            $html .= '<i class="fas fa-medal"></i> ';
        }
        $html .= $rank . '</td>';
        
        if ($queryType === 'team') {
            // 军团名称列
            $html .= '<td class="team-name">' . htmlspecialchars($row['teamname']) . '</td>';
            
            // 繁荣度列
            $html .= '<td class="popularity-value">' . number_format($row['popularity']) . '</td>';
            
            // 军团可选显示的列
            if ($showTid) $html .= '<td>' . htmlspecialchars($row['tid']) . '</td>';
            if ($showTeamLevel) $html .= '<td><i class="fas fa-level-up-alt"></i> ' . htmlspecialchars($row['level']) . '</td>';
            if ($showNofpeople) $html .= '<td><i class="fas fa-users"></i> ' . number_format($row['nofpeople']) . '</td>';
            if ($showPercapita) $html .= '<td><i class="fas fa-chart-line"></i> ' . number_format($row['percapita']) . '</td>';
        } else {
            // 玩家名称列
            $html .= '<td class="player-name">' . htmlspecialchars($row['name']) . 
                     '<a href="user_data.php?uid=' . urlencode($row['uid']) . '" class="detail-link">' .
                     '<i class="fas fa-info-circle"></i> 详情</a></td>';
            
            // 战力列
            $html .= '<td class="power-value">' . number_format($row['power']) . '</td>';
            
            // 阵容显示列（小图标）
            if ($showFormation) {
                $formationArr = [];
                if (!empty($row['formation'])) {
                    $formationArr = explode('-', $row['formation']);
                }
                $html .= '<td style="white-space:nowrap;vertical-align:middle;padding:0;width:auto;max-width:0;">';
                foreach ($formationArr as $idx => $fid) {
                    $fid = trim($fid);
                    if ($fid) {
                        $imgSrc = "img/" . htmlspecialchars($fid) . ".png";
                        if (!file_exists($imgSrc)) {
                            $imgSrc = "img/" . htmlspecialchars($fid) . ".jpg";
                        }
                        $pos = $idx + 1;
                        $html .= '<img src="' . $imgSrc . '" alt="英雄' . $fid . '" style="width:28px;height:28px;margin:0 1px;vertical-align:middle;border-radius:4px;box-shadow:0 1px 2px rgba(0,0,0,0.08);" data-uid="' . htmlspecialchars($row['uid']) . '" data-pos="' . $pos . '" data-attrs="' . htmlspecialchars(json_encode($heroAttrs, JSON_UNESCAPED_UNICODE)) . '" class="hero-icon">';
                    }
                }
                $html .= '</td>';
            }
            // 玩家可选显示的列
            if ($showUid) $html .= '<td>' . htmlspecialchars($row['uid']) . '</td>';
            if ($showLevel) $html .= '<td><i class="fas fa-level-up-alt"></i> ' . htmlspecialchars($row['level']) . '</td>';
            if ($showFame) $html .= '<td><i class="fas fa-star"></i> ' . number_format($row['fame']) . '</td>';
        }
        
        if ($showServer) {
            $html .= '<td>';
            
            // 根据zone添加区服标识
            $zoneClass = '';
            $zoneIcon = '';
            $zoneTitle = '';
            switch($row['zone']) {
                case 1:
                    $zoneClass = 'zone-domestic';
                    $zoneIcon = '🇨🇳';
                    $zoneTitle = '国内服';
                    break;
                case 2:
                    $zoneClass = 'zone-intl-cn';
                    $zoneIcon = '🌏';
                    $zoneTitle = '国际-中文服';
                    break;
                case 3:
                    $zoneClass = 'zone-intl-en';
                    $zoneIcon = '🌎';
                    $zoneTitle = '国际-英文服';
                    break;
                default:
                    $zoneClass = 'zone-test';
                    $zoneIcon = '🧪';
                    $zoneTitle = '测试服';
                    break;
            }
            
            if ($abbreviate) {
                $serverAbbreviate = preg_split('/[.-]/', $row['server'])[0];
                $html .= '<span class="server-name ' . $zoneClass . '" title="' . $zoneTitle . ' - ' . htmlspecialchars($row['server']) . '">';
                $html .= '<span class="zone-icon">' . $zoneIcon . '</span>';
                $html .= htmlspecialchars($serverAbbreviate) . '</span>';
            } else {
                $html .= '<span class="server-name ' . $zoneClass . '" title="' . $zoneTitle . '">';
                $html .= '<span class="zone-icon">' . $zoneIcon . '</span>';
                $html .= htmlspecialchars($row['server']) . '</span>';
            }
            $html .= '</td>';
        }
        
        if ($queryType === 'team') {
            // 军团查询暂无额外显示字段
        } else {
            if ($showUpdateTime) {
                $updateTime = new DateTime($row['updata_time']);
                $html .= '<td><i class="fas fa-clock"></i> ' . $updateTime->format('m-d H:i') . '</td>';
            }
        }
        
        $html .= '</tr>';
        $rank++;
    }
    $html .= '</tbody></table></div>';
    
    // 显示统计信息
    $html .= '<div style="margin-top: 20px; text-align: center; color: #666;">';
    if ($queryType === 'team') {
        $html .= '<i class="fas fa-shield-alt"></i> 共找到 ' . $totalRows . ' 个军团';
    } else {
        $html .= '<i class="fas fa-info-circle"></i> 共找到 ' . $totalRows . ' 条记录';
    }
    if (!empty($selectedServer)) {
        $html .= ' (服务器: ' . htmlspecialchars($selectedServer) . ')';
    }
    $html .= '</div>';
    
    return $html;
}

$html = generateTableHTML($result, $queryType, $showUid, $showLevel, $showFame, $showServer, $showUpdateTime, $abbreviate, $selectedServer, $showTid, $showTeamLevel, $showNofpeople, $showPercapita, $showFormation, $page, $pageSize, $totalRows);

// 返回JSON响应
echo json_encode([
    'success' => true,
    'html' => $html,
    'count' => count($result),
    'currentPage' => $page,
    'totalPages' => $totalPages
]);

// 关闭数据库连接
$pdo = null;
?>