<?php
    $tid=$_POST['tid'];//tid（int）必填——[例：12345]
    $teamname=$_POST['teamname'];//军团名（text）新id必填——[例：张三军团]
    $popularity=$_POST['popularity'];//繁荣（int）新id必填——[例：666]
    $level=$_POST['level'];//等级（smallint）新id必填——[例：200]
    $nofpeople=$_POST['nofpeople'];//人数（smallint）新id必填——[例：99]
    $server=$_POST['server'];//区服（text）新id必填——[例：S1.一区]
    $zone=$_POST['zone'];//大区（tinyint）新id必填——[例：0]  1是国服，2是国际服，,3是英文服，0是测试
    $ps=$_POST['ps'];//备注（text）可不填——[例：任意字符串]
    $percapita=$_POST['percapita'];//人均繁荣（int）可不填——[例：999]
    
    // $tid=55;
    // $teamname="老登镇夷府";
    // $popularity=261814120;
    // $level=200;
    // $nofpeople=173;
    // $server="S1-老登总部";
    // $zone=2;
    // $ps="";
    // $percapita=1513371;
    //正则矫正
    $server = preg_replace('/一/', '-', $server);
    //允许空值↓
    if(empty($teamname)){
        $teamname="未知军团名";
    }
    if(empty($popularity)){
        $popularity=0;
    }
    if(empty($level)){
        $level=0;
    }
    if(empty($nofpeople)){
        $nofpeople=1;
    }
    if(empty($server)){
        $server='S0-未知区';
    }
    if(empty($percapita)){
        $percapita=0;
    }
    //========
    $servernames = 'mysql:host=localhost;dbname=rank_ddata';
    $username = 'rank_ddata';
    $password = 'Pj5hhsH6jxWkRjfN';
    $pdo = new PDO($servernames,$username,$password);
    $pdo->query('SET NAMES utf8');
    //echo time();
    $keydata = $pdo->query("SELECT * FROM team_rank WHERE tid='{$tid}'");
    $rows = $keydata->fetch();
    $updatatime = date('y-m-d H:i:s',time());
    if ($rows) {
        // if(empty($_POST['teamname'])){
        //     $teamname=$rows['teamname'];
        // }
        // if(empty($_POST['popularity'])){
        //     $popularity=$rows['popularity'];
        // }
        // if(empty($_POST['percapita'])){
        //     $percapita=$rows['percapita'];
        // }
        // if(empty($_POST['nofpeople'])){
        //     $nofpeople=$rows['nofpeople'];
        // }
        // if(empty($_POST['level'])){
        //     $level=$rows['level'];
        // }
        // if(empty($_POST['server'])){
        //     $server=$rows['server'];
        // }
        // if(empty($_POST['zone'])){
        //     $zone=$rows['zone'];
        // }
        // if(empty($_POST['ps'])){
        //     $ps=$rows['ps'];
        // }
        if ($rows['zone'] == $zone) {
            // $inset=$pdo->exec("UPDATE team_rank set teamname='{$teamname}',popularity=$popularity,level=$level,nofpeople=$nofpeople,percapita=$percapita,server='{$server}',zone=$zone,ps='{$ps}' WHERE tid='{$tid}'");
            // if($inset==1){
            //     echo "tid:".$tid." 已存在，数据更新成功!";
            // }else{
            //     echo "更新失败！请确定参数类型是否正确！";
            // }
            $stmt = $pdo->prepare("UPDATE team_rank SET teamname = :teamname, popularity = :popularity, level = :level, nofpeople = :nofpeople, percapita = :percapita, server = :server, zone = :zone, ps = :ps WHERE tid = :tid");
            $stmt->execute([
                ':teamname' => $teamname,
                ':popularity' => $popularity,
                ':level' => $level,
                ':nofpeople' => $nofpeople,
                ':percapita' => $percapita,
                ':server' => $server,
                ':zone' => $zone,
                ':ps' => $ps,
                ':tid' => $tid,
            ]);
            if ($stmt->rowCount() > 0) {
                echo "tid:".$tid." 已存在，数据更新成功!";
            } else {
                echo "更新失败！没有行被更新，可能是因为数据没有变化。";
            }

        }else{
            echo "更新失败！同id军团！";
            $str = $zone."|".$server."|".$tid."|同名|".date('y-m-d H:i:s',time());
            file_put_contents('log.txt', $str, FILE_APPEND);
        }
        
        // echo "123"."——操作时间戳:".date('y-m-d H:i:s',time());
    }else {
        $inset=$pdo->exec("INSERT INTO team_rank (tid,teamname,popularity,level,nofpeople,ps,percapita,server,zone) VALUES('{$tid}','{$teamname}',$popularity,$level,$nofpeople,'{$ps}',$percapita,'{$server}',$zone)");
        // echo "222"."——操作时间戳:".date('y-m-d H:i:s',time());
        if($inset==1){
            echo "tid:".$tid." 不存在，已生成新数据添加到数据库中!";
        }else{
            echo "更新失败！请确定参数类型是否正确，以及是否有留空选项。";
        }
    }
    echo "\n——操作时间戳:".date('y-m-d H:i:s',time());
    // echo $name;