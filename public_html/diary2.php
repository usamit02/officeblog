<?php
session_start();
require_once(__DIR__."/sys/dbinit.php");
if (isset($_GET['p'])) {
    $p = htmlspecialchars($_GET['p'], ENT_QUOTES);
} else {
    $r=$db->query("SELECT id FROM t11page WHERE upd =(SELECT MAX(upd) AS lastup FROM t11page);")->fetch();
    $p=$r['id']?$r['id']:1;
}
if ($p==1) {
    header("Location:index.php");
    exit;
}
$r=$db->query("SELECT na,parent,title,h1,upd,rev,pv,comment FROM t11page WHERE id=$p;")->fetch();
if ($r['na']) {
    $title = $r['title'];
    $h1=$r['h1'];
    $upd=date('Y年n月j日', strtotime($r['upd']))."作成";
    $rev=strtotime($r['upd'])<strtotime($r['rev'])?date('Y年n月j日', strtotime($r['rev']))."更新":"";
    $pv=$r['pv']."頁ビュー";
    $pna=$r['na'];
    $parent = $r['parent'];
    $comment = $r['comment'];
} else {
    header("Location:index.php") ;
    exit;
}
if (!(isset($_SESSION['pv'][$p]))) {
    $_SESSION['pv'][$p] = 1;
    $db->query("UPDATE t11page SET pv=pv+1 WHERE id=$p;");
}
include_once(__DIR__.'/sys/id.php');
?>
  <html lang="ja">

  <HEAD>
    <META charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary">
    <?php
echo '<meta property="og:title" content="'.$title.'"/>';
echo '<meta name="description" content="'.$h1.'"/>';
echo '<meta property="og:description" content="'.$h1.'"/>';
echo '<meta property="og:url" content="https://'.$_SERVER['HTTP_HOST'].'/diary.php?p='.$p.'"/>';
echo "<title>$title</title>";
?>
      <link rel="stylesheet" href="css/diary.css" type="text/css">
      <link rel="stylesheet" href="css/nav.css" type="text/css">
      <link rel="stylesheet" href="css/thumb.css" type="text/css">
      <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="https://ss1.xrea.com/clife.s17.xrea.com/clife/rss.xml" />

  </HEAD>

  <BODY>

    <?php
include_once(__DIR__.'/include/head.php');
echo "<div id='main'><div id='diary'><h1 class='h1'>$h1</h1>";
$log=0;$pay=0;
$rs12=$db->query("SELECT id,num,pt,ctl,h2,txt,photo,photo1,photo2,photo3,html FROM t12story WHERE pid=$p ORDER BY num;");
while ($r=$rs12->fetch()) {
    $sid=$r['id'];$h2=$r['h2']; $pt=$r['pt'];$ctl=$r['ctl'];$txt="";
    for ($i=0; $i<4; $i++) {
        $j = ($i) ? $i : null;
        $pht = $r["photo".$j];
        if (isset($pht)&&strlen($pht)>4) {
            $photo[$i] = '<img src="img/'.$p."/".trim($pht, "`").'">';
            $photona[$i]=trim($pht, "`");
        } else {
            $photo[$i]="";
        }
    }
    if(isset($ctl)){
        $tag=getTag($ctl,"<log");
        if($tag){
            if($rnk==0){$log=intval($tag);}
        }
        $tag=getTag($ctl,"<pay");
        if($tag){
            if(!($rnk&&$db->query("SELECT num FROM t27pay WHERE pid=$p AND sid=$sid AND mid='$id';")->fetchcolumn())){
                $split=explode(",",$tag);
                $pay=intval($split[0]);$price=intval($split[1]);
            }
        }
        $tag=getTag($ctl,"<h2");
        if($tag){$h3=$tag;}
    }
    if (isset($h2)) {
        if(isset($h3)){
            if($h3!=""){
                $txt="<div><h2>$h3</h2></div><div><h3 id='n$sid'>$h2</h3></div>";
                $h3="";
            }else{
                $txt="<div><h3 id='n$sid'>$h2</h3></div>";
            }
            
        }else{
            $txt="<div><h2 id='n$sid'>$h2</h2></div>";
        }
    }
    if($pay){
        $pt=70;$pay--;
    }else if($log){
        $pt=60;$log--;
    }else{
        $txt.=isset($r['txt'])?$r['txt']:"";
    }
    echo '<div class="row">';
    if ($pt==0) {//ノーマル
        if ($photo[1]=="") {
            echo '<div class="txt">'.$txt.'</div>'.$photo[0];
        } else {
            if ($photo[2]!=""&&$photo[3]==""&&$txt=="") {
                echo $photo[0].$photo[1];
            } else {
                echo '<div class="thumbtext"><div class="txt">'.$txt.'</div>';
                echo '<ul class="thumblist">';
                for ($i=0; $i<$np; $i++) {
                    echo '<li><a href="img/'.$p."/".$photona[$i].'"><img src="img/'.$p.'/s-'.$photona[$i].'"></a></li>';
                }
                echo '</ul></div>';
                echo '<div class="thumbimg">'.$photo[0].'</div>';
            }
        }
    }
    else if($pt==10||$pt==50){//HTML //コード
        if($pt==50){
            echo"<script type='text/javascript' src='//rawgit.com/google/code-prettify/master/loader/run_prettify.js?skin=sons-of-obsidian'></script>";
        }
        $rr=$db->query("SELECT h2,html FROM t16html WHERE pid=$p AND sid=".$r['id'])->fetch();
        $h2div= isset($rr['h2'])?'<h2 id="'.$r['id'].'">'.$rr['h2'].'</h2></div>':"";
        $html = isset($rr['html']) ? $rr['html'] : "エラー　htmlが見つかりません。";
        $html = $pt==50?"<pre class='prettyprint'>$html</pre>":$html;
        if(strlen($txt)==0&&$photo[0]==""){
            echo"$h2div$html";
        }else if(strlen($txt)){
            echo"<div class='txt'>$txt</div>$html";
        }else{
            echo"$h2div$html".$photo[0]."</div>";
        }
    }else if($pt>10&&$pt<20){//広告//地図//HTML定番//
        $htmlid = isset($r["html"])?$r["html"]:1;
        $html=$db->query("SELECT html FROM t17html WHERE id=$htmlid;")->fetchcolumn();
        $html=$html?$html:'エラー　htmlが見つかりません';
        if ($photo[0]<>"") {
            echo '<div class="txt">'.$txt.$html."</div>".$photo[0];
        } else if(strlen($txt)){
            echo '<div class="txt">'.$txt.'</div>'.$html;
        }else{
            echo$html;
        }
    }else if($pt==60){//閲覧制限要ログイン
        echo"<div class='txt'>$txt"."ご覧になるにはログインしてください。</div>";
    }else if($pt==70){//閲覧有料
        if($price){
            echo"<div class='txt'>$txt<form action='download.php' method='GET' style='display:inline;'><input type='hidden'
            name='pid' value='$p'><input type='hidden' name='sid' value='$sid'><input type='submit' value='$price"
            ."円でご覧になる'></form></div>";
            $price=0;
        }else{
            echo"<div class='txt'>$txt"."有料コンテンツです。</div>";
        }
    }
    echo'</div>';
}
echo'</div><aside>';




//フッター
$sitepath = "<a href='diary.php?p=$p' title='$title'>$pna</a>";
while ($parent) {
    $rs=$db->query("SELECT na,title,parent FROM t11page WHERE id=$parent;");
    if ($r=$rs->fetch()) {
        $sitepath=$sitepath."　＞　<a href='diary.php?p=$parent' title='".$r['title']."'>".$r['na']."</a>";
        $parent=$r['parent'];
    } else {
        break;
}
}
echo"<div id='footer'><div>$upd</div>  <div>$rev</div>  <div>$pv</div>  <div>$sitepath</div></div>";
echo '</aside></div>';
include_once(__DIR__.'/include/nav.html');
function getTag($str,$tag){
    $i=strpos($str,$tag);
    if($i===false){return false;}
    $i+=strlen($tag);
    $j=strpos($str,">",$i);
    if($j===false){return false;}
    return substr($str,$i,$j-$i);
}
?>
      <button type="button" id="menu">
        <span class="bar bar1"></span>
        <span class="bar bar2"></span>
        <span class="bar bar3"></span>
        <span class="menu">MENU</span>
        <span class="close">CLOSE</span>
      </button>
      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/nav.js"></script>
      <script type="text/javascript" src="js/thumb.js"></script>
  </BODY>

  </HTML>
