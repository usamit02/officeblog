<?php
session_start();
require_once (__DIR__.'/../sys/dbinit.php');
$pref=$_POST['pref'];$size=$_POST['size'];$g=$_POST['g'];$pack=$_POST['pack'];
if($pack&&$size&&$g){
    $g=$g/$pack;
    $r=$db->query("SELECT MIN(t26postage.price) AS minprice FROM t26postage JOIN t05pref ON t26postage.region=t05pref.region WHERE t05pref.id=$pref AND t26postage.size>=$size AND t26postage.g>=$g;")->fetch();
    if($r['minprice']){
        $json=$r['minprice']*$pack;
    }else{
        $json="送料計算に失敗しました。商品を減らしてみてください。";
    }
}else{
    $json=0;
}
header('Content-type: application/json');
echo json_encode($json);
?>