<?php
session_start();
require_once(__DIR__."/sys/dbinit.php");
require_once(__DIR__."/sys/id.php");
$pid=$_GET['pid'];
$sid=$_GET['sid'];
$r=$db->query("SELECT pid FROM t27pay WHERE mid='$id' AND pid=$pid AND sid=$sid;")->fetch();
if($r){
    if(isset($_GET['file'])){
        $file=__DIR__."/down_pay/$pid/".$_GET['file'];
        if(!file_exists($file)){
            echo "ファイルが存在しません。";exit;
        }else if(($content_length=filesize($file))==0){
            echo "ファイルサイズが0です。";exit;
        }else{
            $basic = array(
            'User-Agent: My User Agent 1.0',
            'Authorization: Basic '.base64_encode("$dbname:$password"),
            );
            $options = array('http' => array('header' => implode("\r\n", $basic )));
            $res= file_get_contents($file, false, stream_context_create($options));
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Content-Length: '.$content_length);
            header('Content-Type: application/octet-stream');
            echo $res;
        }
    }else{
        header("Location:diary.php?p=$pid#n$sid");
    }
}else{
    ?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <title>コンテンツ販売</title>
    <link rel="stylesheet" href="css/cart.css" type="text/css">
  </HEAD>

  <BODY>
    <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
    <script type="text/javascript">
      function guestPay() {
        $("#payjp_checkout_box input[type='button']").click();
      }
    </script>
    <?php
    if(isset($_SESSION['na'])){echo $_SESSION['na']." 様";}
    $rs=$db->query("SELECT price FROM t25down WHERE pid=$pid AND sid=$sid;");
    if($r=$rs->fetch()){
        $total=$r['price'];
        echo "<div>支払金額:".$total."円<div style='display:flex;'>";
        $pays[0]['pid']=$pid;
        $pays[0]['sid']=$sid;
        $pays[0]['num']=1;
        $pays[0]['price']=$total;
        include(__DIR__."/pay/pay.php");
    }else{
        echo '<div>システムエラー　価格が設定されていません。';
    }
    echo '<a href="diary.php?p='.$pid.'#n'.$sid.'">購入せずに戻る</a></div></div></body></html>';
}