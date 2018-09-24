<?php
$tm = new DateTime();
$token = bin2hex(openssl_random_pseudo_bytes(16));
$ps=$db->prepare("INSERT INTO t16autologin(id,token,tm) VALUES (?,?,?)");
$ps->execute(array($id,$token,$tm->format('Y-m-d H:i:s')));
$db->query("DELETE FROM t16autologin WHERE id='$id' AND tm<>'".$tm->format('Y-m-d H:i:s')."'");
setCookie("token", $token, time()+60*60*24*30, "/", null, TRUE, TRUE);

//id.phpとdbinit.phpを事前に呼び出し必須