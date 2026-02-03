<!-- <button onclick="window.history.back();">返回上一页</button> -->

<br>


<!-- <img src="img/001.jpg" alt="描述文字"> -->

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>玩家详情</title>
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- 战力曲线 -->
<script src="chart.umd.min.js"></script>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
        color: #333;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        /* backdrop-filter: blur(10px); */
    }

    .header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 30px;
        text-align: center;
        position: relative;
    }

    .header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
        opacity: 0.3;
    }

    .header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .player-title {
        font-size: 1.2rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .content {
        padding: 30px 30px 0;
    }

    .player-info-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 10px;
    }

    .info-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .info-card h3 {
        color: #4facfe;
        margin-bottom: 15px;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #666;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-value {
        color: #333;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .highlight {
        color: #ff6b6b;
        font-weight: 700;
    }

    .player-name {
        color: #4facfe;
        font-weight: 700;
    }

    .formation-section {
        margin: 0px 0;
    }

    .formation-title {
        text-align: center;
        color: #4facfe;
        font-size: 1.5rem;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .formation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        gap: 15px;
        max-width: 900px;
        margin: 0 auto;
        padding: 10px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .formation-item {
        text-align: center;
        padding: 5px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 2px solid transparent;
    }

    .formation-item:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        border-color: #4facfe;
    }

    .formation-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .formation-item:hover img {
        transform: scale(1.1);
    }

    .back-button {
        text-align: center;
        margin-top: 30px;
        padding: 0 30px 30px;
    }

    .btn-back {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        padding: 15px 40px;
        font-size: 1.1rem;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .btn-back:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(79, 172, 254, 0.4);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .rank-badge {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #333;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-block;
    }

    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .header {
            padding: 20px;
        }

        .header h1 {
            font-size: 2rem;
        }

        .content {
            padding: 20px;
        }

        .formation-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 15px;
        }

        .formation-item img {
            width: 60px;
            height: 60px;
        }

        .info-card {
            padding: 20px;
        }

        .player-info-section {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .formation-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .header h1 {
            font-size: 1.5rem;
        }

        .formation-item img {
            width: 50px;
            height: 50px;
        }
    }
    
    .raw-num   { color: #ff6b6b; }        /* 原数据：深黑 */
    .simple-num{ color: #4facfe; }        /* 简约数据：灰色 */
    
/* 三容器父级：强制不换行，横向滚动 */
.equip-line{display:flex;gap:12px;margin:20px auto;max-width:100%;overflow-x:auto;}
/* 单个容器：最小宽度刚好容纳 2 列图标 */
.equip-mini{flex:0 0 auto;width:104px;background:#fff;border-radius:12px;padding:12px;box-shadow:0 4px 15px rgba(0,0,0,.1);}
.equip-mini h4{color:#4facfe;font-size:14px;margin:0 0 10px;text-align:center;}
.equip-mini-grid{display:grid;grid-template-columns:repeat(2, 1fr);gap:6px;}
.equip-mini-grid img{width:40px;height:40px;object-fit:cover;border-radius:6px;display:block;margin:0 auto;}
</style>

<script>
/* 根目录英雄名字对照表 */
let heroNameMap = {};
fetch('hero_name.json')
  .then(r => { if(!r.ok) throw 0; return r.json(); })
  .then(j => { heroNameMap = j; })
  .catch(() => { heroNameMap = {}; });
  
  // 首次渲染：等字典加载完成后再执行
(function waitForDict() {
    if (Object.keys(heroNameMap).length) {   // 字典已加载
        showHero(1);
    } else {                                 // 字典未到，继续轮询
        setTimeout(waitForDict, 50);
    }
})();
</script>
</head>
    <?php
        $uid=$_GET['uid'];
        // echo '123123';
        
        $servernames = 'mysql:host=localhost;dbname=rank_ddata';
        $username = 'rank_ddata';
        $password = 'Pj5hhsH6jxWkRjfN';
        
        try {
            // $pdo = new PDO($servernames, $username, $password);
            // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $pdo->query('SET NAMES utf8');
            
            $pdo = new PDO($servernames, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('SET NAMES utf8mb4');
        
            /* 1. 先拿到玩家自己的记录（含 zone） */
            $stmt = $pdo->prepare('SELECT * FROM ranking WHERE uid = :uid');
            $stmt->execute(['uid' => $uid]);
            $rows = $stmt->fetch();
            if (!$rows) {
                die('UID 不存在');
            }
            // 查询特定uid在zone=1的power排名
            // $sql = "SELECT a.uid, a.power, 
            // (SELECT COUNT(*) FROM ranking b WHERE b.zone = 1 AND b.power > a.power) + 1 AS power_rank
            // FROM ranking a
            // WHERE a.zone = 1 AND a.uid = :uid";
            
            // $stmt = $pdo->prepare($sql);
            // $stmt->execute(['uid' => $uid]);
            // $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // $pran = $result[0]['power_rank'];
            /* ===== 全服排名：国内服单独排，国际服(2+3)合并排 ===== */
            $zone = (int)$rows['zone'];          // 当前玩家所在大区
            if ($zone === 1) {
                // 国内服
                $sql = "SELECT a.uid, a.power, 
                        (SELECT COUNT(*) FROM ranking b WHERE b.zone = 1 AND b.power > a.power) + 1 AS power_rank
                        FROM ranking a
                        WHERE a.zone = 1 AND a.uid = :uid";
            } else {
                // 国际服（2 和 3 合并）
                $sql = "SELECT a.uid, a.power, 
                        (SELECT COUNT(*) FROM ranking b WHERE b.zone IN (2,3) AND b.power > a.power) + 1 AS power_rank
                        FROM ranking a
                        WHERE a.zone IN (2,3) AND a.uid = :uid";
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['uid' => $uid]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pran = $result[0]['power_rank'] ?? 0;
            // echo $pran;
        } catch (PDOException $e) {
            die("连接失败: " . $e->getMessage());
        }

        // 获取uid的数据
        $keydata = $pdo->query("SELECT * FROM ranking WHERE uid='{$uid}'");
        $rows = $keydata->fetch();
        
        /* ===== 战力变化日志 ===== */
        $stmt = $pdo->prepare(
            "SELECT DATE(log_time) AS d, power
             FROM power_log
             WHERE uid = :uid
             ORDER BY log_time
             LIMIT 30");     // 最近 30 条，想多显示就改
        $stmt->execute(['uid' => $uid]);
        $powerLog = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        /* 拼成 JS 直接可用的两个数组 */
        $dates = array_column($powerLog, 'd');
        $powers = array_column($powerLog, 'power');
        
        // 判断区服
        if($rows['zone']==1){
            $zone = '国内服';
        }elseif($rows['zone']==2){
            $zone = '国际-中文服';
        }elseif($rows['zone']==3){
            $zone = '国际-英文服';
        }
        // 阵容图提取(数字分割)
        // $astr = $row['formation'];
        $num = explode("-", $rows['formation']);
        // $num = explode("-", "001-002-003-004-005-006");
        $imgname = [];
        // 遍历每个数字，检查对应的图片文件
        foreach ($num as $number) {
            $jpgPath = "img/$number.jpg";
            $pngPath = "img/$number.png";

            // 检查jpg文件是否存在
            if (file_exists($jpgPath)) {
                $imgname[] = $jpgPath;
                // echo "<img src='$jpgPath' alt='Image $number' />";
            }
            // 检查png文件是否存在
            elseif (file_exists($pngPath)) {
                $imgname[] = $pngPath;
                // echo "<img src='$pngPath' alt='Image $number' />";
            }
            // 如果都不存在，可以选择输出提示或者不做任何操作
            else {
                // echo "<p>Image for $number not found.</p>";
                $imgname[] = 'img/302.png';
            }
        }
    ?>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-circle"></i> 玩家详情</h1>
            <div class="player-title">
                <span class="player-name"><?php echo $rows['name'];?></span> (UID: <?php echo $rows['uid'];?>)
            </div>
        </div>

        <div class="content">
            <!-- 基本信息区域 -->
            <div class="player-info-section">
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> 基本信息</h3>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-server"></i> 服务器大区</span>
                        <span class="info-value player-name"><?php echo $zone;?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> 所在区</span>
                        <span class="info-value player-name"><?php echo $rows['server'];?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-level-up-alt"></i> 等级</span>
                        <span class="info-value highlight"><?php echo $rows['level'];?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-trophy"></i> 战斗数据</h3>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-fist-raised"></i> 战斗力</span>
                        <span class="info-value highlight"><?php echo number_format($rows['power']);?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-crown"></i> 区排名</span>
                        <span class="info-value"><span class="rank-badge"><?php echo $rows['area'];?></span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-globe"></i> 全服排名</span>
                        <span class="info-value"><span class="rank-badge"><?php echo $pran;?></span></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-star"></i> 成就数据</h3>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-medal"></i> 声望值</span>
                        <span class="info-value highlight"><?php echo number_format($rows['fame']);?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-award"></i> 成就点</span>
                        <span class="info-value highlight"><?php echo number_format($rows['achieve']);?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-star"></i> 冒险星</span>
                        <span class="info-value highlight"><?php echo $rows['star'];?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-gamepad"></i> 游戏进度</h3>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-map"></i> 推图进度</span>
                        <span class="info-value highlight"><?php echo $rows['sche'];?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-dungeon"></i> 试炼进度</span>
                        <span class="info-value highlight"><?php echo $rows['tow'];?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-chart-line"></i> 战绩</span>
                        <span class="info-value highlight"><?php echo $rows['record'];?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-clock"></i> 时间信息</h3>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user-clock"></i> 最后在线</span>
                        <span class="info-value"><?php echo $rows['uptime'];?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-sync-alt"></i> 数据更新</span>
                        <span class="info-value"><?php echo $rows['updata_time'];?></span>
                    </div>
                </div>
                <!-- 战力变化曲线 -->
                <div class="info-card">
                  <h3><i class="fas fa-chart-line"></i> 战力变化</h3>
                  <div style="height:220px;">
                    <canvas id="powerChart"></canvas>
                  </div>
                </div>
            </div>

            <!-- 阵容展示区域 -->
            <div class="formation-section">
                <h2 class="formation-title">
                    <i class="fas fa-users"></i> 当前阵容
                </h2>
                <div class="formation-grid">
                    <?php
                    for($i=0;$i<6;$i++){
                        $pos=$i+1;
                        echo "<div class='formation-item' onclick='showHero($pos)'>";
                        // echo "<img src='{$imgname[$i]}' alt='英雄 $pos'>";
                        echo "<img src='{$imgname[$i]}' alt='英雄{$num[$i]}' data-fid='{$num[$i]}'>";
                        echo "</div>";
                        
                    }
                    // for($i = 0; $i < 6; $i++) {
                    //     if(isset($imgname[$i])) {
                    //         echo "<div class='formation-item'>";
                    //         echo "<img src='$imgname[$i]' alt='英雄 " . ($i+1) . "'>";
                    //         echo "</div>";
                    //     }
                    // }
                    ?>
                </div>
            </div>
        </div>
        <?php
        /* ========= 1. 读库（无论有没有数据都补 6 条） ========= */
        $stmt = $pdo->prepare("SELECT * FROM player_heroes WHERE uid = :uid ORDER BY position");
        $stmt->execute(['uid' => $uid]);
        $rowsHero = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $heroMap = [];
        for ($i = 1; $i <= 6; $i++) {
            $heroMap[$i] = [
                'position'   => $i,
                'hp'         => null,
                'attack'     => null,
                'defense'    => null,
                'skill'      => null,
                'addattack'  => null,
                'adddefense' => null,
                'power'      => null,
                'fid' => (string)($num[$i-1] ?? '000'),
            ];
            // $heroMap[$i]['fid'] = $num[$i-1] ?? '000';
            
        }
        foreach ($rowsHero as $h) {           // 把库里有值的覆盖
            $heroMap[$h['position']] = $h;
        }
        $heroJson = json_encode(array_values($heroMap), JSON_UNESCAPED_UNICODE);
        ?>
        
        <!-- ========= 2. 属性卡片 ========= -->
        <style>
        .hero-stats-card{margin:10px auto 0;max-width:600px;background:#fff;border-radius:15px;padding:25px;box-shadow:0 8px 25px rgba(0,0,0,.1);}
        .hero-stats-card h3{color:#4facfe;margin-bottom:15px;display:flex;align-items:center;gap:10px}
        .hero-stats-card .info-item{border-bottom:1px solid rgba(0,0,0,.05);padding:5px 0;display:flex;justify-content:space-between}
        .hero-stats-card .info-item:last-child{border:none}
        .hero-stats-card .info-value{min-width:60px;text-align:right}
        </style>
        
        <div class="hero-stats-card">
            <h3><i class="fas fa-user"></i> <span id="hero-title">1 号英雄 属性</span></h3>
            <div class="info-item"><span class="info-label"><i class="fas fa-heart"></i> 生命值</span>
                <span class="info-value highlight" id="h-hp">?</span></div>
            <div class="info-item"><span class="info-label"><i class="fas fa-paw"></i> 攻击力</span>
                <span class="info-value highlight" id="h-attack">?</span></div>
            <div class="info-item"><span class="info-label"><i class="fas fa-shield-alt"></i> 防御力</span>
                <span class="info-value highlight" id="h-defense">?</span></div>
            <div class="info-item"><span class="info-label"><i class="fas fa-jedi"></i> 必杀值</span>
                <span class="info-value highlight" id="h-skill">?</span></div>
            <div class="info-item"><span class="info-label"><i class="fas fa-plus"></i> 追加攻击</span>
                <span class="info-value highlight" id="h-addattack">?</span></div>
            <div class="info-item"><span class="info-label"><i class="fas fa-plus"></i> 追加防御</span>
                <span class="info-value highlight" id="h-adddefense">?</span></div>
            <div class="info-item"><span class="info-label"><i class="fas fa-fist-raised"></i> 战斗力</span>
                <span class="info-value highlight" id="h-power">?</span></div>

<!-- ===== 2. 装备-首饰-符石容器（结构不变，只改 id） ===== -->
<div class="equip-line">
  <!-- 装备 -->
  <div class="equip-mini">
    <h4><i class="fas fa-wand-magic"></i> 装备</h4>
    <div class="equip-mini-grid" id="gear-equip">
      <!-- 第一次由 PHP 渲染 1 号位，后续 JS 换 -->
      <?php
        $eq=explode('-',$heroMap[1]['equips']??'0000-0000-0000-0000-0000-0000');
        for($row=0;$row<3;$row++){
          $id=$eq[$row]??'0000';     $src="img/equip/{$id}.jpg";     if(!file_exists($src))$src='img/equip/0000.jpg';     echo '<img src="'.$src.'" alt="'.($row+1).'">';
          $id=$eq[$row+3]??'0000';   $src="img/equip/{$id}.jpg";     if(!file_exists($src))$src='img/equip/0000.jpg';     echo '<img src="'.$src.'" alt="'.($row+4).'">';
        }
      ?>
    </div>
  </div>

  <!-- 首饰 -->
  <div class="equip-mini">
    <h4><i class="fas fa-ring"></i> 首饰</h4>
    <div class="equip-mini-grid" id="gear-jewelry">
      <?php
        $jw=explode('-',$heroMap[1]['jewelrys']??'0000-0000-0000-0000-0000-0000');
        for($row=0;$row<3;$row++){
          $id=$jw[$row]??'0000';     $src="img/jewelry/{$id}.jpg";     if(!file_exists($src))$src='img/jewelry/0000.jpg';     echo '<img src="'.$src.'" alt="'.($row+1).'">';
          $id=$jw[$row+3]??'0000';   $src="img/jewelry/{$id}.jpg";     if(!file_exists($src))$src='img/jewelry/0000.jpg';     echo '<img src="'.$src.'" alt="'.($row+4).'">';
        }
      ?>
    </div>
  </div>

  <!-- 符石 -->
  <div class="equip-mini">
    <h4><i class="fas fa-gem"></i> 符石</h4>
    <div class="equip-mini-grid" id="gear-runes">
      <?php
        $ru=explode('-',$heroMap[1]['runes']??'0000-0000-0000-0000-0000-0000');
        for($row=0;$row<3;$row++){
          $id=$ru[$row]??'0000';     $src="img/runes/{$id}.jpg";     if(!file_exists($src))$src='img/runes/0000.jpg';     echo '<img src="'.$src.'" alt="'.($row+1).'">';
          $id=$ru[$row+3]??'0000';   $src="img/runes/{$id}.jpg";     if(!file_exists($src))$src='img/runes/0000.jpg';     echo '<img src="'.$src.'" alt="'.($row+4).'">';
        }
      ?>
    </div>
  </div>
</div>



        </div>
        <script>
            /* 把 PHP 数组直接变成 JS 数组 */
            const chartLabels = <?= json_encode($dates) ?>;
            const chartData   = <?= json_encode($powers) ?>;
            
            /* 没有数据时给一条提示 */
            if (!chartLabels.length) {
              document.getElementById('powerChart')
                      .parentNode
                      .innerHTML = '<p style="text-align:center;color:#999;">暂无战力记录</p>';
            } else {
              const ctx = document.getElementById('powerChart');
              new Chart(ctx, {
                type: 'line',
                data: {
                  labels: chartLabels,
                  datasets: [{
                    label: '战力',
                    data: chartData,
                    borderColor: '#4facfe',
                    backgroundColor: 'rgba(79, 172, 254, 0.15)',
                    tension: 0.25,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                  }]
                },
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: { display: false }
                  },
                  scales: {
                    y: {
                      ticks: {
                        callback: v => v >= 1e8 ? (v/1e8).toFixed(1)+'亿' :
                                       v >= 1e4 ? (v/1e4).toFixed(1)+'万' : v
                      }
                    }
                  }
                }
              });
            }
        </script>
        <!-- ========= 3. 工具函数 + 切换逻辑 ========= -->
        <script>
            const heroList = <?= json_encode($heroMap, JSON_UNESCAPED_UNICODE); ?>;
            
            /* 万/亿省略 */
            function fmt(v) {
                if (v === null) return '?';
                const raw = v.toLocaleString();          // 原数据（千分位）
                if (v < 100000) return `<span class="raw-num">${raw}</span>`; // 低于10万，无括号
            
                let simple;
                if (v < 100000000) simple = (v / 10000).toFixed(2) + '万';
                else               simple = (v / 100000000).toFixed(2) + '亿';
            
                // 原数据 + 括号简约数据，两种颜色
                return `<span class="raw-num">${raw}</span><span class="simple-num">(${simple})</span>`;
            }
            
            function showHero(pos) {
                const h = heroList[pos];
                if (!h) return;
                // const h = heroList[pos - 1];
                // document.getElementById('hero-title').innerText = pos + ' 号英雄 属性';
                const imgEl   = document.querySelector(`.formation-item:nth-child(${pos}) img`);
                const fid     = imgEl ? imgEl.dataset.fid : '000';
                const hName   = heroNameMap[fid] || (pos + '号英雄');
                document.getElementById('hero-title').innerText = hName + ' 属性';
            
                // 更新属性值
                document.getElementById('h-hp').innerHTML        = fmt(h.hp);
                document.getElementById('h-attack').innerHTML    = fmt(h.attack);
                document.getElementById('h-defense').innerHTML   = fmt(h.defense);
                document.getElementById('h-skill').innerHTML     = fmt(h.skill);
                document.getElementById('h-addattack').innerHTML = fmt(h.addattack);
                document.getElementById('h-adddefense').innerHTML= fmt(h.adddefense);
                document.getElementById('h-power').innerHTML     = fmt(h.power);
            
                /* 装备 / 首饰 / 符石 */
                const updateGear = (gear, folder) => {
                    const box = document.getElementById(`gear-${folder}`);
                    box.innerHTML = '';
                    for (let i = 0; i < 3; i++) {
                        const id1 = gear[i]   || '0000';
                        const id2 = gear[i+3] || '0000';
                        box.innerHTML += `
                            <img src="img/${folder}/${id1}.jpg" onerror="this.src='img/${folder}/0000.jpg'">
                            <img src="img/${folder}/${id2}.jpg" onerror="this.src='img/${folder}/0000.jpg'">
                        `;
                    }
                };
                updateGear(h.equips.split('-'),   'equip');
                updateGear(h.jewelrys.split('-'), 'jewelry');
                updateGear(h.runes.split('-'),    'runes');
            }
            
            /* 页面加载完默认 1 号英雄 */
            document.addEventListener('DOMContentLoaded', () => showHero(1));
        </script>
    
    
        <div class="back-button">
            <button onclick="window.history.back();" class="btn-back">
                <i class="fas fa-arrow-left"></i> 返回排行榜
            </button>
        </div>
    </div>
    
</body>
</html>
