<?php
require_once 'servername.php';
    $uid=$_POST['uid'];//uid（int）必填——[例：12345]
    $area=$_POST['area'];//区排名（int）新id必填——[例：1]
    $name=$_POST['name'];//角色名（text）新id必填——[例：张三]
    $power=$_POST['power'];//战力（bigint）新id必填——[例：666]
    $level=$_POST['level'];//等级（int）新id必填——[例：500]
    $fame=$_POST['fame'];//声望（bigint）新id必填——[例：666]
    $server=$_POST['server'];//区服（text）新id必填——[例：S1.一区]
    $server = preg_replace('/^s(\d)/i', 'S$1', $server);//将区号的S强制为大写
    $zone=$_POST['zone'];//大区（tinyint）新id必填——[例：0]  1是国服，2是国际服，0是测试
    $ps=$_POST['ps'];//备注（text）可不填——[例：任意字符串]
    $uptime=$_POST['uptime'];//最后在线时间（text）可不填——[例：2024年6月1日/2024-6-1]
    $achieve=$_POST['achieve'];//成就（int）可不填——[例：20000]
    $record=$_POST['record'];//战绩（text）可不填——[例：0败0胜]
    $formation=$_POST['formation'];//阵容（text）可不填——[例：000-000-000-000-000-000]
    $star=$_POST['star'];//冒险星数(int)可不填
    $sche=$_POST['sche'];//推图进度(text)可不填
    $tow=$_POST['tow'];//试练进度(text)可不填
    $eda=$_POST['eda'];//备用文本（text）可不填
    $warning=$_POST['warning'];//警告（char）可不填
    //正则矫正
    // $server = preg_replace('/一/', '-', $server);
    $server = preg_replace('/(?<=S\d)一|(?<=S\d{2})一|(?<=S\d{3})一/', '-', $server);
    //名字矫正
    $server = correctServerName($zone, $server);
    //允许空值↓
    if(empty($achieve)){
        $achieve=0;
    }
    if(empty($star)){
        $star=0;
    }
    if(empty($eda)){
        $eda='0';
    }
    if(empty($formation)){
        $formation='0000-0000-0000-0000-0000-0000';
    }
    if(empty($sche)){
        $sche='0';
    }
    if(empty($tow)){
        $tow='0';
    }
    if(empty($warning)){
        $warning='';
    }
    //========
    $servernames = 'mysql:host=localhost;dbname=rank_ddata';
    $username = 'rank_ddata';
    $password = 'Pj5hhsH6jxWkRjfN';
    $pdo = new PDO($servernames,$username,$password);
    $pdo->query('SET NAMES utf8');
    //echo time();
    $keydata = $pdo->query("SELECT * FROM ranking WHERE uid='{$uid}'");
    $rows = $keydata->fetch();
    $updatatime = date('y-m-d H:i:s',time());
    if ($rows) {
        if(empty($_POST['name'])){
            $name=$rows['name'];
        }
        if(empty($_POST['area'])){
            $area=$rows['area'];
        }
        if(empty($_POST['power'])){
            $power=$rows['power'];
        }
        if(empty($_POST['level'])){
            $level=$rows['level'];
        }
        if(empty($_POST['fame'])){
            $fame=$rows['fame'];
        }
        if(empty($_POST['server'])){
            $server=$rows['server'];
        }
        if(empty($_POST['zone'])){
            $zone=$rows['zone'];
        }
        if(empty($_POST['achieve'])){
            $achieve=$rows['achieve'];
        }
        if(empty($_POST['uptime'])){
            $uptime=$rows['uptime'];
        }else{
            // $uptime = date('Y-m-d H:i:s', $_POST['uptime']);
            $uptime = $_POST['uptime'];
        }
        if(empty($_POST['ps'])){
            $ps=$rows['ps'];
        }
        if(empty($_POST['record'])){
            $record=$rows['record'];
        }
        if(empty($_POST['formation'])){
            $formation=$rows['formation'];
        }
        if(empty($_POST['star'])){
            $star=$rows['star'];
        }
        if(empty($_POST['sche'])){
            $sche=$rows['sche'];
        }
        if(empty($_POST['tow'])){
            $tow=$rows['tow'];
        }
        if(empty($_POST['eda'])){
            $eda=$rows['eda'];
        }
        if(empty($_POST['warning'])){
            $warning=$rows['warning'];
        }
        // if($warning==0){
        //     $warning="";
        // }
        $inset=$pdo->exec("UPDATE ranking set name='{$name}',power=$power,level=$level,fame=$fame,achieve=$achieve,record='{$record}',server='{$server}',zone=$zone,updata_time='{$updatatime}',uptime='{$uptime}',ps='{$ps}',area=$area,formation='{$formation}',star=$star,eda='{$eda}',sche='{$sche}',tow='{$tow}',warning='{$warning}' WHERE uid='{$uid}'");
        if ($inset == 1) {
            /* ===== 是否写入战力日志 ===== */
            $oldPower = $rows['power'];
            
            // 最近一条 log 的时间（无论战力是否相同）
            $lastLog = $pdo->prepare(
                'SELECT log_time
                 FROM   power_log
                 WHERE  uid = :uid
                 ORDER  BY log_time DESC
                 LIMIT  1');
            $lastLog->execute(['uid' => $uid]);
            $lastDay = $lastLog->fetchColumn();
            
            // ===== 新增：间隔少于7天则不录入（最高优先级）=====
            if ($lastDay && strtotime($lastDay) > strtotime('-5 days')) {
                // 距离上次记录不足7天，跳过录入
                echo "uid:".$uid." 已存在，数据更新成功!（战力日志未达5天间隔，跳过）";
            } else {
                // 条件：①战力变化 ②距离上次≥30天（或无记录）
                $needLog = ($power != $oldPower) ||
                           (!$lastDay) ||
                           (strtotime($lastDay) <= strtotime('-30 days'));
                
                if ($needLog) {
                    $pdo->prepare('INSERT INTO power_log(uid, power, log_time) VALUES (?,?,NOW())')
                        ->execute([$uid, $power]);
                    echo "uid:".$uid." 已存在，数据更新成功!（战力日志已记录）";
                } else {
                    echo "uid:".$uid." 已存在，数据更新成功!（战力未变化，跳过日志）";
                }
            }
        } else {
            echo "更新失败！请确定参数类型是否正确！";
        }
        // echo "123"."——操作时间戳:".date('y-m-d H:i:s',time());
    }else {
        if(empty($_POST['uptime'])){
            $uptime = '1000-01-01 00:00:00';
        }
        
        $inset=$pdo->exec("INSERT INTO ranking (uid,name,power,level,fame,server,zone,updata_time,ps,area,achieve,record,uptime,formation,star,eda,sche,tow,warning) VALUES('{$uid}','{$name}',$power,$level,$fame,'{$server}',$zone,'{$updatatime}','{$ps}',$area,'{$achieve}','{$record}','{$uptime}','{$formation}',$star,'{$eda}','{$sche}','{$tow}','{$warning}')");
        // echo "222"."——操作时间戳:".date('y-m-d H:i:s',time());
        if($inset==1){
            /* 新增：首次入库也要记一笔 */
            $pdo->prepare("INSERT INTO power_log(uid,power) VALUES(?,?)")
                ->execute([$uid, $power]);
                
            echo "uid:".$uid." 不存在，已生成新数据添加到数据库中!";
        }else{
            echo "更新失败！请确定参数类型是否正确，以及是否有留空选项。";
        }
    }
    echo "\n——操作时间戳:".date('y-m-d H:i:s',time());
    // echo $name;