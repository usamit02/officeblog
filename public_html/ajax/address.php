<?php
session_start();
require_once (__DIR__.'/../sys/dbinit.php');
require_once (__DIR__.'/../sys/id.php');
$ip=$_SERVER['REMOTE_ADDR'];
$host=gethostbyaddr($ip);
$r=$db->query("SELECT id FROM t14member WHERE id='$id';")->fetch();
if($r){
    $ps=$db->prepare("UPDATE t14member SET na=?,post=?,pref=?,addr=?,tel=?,ip=?,host=?,fix=? WHERE id='$id';");
    $error=($ps->execute(array($_POST['na'],$_POST['post'],$_POST['pref'],$_POST['addr'],$_POST['tel'],$ip,$host,date('YmdHis'))))?0:1;
}else{
    $ps=$db->prepare("INSERT INTO t14member(id,na,post,pref,addr,tel,ip,host,fix) VALUES (?,?,?,?,?,?,?,?,?)");
    $error=($ps->execute(array($id,$_POST['na'],$_POST['post'],$_POST['pref'],$_POST['addr'],$_POST['tel'],$ip,$host,date('YmdHis'))))?0:1;
}
if($error||$ps->rowCount()!=1){
    $json[]="データーベースエラーによりお届け先を更新できませんでした。";
}else{
    $json[]="ok";
}
header('Content-type: application/json');
echo json_encode($json);
?>