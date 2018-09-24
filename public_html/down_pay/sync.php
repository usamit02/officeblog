<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>

<body>
  <?php
$sync = date('Y-m-d H:i:s');
require_once(__DIR__."/../sys/dbinit.php");
require_once(__DIR__.'/../pay/payjp/init.php');
require_once(__DIR__.'/../pay/payinit.php');
file_put_contents(__DIR__."/syncerror.txt","");
$i=1;
$cnt['INSERT']=0;
$cnt['DELETE']=0;
$cnt['UPDATE']=0;
$exe{'INSERT'}=0;
$exe['DELETE']=0;
$exe['UPDATE']=0;
$rlt['INSERT']=0;
$rlt['DELETE']=0;
$rlt['UPDATE']=0;
$error="error";
try{
    $db->beginTransaction();
    while (isset($_POST["p$i"])) {
        $sql=str_replace("<AnD>","&",$_POST["p$i"]);  //xlmhttp post key1=value&key2=value...の&ではない&を<AnD>で置き換えてあるのを戻す。
        $sql=str_replace("<pLs>","+",$sql);
        $sql=str_replace("&nbsp;"," ",$sql);
        $sql=str_replace("&quot;",'"',$sql);
        $sql=str_replace("`","\'",$sql);
        $sqltyp=mb_substr($_POST["p$i"],0,6);
        $q=$db->prepare($sql);
        $result=$q->execute()&&($sqltyp=="DELETE"||$q->rowCount()==1);
        if($result){
            $rlt[$sqltyp]++;
        }else{
            if($sqltyp=="UPDATE"){
                $error.=":UPDATE ".mb_substr($sql,7,8)." ".mb_substr($sql,mb_strpos($sql, "WHERE") + 7);
            }else if($sqltyp=="INSERT"){
                $error.=":INSERT ".mb_substr($sql,12,8)." ".mb_substr($sql,mb_strpos($sql, "VALUES") + 8,8);
            }else if($sqltyp=="DELETE"){
                $error.=":DELETE".mb_substr($sql,12,8)." ".mb_substr($sql,mb_strpos($sql,"WHERE") + 7);
            }else{
                $error.=":unknown";
            }
            file_put_contents(__DIR__."/syncerror.txt",$sql.PHP_EOL.PHP_EOL,FILE_APPEND);
        }
        $cnt[$sqltyp]+=$q->rowCount();
        $i++;
        $exe[$sqltyp]++;
    }
}catch(exception $e){
    $db->rollback();
    echo 'システムエラー:SQLステートメント'.$_POST["p$i"]."番を実行できませんでした。";
}
if($error=="error"){
    $db->commit();
    $error="syncok";
    $now=date('Y-m-d H:i:s');
    $rs=$db->query("SELECT mid,pay_day,chid FROM t28order WHERE ack_day is null AND cancel_day is not null AND capture_day is null AND refund_day is null;");
    while($r=$rs->fetch()){//ackしてない注文はキャンセルされたら即与信枠解放
        try {
            Payjp\Payjp::setApiKey($paySecret);
            $ch = Payjp\Charge::retrieve($r['chid']);
            $result=$ch->refund();
            if (isset($result['error'])) {
                throw new Exception();
            }
            $q=$db->prepare("UPDATE t28order SET refund_day=? WHERE mid=? AND pay_day=?;");
            $q->execute(array($now,$r['mid'],$r['pay_day']));
        } catch (Exception $e) {
            if (isset($result['error'])) {
                echo $result['error']['message'];
            }else{
                echo "pay jpの内部エラーです。";
            }
            exit;
        }
    }
    $rs=$db->query("SELECT mid,pay_day,chid FROM t28order WHERE ack_day is not null AND send_day is not null AND cancel_day is null AND capture_day is null AND refund_day is null;");
    while($r=$rs->fetch()){//発送時与信枠確定
        try {
            Payjp\Payjp::setApiKey($paySecret);
            $ch = Payjp\Charge::retrieve($r['chid']);
            $result=$ch->capture();
            if (isset($result['error'])) {
                throw new Exception();
            }
            $q=$db->prepare("UPDATE t28order SET capture_day=? WHERE mid=? AND pay_day=?;");
            $q->execute(array($now,$r['mid'],$r['pay_day']));
        } catch (Exception $e) {
            if (isset($result['error'])) {
                echo $result['error']['message'];
            }else{
                echo "pay jpの内部エラーです。";
            }
            exit;
        }
    }
    $ago=date('Y-m-d', strtotime("-3 month"));//一時メンバーのデータは３カ月で削除
    $db->query("DELETE FROM t42comment WHERE left(mid,4)!='acct' AND fix<$ago;");
    $db->query("DELETE FROM t21cart WHERE left(mid,4)!='acct' AND fix<$ago;");
}else{
    $db->rollBack();
}
$syncTable=array("t42comment","t14member","t26postage","t27pay","t28order");
$replaceNull=array("''","##");
foreach($syncTable as $table){
    $rs=$db->query("SELECT * FROM $table WHERE sync is null;");
    while($r=$rs->fetch()){
        $into="";$values="";
        for($i=0;$i<$rs->columncount();$i++){
            $column=$rs->getColumnMeta($i);
            $into.=$column['name'].",";
            if($column['native_type']=="LONG"){
                $values.=$r[$column['name']].",";
            }else if($column['native_type']=="DATETIME"){
                $values.=$column['name']=="sync"?"#".date('Y-m-d H:i:s')."#,":"#".$r[$column['name']]."#,";
            }else{
                $values.="'".$r[$column['name']]."',";
            }
        }
        $into=substr($into,0,strlen($into)-1);
        $values=str_replace($replaceNull,"null",substr($values,0,strlen($values)-1));
        echo"INSERT INTO $table ($into) VALUES ($values);";
    }
    $rs=$db->query("SELECT * FROM $table WHERE fix>sync;");
    while($r=$rs->fetch()){
        $set="";$where="";
        for($i=0;$i<$rs->columncount();$i++){
            $column=$rs->getColumnMeta($i);
            if(in_array("primary_key",$column['flags'])){
                $where.=$column['name']."=";
                if($column['native_type']=="LONG"){
                    $where.=$r[$column['name']]." AND ";
                }else if($column['native_type']=="DATETIME"){
                    $where.="#".$r[$column['name']]."# AND";
                }else{
                    $where.="'".$r[$column['name']]."' AND ";
                }
            }else{
                $set.=$column['name']."=";
                if($column['native_type']=="LONG"){
                    $set.=$r[$column['name']].",";
                }else if($column['native_type']=="DATETIME"){
                    $set.=$column['name']=="sync"?"#".date('Y-m-d H:i:s')."#,":"#".$r[$column['name']]."#,";
                }else{
                    $set.="'".$r[$column['name']]."',";
                }
            }
        }
        $set=str_replace($replaceNull,"null",substr($set,0,strlen($set)-1));
        $where=substr($where,0,strlen($where)-5);
        echo"UPDATE $table SET $set WHERE $where;";
    }
}
echo $error;
?>
</body>

</html>