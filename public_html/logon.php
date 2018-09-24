<?php
session_start();
if(isset($_SESSION['callback'])){
    $callback=$_SESSION['callback'];
    unset($_SESSION['callback']);
}
if(isset($_GET['code'])&&isset($_SESSION['state'])&&isset($_GET['state'])){
    if($_GET['state']==$_SESSION['state']||$_GET['state']=='auto'.$_SESSION['state']){
        require_once(__DIR__."/pay/payinit.php");
        $params = array(
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'client_id' => $clientID,
        );
        $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic '.base64_encode("$clientID:$clientSecret"),
        );
        $options = array('http' => array(
        'method' => 'POST',
        'header' => implode("\r\n", $headers),
        'content' => http_build_query($params)
        ));
        $res = file_get_contents('https://api.pay.jp/u/.oauth2/token', false, stream_context_create($options));
        $token = json_decode($res, true);
        $access_token = $token['access_token'];
        $params = array(
        'access_token' => $access_token
        );
        $res = file_get_contents('https://api.pay.jp/u/v1/accounts?'.http_build_query($params));
        $result = json_decode($res, true);
        if(isset($result['id'])){
            $id=$result['id'];
            $ip=$_SERVER['REMOTE_ADDR'];
            require_once(__DIR__."/sys/dbinit.php");
            $rs=$db->query("SELECT id FROM t15black WHERE mid='$id' OR ip='$ip'");
            if($rs->fetch()){
                echo 'あなたのログインは運営者により停止されています。お問い合わせください。';
                die;
            }
            session_regenerate_id(true);
            $name=$result['last_name']." ".$result['first_name'];
            $mail=$result['email'];
            $host=gethostbyaddr($ip);
            $fix=date('Y-m-d H:i:s');
            $_SESSION['id']=$id;
            $_SESSION['na']=$name;
            $_SESSION['mail']=$mail;
            $_SESSION['access_token']=$access_token;
            $rs=$db->query("SELECT rnk FROM t14member WHERE id='$id'");
            if($r=$rs->fetch()){
                $_SESSION['rnk']=$r['rnk'];
                $rs=$db->query("SELECT id FROM t14member WHERE id='$id' AND na='$name' AND mail='$mail' AND ip='$ip'");
                if($rs->fetch()==false){
                    $q=$db->prepare("UPDATE t14member SET na=?,mail=?,fix=?,ip=?,host=? WHERE id='$id'");
                    $q->execute(array($name,$mail,$fix,$ip,$host));;
                }
            }else{
                $q=$db->prepare("INSERT INTO t14member(id,na,rnk,mail,fix,ip,host) VALUES (?,?,?,?,?,?,?)");
                $q->execute(array($id,$name,1,$mail,$fix,$ip,$host));
            }
            //仮IDを本登録
            if(isset($_COOKIE['tid'])){
                $tid=$_COOKIE['tid'];
                $q=$db->prepare("UPDATE t21cart SET mid=? WHERE mid='$tid'");
                $q->execute(array($id));
                $q=$db->prepare("UPDATE t42comment SET mid=? WHERE mid='$tid'");
                $q->execute(array($id));
                $num=$q->rowCount();
                if($num>0){
                    mb_send_mail($mymail,$name.'さんからコメント'.$num.'件投稿されました。',"本文なし");
                }
                $q=$db->prepare("UPDATE t41rss SET id=? WHERE id='$tid'");
                $q->execute(array($id));
                if(file_exists(__DIR__."/rss/rss$tid.html")){rename(__DIR__."/rss/rss$tid.html","rss/rss$id.html");}
                //setcookie("tid",'',time()-1800);
            }
            //オートログイン
            if(substr($_GET['state'],0,4)=='auto'){
                require (__DIR__.'/sys/autologin.php');
            }else{
                $db->query("DELETE FROM t16autologin WHERE id='$id'");
                setCookie("token",'',-1, "/", null, TRUE, TRUE);
            }
        }
    }
}
//$path=str_replace("/logon.php","",parse_url($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']));
if(isset($callback)){
    //$url=$path['path'].$callback;
    header("Location: $callback");
    //header("Location: https://ss1.xrea.com/clife.s17.xrea.com".$callback);
}else{
    //$url=$path['path']."clife/diary.php";
    //header("Location: clife/diary.php");
    header("Location: ".$path['path']."diary.php");
    //header("Location: https://ss1.xrea.com/clife.s17.xrea.com/clife/diary.php");
}
?>