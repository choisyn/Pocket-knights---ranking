<?php
// activeplayers.php
$servernames = 'mysql:host=localhost;dbname=rank_ddata';
$username = 'rank_ddata';
$password = 'Pj5hhsH6jxWkRjfN';

try {
    $pdo = new PDO($servernames, $username, $password);
    $pdo->query('SET NAMES utf8');
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

/* ---------- 0. 工具函数 ---------- */
function pct($val, $total) {
    return $total ? round($val / $total * 100, 2) : 0;
}

/* ---------- 1. 原「活跃玩家统计（全服）」 ---------- */
$sqlTime = "
SELECT
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 0 AND 24 THEN 1 ELSE 0 END) AS active_24h,
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 24 AND 72 THEN 1 ELSE 0 END) AS active_24_72h,
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 72 AND 168 THEN 1 ELSE 0 END) AS active_72_168h,
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) > 168 THEN 1 ELSE 0 END) AS active_over_168h,
    COUNT(*) AS total_time
FROM ranking
WHERE uptime IS NOT NULL
  AND updata_time IS NOT NULL
  AND zone IN (1,2,3);
";
$timeRow = $pdo->query($sqlTime)->fetch(PDO::FETCH_ASSOC);

$sqlTimeEx = "
SELECT
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 0 AND 24 THEN 1 ELSE 0 END) AS active_24h,
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 24 AND 72 THEN 1 ELSE 0 END) AS active_24_72h,
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 72 AND 168 THEN 1 ELSE 0 END) AS active_72_168h,
    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) > 168 THEN 1 ELSE 0 END) AS active_over_168h
FROM ranking
WHERE uptime IS NOT NULL
  AND updata_time IS NOT NULL
  AND power > 10000000000;
";
$timeExRow = $pdo->query($sqlTimeEx)->fetch(PDO::FETCH_ASSOC);

/* ---------- 2. 新增：国服(zone=1) 与 国际中文服(zone=2) 独立统计 ---------- */
$zones = [1 => '国服', 2 => '国际中文服', 3 => '国际英文服'];
$zoneData = [];                 // 保存结果
foreach ($zones as $zid => $zname) {
    /* 总数据（该 zone） */
    $sqlTotal = "
        SELECT
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 0 AND 24 THEN 1 ELSE 0 END) AS active_24h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 24 AND 72 THEN 1 ELSE 0 END) AS active_24_72h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 72 AND 168 THEN 1 ELSE 0 END) AS active_72_168h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) > 168 THEN 1 ELSE 0 END) AS active_over_168h,
            COUNT(*) AS total
        FROM ranking
        WHERE zone = $zid
          AND uptime IS NOT NULL
          AND updata_time IS NOT NULL;
    ";
    $totalRow = $pdo->query($sqlTotal)->fetch(PDO::FETCH_ASSOC);

    /* 排除战力≤10亿 */
    $sqlGt10 = "
        SELECT
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 0 AND 24 THEN 1 ELSE 0 END) AS active_24h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 24 AND 72 THEN 1 ELSE 0 END) AS active_24_72h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) BETWEEN 72 AND 168 THEN 1 ELSE 0 END) AS active_72_168h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, uptime, updata_time) > 168 THEN 1 ELSE 0 END) AS active_over_168h
        FROM ranking
        WHERE zone = $zid
          AND uptime IS NOT NULL
          AND updata_time IS NOT NULL
          AND power > 10000000000;
    ";
    $gt10Row  = $pdo->query($sqlGt10)->fetch(PDO::FETCH_ASSOC);

    $zoneData[$zid] = [
        'name'  => $zname,
        'total' => $totalRow,
        'gt10'  => $gt10Row
    ];
}

/* ---------- 3. 战斗力分布统计（原逻辑） ---------- */
$sqlPower = "
SELECT
    SUM(CASE WHEN power >= 100000000000 THEN 1 ELSE 0 END) AS p_1000e,
    SUM(CASE WHEN power BETWEEN 90000000000 AND 99999999999 THEN 1 ELSE 0 END) AS p_900e,
    SUM(CASE WHEN power BETWEEN 80000000000 AND 89999999999 THEN 1 ELSE 0 END) AS p_800e,
    SUM(CASE WHEN power BETWEEN 70000000000 AND 79999999999 THEN 1 ELSE 0 END) AS p_700e,
    SUM(CASE WHEN power BETWEEN 60000000000 AND 69999999999 THEN 1 ELSE 0 END) AS p_600e,
    SUM(CASE WHEN power BETWEEN 50000000000 AND 59999999999 THEN 1 ELSE 0 END) AS p_500e,
    SUM(CASE WHEN power BETWEEN 40000000000 AND 49999999999 THEN 1 ELSE 0 END) AS p_400e,
    SUM(CASE WHEN power BETWEEN 30000000000 AND 39999999999 THEN 1 ELSE 0 END) AS p_300e,
    SUM(CASE WHEN power BETWEEN 20000000000 AND 29999999999 THEN 1 ELSE 0 END) AS p_200e,
    SUM(CASE WHEN power BETWEEN 10000000000 AND 19999999999 THEN 1 ELSE 0 END) AS p_100e,
    SUM(CASE WHEN power BETWEEN 5000000000 AND 9999999999 THEN 1 ELSE 0 END) AS p_50e,
    SUM(CASE WHEN power BETWEEN 1000000000 AND 4999999999 THEN 1 ELSE 0 END) AS p_10e,
    SUM(CASE WHEN power BETWEEN 500000000 AND 999999999 THEN 1 ELSE 0 END) AS p_5e,
    SUM(CASE WHEN power < 500000000 THEN 1 ELSE 0 END) AS p_under_5e,
    COUNT(*) AS total_power
FROM ranking
WHERE power IS NOT NULL;
";
$powerRow = $pdo->query($sqlPower)->fetch(PDO::FETCH_ASSOC);

/* ---------- 4. 各区战力统计（带排序切换） ---------- */
$allowOrder = ['total_power', 'avg_power', 'max_power']; // 允许排序的字段
$allowSort  = ['asc', 'desc'];                           // 允许的方向

$orderField = isset($_GET['f']) && in_array($_GET['f'], $allowOrder) ? $_GET['f'] : 'total_power';
$orderSort  = isset($_GET['s']) && in_array($_GET['s'], $allowSort)  ? $_GET['s'] : 'desc';

$sqlServer = "
SELECT
    CASE zone
        WHEN 1 THEN '国服'
        WHEN 2 THEN '国际中文服'
        WHEN 3 THEN '国际英文服'
        ELSE '未知'
    END AS zone_name,
    server,
    SUM(power) AS total_power,
    COUNT(*) AS count_gt_10e,
    AVG(power) AS avg_power,
    MAX(power) AS max_power
FROM ranking
WHERE power > 500000000          -- 10亿
GROUP BY zone, server
ORDER BY $orderField $orderSort;
";
$serverRows = $pdo->query($sqlServer)->fetchAll(PDO::FETCH_ASSOC);

/* 小箭头函数 */
function arrow($field, $currentField, $currentSort){
    if ($field !== $currentField) return '';
    return $currentSort === 'desc' ? ' ▼' : ' ▲';
}

/* ---------- 5. 全服最高战力排名表（原逻辑） ---------- */
$rankSql = "
SELECT power, (@rank:=@rank+1) AS rank
FROM (SELECT power FROM ranking WHERE power > 500000000 ORDER BY power DESC) t,
     (SELECT @rank:=0) r
";
$rankList = $pdo->query($rankSql)->fetchAll(PDO::FETCH_ASSOC);
$rankMap = [];
foreach ($rankList as $r) {
    $rankMap[$r['power']] = $r['rank'];
}
?>

<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>活跃玩家 & 战斗力分布</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;background:#f5f5f5;margin:0;padding:40px;}
        .box{max-width:1200px;margin:0 auto 30px;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);padding:30px 40px;}
        h2{text-align:center;margin-top:0;}
        table{width:100%;border-collapse:collapse;}
        th,td{padding:12px 0;text-align:left;}
        th{border-bottom:2px solid #eee;}
        td{border-bottom:1px solid #f0f0f0;}
        .num{font-weight:bold;color:#1890ff;}
        .pct{font-size:0.9em;color:#666;margin-left:8px;}
        td.seq{text-align:center;color:#666;font-weight:bold;}
        /* 新增两栏并排 */
        .two-col{display:flex;gap:40px;}
        .two-col>div{flex:1;}
        .sort-btn{
            background:none;border:none;font:inherit;color:inherit;cursor:pointer;
        }
        .sort-btn:hover{text-decoration:underline;}
    </style>
</head>
<body>

<!-- 1. 原：全服活跃玩家统计 -->
<div class="box">
    <h2>活跃玩家统计（全服）</h2>
    <table>
        <thead>
            <tr>
                <th>最后在线时间</th>
                <th>玩家数量<br><small>(战力>10亿)</small></th>
                <th>被排除玩家数<br><small>(战力≤10亿)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ranges = [
                '最近24小时' => ['t'=>$timeRow['active_24h'],   'g'=>$timeExRow['active_24h']],
                '最近1-3天'  => ['t'=>$timeRow['active_24_72h'],'g'=>$timeExRow['active_24_72h']],
                '最近3-7天'  => ['t'=>$timeRow['active_72_168h'],'g'=>$timeExRow['active_72_168h']],
                '7天以上'    => ['t'=>$timeRow['active_over_168h'],'g'=>$timeExRow['active_over_168h']],
            ];
            $totalGt10e = array_sum($timeExRow);   // 战力>10亿总人数
            foreach ($ranges as $range=>$arr): ?>
              <tr>
                <td><?= $range ?></td>
                <td class="num">
                  <?= $arr['g'] ?>
                  <span class="pct">(<?= pct($arr['g'], $totalGt10e) ?>%)</span>
                </td>
                <td class="num"><?= $arr['t'] - $arr['g'] ?></td>
              </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 2. 新增：国服 vs 国际中文服 独立统计 -->
<div class="box three-col">
    <!-- 国服 / 国际中文服 / 国际英文服 三栏并排 -->
    <div class="box">
        <h2>活跃玩家统计 - 分服明细</h2>
        <div class="three-col" style="display:flex;gap:30px;">
        <?php
        /* 循环三个 zone */
        for ($zid = 1; $zid <= 3; $zid++):
            $data = $zoneData[$zid] ?? ['total'=>[],'gt10'=>[]];
            $zname = [1=>'国服',2=>'国际中文服',3=>'国际英文服'][$zid] ?? '未知';
            $totalGt10eZone = array_sum($data['gt10']);
            $rangesZone = [
                '最近24小时' => ['t'=>$data['total']['active_24h']   ?? 0, 'g'=>$data['gt10']['active_24h']   ?? 0],
                '最近1-3天'  => ['t'=>$data['total']['active_24_72h']?? 0, 'g'=>$data['gt10']['active_24_72h']?? 0],
                '最近3-7天'  => ['t'=>$data['total']['active_72_168h']??0, 'g'=>$data['gt10']['active_72_168h']??0],
                '7天以上'    => ['t'=>$data['total']['active_over_168h']??0,'g'=>$data['gt10']['active_over_168h']??0],
            ];
        ?>
            <div style="flex:1;">
                <h3><?= $zname ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>最后在线时间</th>
                            <th>玩家数量<br><small>(战力>10亿)</small></th>
                            <th>被排除玩家数<br><small>(战力≤10亿)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rangesZone as $range=>$arr): ?>
                      <tr>
                        <td><?= $range ?></td>
                        <td class="num"><?= $arr['g'] ?><span class="pct">(<?= pct($arr['g'], $totalGt10eZone) ?>%)</span></td>
                        <td class="num"><?= $arr['t'] - $arr['g'] ?></td>
                      </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endfor; ?>
        </div>
    </div>
</div>

<!-- 3. 战斗力分布统计（原样） -->
<div class="box">
    <h2>战斗力分布统计</h2>
    <table>
        <thead><tr><th>战斗力区间</th><th>玩家数量</th></tr></thead>
        <tbody>
            <?php
            $powerMap = [
                '≥1000亿' => $powerRow['p_1000e'],
                '900亿 – 999亿' => $powerRow['p_900e'],
                '800亿 – 899亿' => $powerRow['p_800e'],
                '700亿 – 799亿' => $powerRow['p_700e'],
                '600亿 – 699亿' => $powerRow['p_600e'],
                '500亿 – 599亿' => $powerRow['p_500e'],
                '400亿 – 499亿' => $powerRow['p_400e'],
                '300亿 – 399亿' => $powerRow['p_300e'],
                '200亿 – 299亿' => $powerRow['p_200e'],
                '100亿 – 199亿' => $powerRow['p_100e'],
                '50亿 – 99亿' => $powerRow['p_50e'],
                '10亿 – 49亿' => $powerRow['p_10e'],
                '5亿 – 9亿' => $powerRow['p_5e'],
                '<5亿' => $powerRow['p_under_5e'],
            ];
            foreach ($powerMap as $range => $count): ?>
                <tr>
                    <td><?= $range ?></td>
                    <td class="num"><?= $count ?><span class="pct">(<?= pct($count, $powerRow['total_power']) ?>%)</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 4. 各区战力统计（带排序） -->
<div class="box">
    <h2>各区战力统计（仅统计战力＞5亿）</h2>
    <table>
        <thead>
            <tr>
                <th>序号</th>
                <th>区服</th>
                <th>区名</th>
                <th>
                    <button class="sort-btn" onclick="sortTable('total','desc')" id="btn-total">
                        战力总和 ▼
                    </button>
                </th>
                <th>统计人数</th>
                <th>
                    <button class="sort-btn" onclick="sortTable('avg','desc')" id="btn-avg">
                        人均战力 ▼
                    </button>
                </th>
                <th>
                    <button class="sort-btn" onclick="sortTable('max','desc')" id="btn-max">
                        最高战力 ▼
                    </button>
                </th>
            </tr>
            </thead>
        <tbody id="serverList">
            <?php
            $idx = 0;
            foreach ($serverRows as $row):
                $idx++; ?>
                <tr data-total="<?= $row['total_power'] ?>"
                    data-avg="<?= $row['avg_power'] ?>"
                    data-max="<?= $row['max_power'] ?>"
                    data-idx="<?= $idx ?>">          <!-- ★ 固定序号 -->
                    <td class="seq"><?= $idx ?></td>
                    <td><?= $row['zone_name'] ?></td>
                    <td><?= $row['server'] ?></td>
                    <td class="num"><?= number_format($row['total_power']) ?></td>
                    <td class="num"><?= $row['count_gt_10e'] ?></td>
                    <td class="num"><?= number_format($row['avg_power'], 0) ?></td>
                    <td class="num"><?= number_format($row['max_power']) ?>（全服第<?= $rankMap[sprintf('%.0f', $row['max_power'])] ?? '未上榜' ?>名）</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
/* 当前排序状态 */
let curField = 'total';
let curDir   = 'desc';

function sortTable(field, dir){
    const tbody = document.getElementById('serverList');
    const rows  = Array.from(tbody.rows);

    if (field === curField) {
        dir = curDir === 'desc' ? 'asc' : 'desc';
    }
    curField = field;
    curDir   = dir;

    /* 按数值排序 */
    rows.sort((a, b) => {
        const va = Number(a.dataset[field]);
        const vb = Number(b.dataset[field]);
        return dir === 'desc' ? vb - va : va - vb;
    });

    /* 重新插回 DOM */
    const frag = document.createDocumentFragment();
    rows.forEach(r => frag.appendChild(r));
    tbody.appendChild(frag);

    /* ★ 重新写序号（永远 1 开始） */
    rows.forEach((r, i) => {
        r.querySelector('td.seq').textContent = i + 1;
    });

    /* 更新按钮箭头 */
    document.querySelectorAll('.sort-btn').forEach(btn => btn.textContent = btn.textContent.replace(/[▼▲]/g,''));
    const arrow = dir === 'desc' ? '▼' : '▲';
    document.getElementById('btn-'+field).textContent += arrow;
}

/* 默认按战力总和降序（后端已排好，无需再调） */
</script>
</body>
</html>