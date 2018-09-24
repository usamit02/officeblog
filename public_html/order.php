<?php
session_start();
require_once(__DIR__."/sys/dbinit.php");
require_once(__DIR__."/sys/id.php");
if(isset($_GET['pay_day'])&&isset($_GET['reason'])){
    $reason=htmlspecialchars($_GET['reason'], ENT_QUOTES);
    $payday=htmlspecialchars($_GET['pay_day'], ENT_QUOTES);
    $q=$db->prepare("UPDATE t28order SET cancel_day=?,cancel_reason=?,fix=? WHERE mid=? AND pay_day=?;");
    if(!($q->execute(array(date('Y-m-d H:i:s'),$reason,date('Y-m-d H:i:s'),$id,$payday))&&$q->rowCount()==1)){
        echo"データーベースエラー".$q->errorCode()."によりキャンセル処理に失敗しました。";
        exit;
    }
    $subject=isset($name)?$name:strval($id);
    //mb_send_mail($mymail,$subject."さんに".$payday."の注文をキャンセルされました。","キャンセル理由:".$reason);
}
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <title>注文履歴</title>
    <link rel="stylesheet" href="css/cart.css" type="text/css">
  </HEAD>

  <BODY>

    <?php
include(__DIR__.'/include/login.php');
$referer = $_SERVER['HTTP_REFERER'];
if (isset($referer)) {
    $url = parse_url($referer);
    $ref=substr($url['path'],strlen($url['path'])-8);
    if($ref=="cart.php"){
        echo"お買い上げいただきありがとうございます。";
    }
}
$rs=$db->query("SELECT pay_day,h2,photo,ack_day,send_day,track,cancel_day,t28order.pid AS pid,sid,num,total FROM t28order LEFT JOIN t12story ON t28order.pid=t12story.pid AND t28order.sid=t12story.id WHERE t28order.mid='$id' ORDER BY t28order.fix DESC;")->fetchAll(PDO::FETCH_ASSOC);
if($rs){
    echo '<table id="indextable">';
    echo '<tr><th>注文日</th><th>画&リンク</th><th>品名</th><th>金額</th><th>受注日</th><th>発送日</th><th>追跡番号</th><th>キャンセル</th></tr>';
    foreach($rs as $i=>$r){
        $now=new DateTime();
        $send_date=new DateTime($r['send_day']);
        $cancel=(isset($r['cancel_day'])||$send_date->diff($now)->format('%a') >14)?$r['cancel_day']:
        '<form id="cancel" action="order.php" method="GET" onsubmit="return beforeCancel()">
        <button>キャンセルする</button><input type="hidden" name="pay_day" value="'.$r['pay_day'].'">
        <textarea class="reason" name="reason" minlength="5" maxlength="100" placeholder=
        "理由を５文字以上１００文字以内で必ず入力してください。後からの訂正はできません。"></textarea></form>';
        $other=$r["num"]>1?"他".strval($r['num']-1)."点":"";
        echo'<tr><td>'.$r['pay_day'].'</td><td><a href="./diary.php?p='.$r['pid'].'#n'.$r['sid'].'">
        <img class="indeximg" src="./img/'.$r['pid'].'/s-'.trim($r['photo'],'`').'"></a></td>'
        .'<td>'.$r['h2'].$other.'<button onclick="itemOpen('."'".$r['pay_day']."'".','.$i.')">内訳</button>
        <div id="items'.$i.'"></div></td><td align="right">'.number_format($r['total']).'円</td>
        <td>'.$r['ack_day'].'</td><td>'.$r['send_day'].'</td><td>'.$r['track'].'</td><td>'.$cancel.'</td></tr>';
    }
    echo'</table>';
}else{
    echo"注文履歴はまだありません。<br>";
}
echo'発送日時から２週間以内で不良がある場合のみ返品可能です。<font color="red">返品する前に必ず「キャンセルする」ボタンから手続きしてください。</font><br>';
echo'手続きせず商品を返送された場合受取は拒否し、いかなる損害も賠償いたしません。また、お客様都合による返品の場合代金は返還できません。';
echo'<div align="center"><a href="diary.php">トップページへ</a></div>';
?>
      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/order.js"></script>
  </BODY>

  </HTML>