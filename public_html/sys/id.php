<?php
if(isset($_SESSION['id'])){
    $id=$_SESSION['id'];
    $name=$_SESSION['na'];
    $rnk=$_SESSION['rnk'];
}else{
    if(isset($_COOKIE{'token'})){
        $tm=new DateTime("- 30 days");
        $id=$db->query("SELECT id FROM t16autologin WHERE token='".$_COOKIE['token']."' AND tm>'".$tm->format('Y-m-d H:i:s')."'")->fetchcolumn();
        if($id){
            $_SESSION['id']=$id;
            $rs14=$db->query("SELECT id,na,rnk,mail FROM t14member WHERE id='$id'");
            if($r=$rs14->fetch()){
                $name=$r['na'];
                $rnk=$r['rnk'];
                $mail=$r['mail'];
                $_SESSION['na']=$name;
                $_SESSION['rnk']=$rnk;
                $_SESSION['mail']=$mail;
                require(__DIR__.'/autologin.php');
            }
        }
    }else{
        if(isset($_COOKIE['tid'])){
            $id=$_COOKIE['tid'];
            $rnk=0;
            setcookie('tid',$id,time()+60*60*24*30);
        }
    }
}
if(!isset($id)){
    $id=md5(microtime().mt_rand());
    setcookie('tid',$id,time()+60*60*24*30);
    $rnk=0;
}
?>