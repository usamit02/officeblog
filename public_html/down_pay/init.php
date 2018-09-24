<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>

<body>
  <?php
$sync = date('Y-m-d H:i:s');
require_once(__DIR__."/../sys/dbinit.php");
file_put_contents(__DIR__."/syncerror.txt","");
$i=1;
$error="error";
$q=$db->query("CREATE VIEW q14lastup AS select max(t11page.upd) AS lastup from t11page;");
/*
try{
while (isset($_POST["p$i"])) {
$sql=str_replace("<pLs>","+",$_POST["p$i"]);
$sql=str_replace("<mNs>","-",$sql);
file_put_contents(__DIR__."/syncerror.txt",$sql.PHP_EOL.PHP_EOL,FILE_APPEND);
$q=$db->prepare($sql);
if(!$q->execute()){
$error.=":p$i";
file_put_contents(__DIR__."/syncerror.txt",$sql.PHP_EOL.PHP_EOL,FILE_APPEND);
}
$i++;
}
if($error=="error"){
$error="syncok";
echo$error;
}
}catch(exception $e){
echo 'システムエラー:SQLステートメント'.$_POST["p$i"]."番を実行できませんでした。";
}
*/
?>
</body>

</html>