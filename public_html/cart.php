<?php
session_start();
require_once(__DIR__."/sys/dbinit.php");
require_once(__DIR__."/sys/id.php");
if(isset($_GET['pid'])&&isset($_GET['sid'])){
    $pid=htmlspecialchars($_GET['pid'], ENT_QUOTES);
    $sid=htmlspecialchars($_GET['sid'], ENT_QUOTES);
    if(isset($_GET['act'])){$db->query("DELETE FROM t21cart WHERE mid='$id' AND pid=$pid AND sid=$sid");}
    if(!(isset($_GET['act']))||$_GET['act']=='UPD'){
        $ps = $db->prepare("INSERT INTO t21cart(mid,pid,sid,tm) VALUES (?,?,?,?)");
        $num=0;
        for($i=1;$i<=$_GET['num'];$i++){
            $ps->execute(array($id,$pid,$sid,date('YmdHis')));
            if ($ps->rowCount()==1){$num++;}
        }
    }
}
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <title>買い物かご</title>
    <link rel="stylesheet" href="css/cart.css" type="text/css">
  </HEAD>

  <BODY>

    <?php
if(isset($_SESSION['na'])){echo$_SESSION['na']."　様";}
echo '<div id="index"><table id="indextable">';
echo '<tr><th>画&リンク</th><th>品名</th><th>数量</th><th>単価</th><th>小計</th></tr>';
$subtotal=0;$g=0;$stkerr=false;
$sql="SELECT t12story.pid AS pid,t12story.id AS sid,h2,photo,num,price,size1,size2,size3,g,stk FROM t12story JOIN q21cart ON t12story.pid=q21cart.pid AND t12story.id=q21cart.sid JOIN t22goods ON t12story.pid=t22goods.pid AND t12story.id=t22goods.sid WHERE mid='$id' ORDER BY tm";
$pays=$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
foreach($pays as $i=>$r){
    if($r["num"]>$r["stk"]){
        $stkerr=true;
        $num=$r["stk"];$out="<br><font color='red'>在庫不足".strval($r["num"]-$r["stk"])."個</font>";
    }else{
        $num=$r["num"];$out="";
    }
    $price=$r['price']*$num;
    $str='<INPUT TYPE="hidden" NAME="pid" Value='.$r['pid'].'><INPUT TYPE="hidden" NAME="sid" Value='.$r['sid'].'>';
    echo'<tr><td><a href="./diary.php?p='.$r['pid'].'#n'.$r['sid'].'"><img class="indeximg" src="./img/'.$r['pid'].'/s-'.trim($r['photo'],'`').'"></a></td>'
    .'<td>',$r['h2'],'<form action="cart.php" method="GET"><INPUT TYPE="submit" VALUE="削除">'.$str.'<INPUT TYPE="hidden" NAME="act" value="DEL">
    </form></td><td><form action="cart.php" method="GET"><INPUT TYPE="number" NAME="num" style="width:3em;" value="'.$num.'"><br>
    <INPUT TYPE="submit" VALUE="変更">'.$out.$str.'<INPUT TYPE="hidden" NAME="act" value="UPD"></form></td><td align="right">'.$r['price'].'円</td>
    <td align="right">'.$price.'円</td></tr>';
    $subtotal+=$price;$g+=$r['g']*$num;//$size_x[]=$r["size1"];$size_y[]=$r["size2"];$size_z[]=$r["size3"];
    $size[]=array("x"=>$r["size1"],"y"=>$r["size2"],"z"=>$r["size3"],"n"=>$num,"g"=>$r["g"]);
}
echo'<tr><th colspan="4">商品計</th><td id="subtotal" align="right">'.number_format($subtotal).'円</td></tr>';
$r=$db->query("SELECT na,post,pref,addr,tel FROM t14member WHERE id='$id'")->fetch();
if($r['na']&&!isset($_GET['payidAddress'])&&strlen($r['na'])>1&&strlen($r['post'])>6&&strlen($r['pref'])>2&&strlen($r['addr'])>4&&strlen($r['tel'])>8){
    $na=$r['na'];$post=$r['post'];$pref=$r['pref'];$addr=$r['addr'];$tel=$r['tel'];
}else{
    if(isset($_SESSION['access_token'])){
        $params = array(
        'access_token' => $_SESSION['access_token']
        );
        $res = file_get_contents('https://api.pay.jp/u/v1/'.'addresses?'.http_build_query($params));
        $address=json_decode($res, true);
        $na=$address['last_name']." ".$address['first_name'];
        $post=substr($address['address_zip'],0,3)."-".substr($address['address_zip'],3,4);
        $pref=$address['address_state'];
        $addr=$address['address_city'].$address['address_line1'].$address['address_line2'];
        $tel=$address['phone'];
    }else{
        $na="";$post="";$pref="";$addr="";$tel="";
    }
}
echo'<tr><th colspan="4">送料</th><td id="postage" align="right"></td><tr><th colspan="4">合計金額</th>
<td align="right" id="total"></td></table></div>';
echo'<div id="pay">';
array_unshift($pays,array('pid'=>0,'sid'=>0,'price'=>0,'num'=>1));//送料分のinputを作成する
if(!$stkerr&&$subtotal){include(__DIR__.'/pay/pay.php');}
$r=$db->query("SELECT pid,sid FROM t21cart WHERE mid='$id' ORDER BY auto DESC")->fetch();
if($r['pid']){
    echo '<a href="diary.php?p='.$r{'pid'}.'#n'.$r['sid'].'">お買い物を続ける</a>';
}else{
    echo '<a href="diary.php">お買い物を続ける</a>';
}
echo'</div>';
echo"<div id='address'>お届け先<label>〒</label><input id='post' name='post' size='4' value='$post' minlength='8' maxlength='8' placeholder='326-0101' required>";
echo"住所<label style='display:none;'>都道府県'</label><input id='pref' name='pref' size='6' value='$pref' minlength='3' maxlength='4' placeholder='栃木県' required>";
echo"<label style='display:none;'>市区町村以下</label><input id='addr' name='addr' size='40' value='$addr' minlength='5' maxlength='100' placeholder='足利市松田町2260 C-Life101号室' required>";
echo"<label>氏名</label><input id='na' name='na' size='10' value='$na' minlength='2' maxlength='50' placeholder='宇佐美　徹' required>";
echo"<label>電話</label><input id='tel' name='tel' size='10' value='$tel' minlength='6' maxlength='13' placeholder='050-5873-4712' required>";
if(isset($_SESSION['access_token'])){
    echo'<form action="cart.php" method="GET"><INPUT TYPE="submit" VALUE="PAYIDのお届け先に変更"><INPUT TYPE="hidden" NAME="payidAddress" value="1"></form>';
}
echo'</div>';
if(isset($size)){
    $bag=culcSize($size);
}else{
    $bag["size"]=0;$bag["g"]=0;$bag["pack"]=0;
}

function culcSize($size){
    if(maxSize($size,"x")<=34&&maxSize($size,"y")<=25&&maxSize($size,"z")<=3&&maxSize($size,"g")<=1000){//クリックポスト
        $area=0;$g=0;
        foreach($size as $i=>$v){
            $area+=$v["x"]*$v["y"]*$v["n"];
            $g+=$v["g"]*$v["n"];
        }
        $pack_area=ceil($area/(34*25*0.8));
        $pack_g=ceil($g/1000);
        $pack=($pack_area>$pack_g)?$pack_area:$pack_g;
        if($pack<5&&$g/$pack<=1000){
            $bag["size"]=10;$bag["pack"]=$pack;$bag["g"]=$g;
            return $bag;
        }
    }
    $vol=0;$g=0;
    foreach($size as $i=>$v){
        $vol+=$v["x"]*$v["y"]*$v["z"]*$v["n"];
        $g+=$v["g"]*$v["n"];
    }
    $bag["size"]=ceil(pow($vol,1/3))*3;
    $bag["g"]=$g;$bag["pack"]=1;
    return $bag;
}
function maxSize($array,$column){
    array_multisort(array_column($array, $column),SORT_DESC, $array);
    return $array[0][$column];
}

function culcSize2(){//未定稿
    array_multisort($size1,SORT_DESC,$size2,SORT_DESC,$size3,SORT_DESC,$size);
    $x=$size[0][0];$y=$size[0][1];$z=$size[0][2];$n=$size[0][3];
    if($x<$Z*$n){
        $x=$z*$n;$z=$size[0][0];
    }else if($y<$z*$n){
        $y=$z*$n;$z=$size[0][1];
    }
    $dx=$x;$dy=$y;$dz=$z;
    foreach($size as $i=>$v){
        if($i){
            $xx=$size[$i][0];$yy=$size[$i][1];$zz=$size[$i][2];$n=$size[$i][3];
            if($z>$xx){
                $xx=$zz;$zz=$size[$i][0];
            }else if($z>$yy){
                $yy=$zz;$zz=$size[$i][1];
            }
            if(floor($x/$xx)*floor($y/$yy)<floor($y/$xx)*floor($x/$yy)){
                $xx=$size[$i][1];$yy=$size[$i][0];//縦置
            }
            while($n>0){
                $n-=floor($x/$xx)*floor($y/$yy);
                $z+=$zz;
            }
        }
    }
}
?>
      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/cart.js"></script>
      <script type="text/javascript">
        var size = <?php echo $bag["size"];?>;
        var g = <?php echo $bag["g"];?>;
        var pack = <?php echo $bag["pack"];?>;
      </script>
  </BODY>

  </HTML>