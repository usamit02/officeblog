<?php
session_start();
$p=isset($_GET['p'])?htmlspecialchars($_GET['p'], ENT_QUOTES):1;
$o=isset($_GET['o'])?htmlspecialchars($_GET['o'], ENT_QUOTES):1;
?>
  <!--nobanner-->
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>記事一覧</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="https://ss1.xrea.com/clife.s17.xrea.com/clife/rss.xml" />
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
      (adsbygoogle = window.adsbygoogle || []).push({
        google_ad_client: "ca-pub-7398376455177947",
        enable_page_level_ads: true
      });
    </script>
  </HEAD>

  <BODY>
    <?php
include_once(__DIR__.'/include/head.php');
require_once(__DIR__."/sys/dbinit.php");
echo '<div id="indexdiv"><table id="indextable">';
echo '<tr><th>画＆リンク</th><th>題</th><th>うんちく</th>';
for ($i=1; $i<5; $i++) {
    $updn=($i%2)?'▼':'▲';
    $btn[$i]='<a class="pagerbutton" href="./index.php?o='.$i.'&p='.$p.'">'.$updn.'</a>';
}
echo '<th><div class="updown">'.$btn[1].'<div class="udtext">作成日</div>'.$btn[2].'</div></th>';
echo '<th><div class="updown">'.$btn[3].'<div class="udtext">PV</div>'.$btn[4].'</div></th></tr>';
$orders=array(1=>'upd DESC','upd','pv DESC','pv');
$L=($p-1)*10;
$order=$orders[$o];
$rs=$db->query("SELECT id,photo,na,title,h1,upd,pv FROM q11index ORDER BY $order LIMIT $L,10;");
while ($r=$rs->fetch()) {
    $str='<tr><td><a href="diary.php?p='.$r['id'];
    $str=$str.'"><img class="indeximg" src="./img/'.$r['id'].'/s-'.trim($r['photo'], '`').'"></a></td>';
    echo $str."<td>{$r['title']}</td><td>{$r['h1']}</td><td>".date('Y年n月j日', strtotime($r['upd'])).'</td><td align="right">'.$r['pv'].'</td></tr>';
}
echo '</table></div>';
$maxp=$db->query("SELECT count(id) AS maxid FROM t11page")->fetchcolumn();
$strp='<div id="pager">';
for ($i=1; $i<=ceil($maxp/10); $i++) {
    if ($i==$p) {
        $strp=$strp.'<div id="currentpage">'.$i.'</div>';
    } else {
        $strp=$strp.'<a class="pagerbutton" href="./index.php?p='.$i.'&o='.$o.'">'.$i.'</a>';
    }
}
echo $strp."</div>";
?>

  </BODY>

  </HTML>