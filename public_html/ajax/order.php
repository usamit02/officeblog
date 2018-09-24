<?php
session_start();
require_once (__DIR__.'/../sys/dbinit.php');
require_once (__DIR__.'/../sys/id.php');
$pay_day=$_GET['pay_day'];
$html="<table class='items'><tr><th>品名</th><th>数量</th><th>単価</th><th>計</th></tr>";$postage="";
$rs=$db->query("SELECT t27pay.pid as pid,h2,num,price FROM t27pay LEFT JOIN t12story ON t27pay.pid=t12story.pid AND t27pay.sid=t12story.id WHERE t27pay.mid='$id' AND pay_day='$pay_day' ORDER BY price;");
if($rs){
    while($r=$rs->fetch()){
        $price=number_format($r['price']);
        if($r['pid']){
            $num=number_format($r['num']);
            $html.="<tr><td>".$r['h2']."</td><td>$num</td><td>$price</td><td>".number_format($num*$price)."</td></tr>";
        }else if($r['pid']==0){
            $postage="<tr><th colspan='3'>送料</th><td>$price</td></tr>";
        }
    }
}else{
    $html="内訳の取得に失敗しました。";
}
$html.="$postage</table>";
header('Content-type: text/plain; charset=UTF-8');
echo $html;
?>