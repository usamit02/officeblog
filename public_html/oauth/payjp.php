<?php
session_start();
if(isset($_POST['autologin'])){$_SESSION['autologin']=true;}
$_SESSION['state']=md5(microtime().mt_rand());
$_SESSION['nonce']=md5(microtime().mt_rand());
$params=array(
'response_type'=>'code',
'client_id'=>'5ed208b0c31d21f245a044c9ccada8b0a2e4ea17',
'scope'=>'accounts',
'state'=>$_SESSION['state']
);
$res = file_get_contents('https://id.pay.jp/.oauth2/authorize'.'?'.http_build_query($params));
exit();