<?php
set_time_limit(60);
$preurls = array(
"http://inakalib.com/feed",
"http://henan.blog.fc2.com/?xml",
"http://i401gou.blog.fc2.com/?xml",
"http://ibaya.hatenablog.com/rss",
"http://owl888.blog.fc2.com/?xml",
"https://cnc-selfbuild.blogspot.com/feeds/posts/default?alt=rss",
"http://mugigin4841.blog.fc2.com/?xml",
"http://ron2012simplelife.blog.fc2.com/?xml",
"http://67010450.at.webry.info/rss/index.rdf",
);
$row=2;#------------------------------------１ブログあたり取得記事数
$top=10;#-----------------------------------表示する最新記事数
$min=60;#-----------------------------------更新間隔（分）
$message="";$error="";$reload=false;
$pleaselogin=substr($id,4)=="acct"?"変更を確定するにはログインしてください。":"";
if(isset($_POST['rssdel'])){
    $rssurl=$_POST['rssdel'];
    $ps=$db->query("DELETE FROM t41rss WHERE id='$id' AND url='".$rssurl."'");
    $reload=true;
    if($ps->rowCount()==0){
        $db->query("DELETE FROM t41rss WHERE id='$id'");
        $ps=$db->prepare("INSERT INTO t41rss(id,url) VALUES (?,?)");
        foreach($preurls as $preurl){
            if($rssurl<>$preurl){$ps->execute(array($id,$preurl));}
        }
        $message=$rssurl."の購読を止めました。".$pleaselogin;
    }else{
        $message=$rssurl."の購読を止めました。".$pleaselogin;
    }
}
if(isset($_POST['rssurl'])){
    $url=$_POST['rssurl'];
    $pattern='(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)';
    if(preg_match($pattern,$url,$match)){
        $test=get_headers($url);
        if(strpos($test[0],'OK')){
            $html=file_get_contents($url,false,null,0,30000);#30kB読む
            $patterns=array("/<link>(.*?)<\/link>/i","/<link(.*?)\/>/i","/<link(.*?)>/i");
            foreach($patterns as $pat){
                if(preg_match_all($pat,$html,$links)){
                    foreach($links[1] as $link){
                        if(stripos($link,"application/rss+xml")){
                            if(preg_match($pattern,$link,$match)){
                                $url=$match[0];break;
                        }
                    }
                }
            }
        }
        $html=file_get_contents($url,false,null,0,30000);#30kB読む
        if(stripos($html,"</item>")&&stripos($html,"</title>")&&stripos($html,"</link>")&&(stripos($html,"<dc:date>")||stripos($html,"<pubdate>"))){
            $rs=$db->query("SELECT url FROM t41rss WHERE id='$id'");
            $ps=$db->prepare("INSERT INTO t41rss(id,url) VALUES (?,?)");
            if($rs->fetch()===false){
                foreach($preurls as $preurl){
                    $rs1=$db->query("SELECT auto FROM t41rss WHERE id='$id' AND url='$preurl'");
                    if($rs1->fetch()===false){$ps->execute(array($id,$preurl));}
                }
            }
            $ps->execute(array($id,$url));
            if($ps->rowCount()){
                $message=$url."を追加しました。".$pleaselogin;
                $reload=true;
            }else{
                $error=$url."の追加に失敗しました。データベースエラー。";
            }
        }else{
            $error="このフィードには対応していません。";
        }
    }else{
        $error=$url."の読み込みに失敗しました。urlを確認してください。";
    }
}else{
    $error=$url."は有効なアドレスではありません。httpから始まる文字をブラウザのアドレス欄からコピペしてください。";
}
}
class xmltag{
    public $name;#--------------------------探したいタグ
    public $pos=0;#-------------------------読み込んだ位置を保持、次にreadするときは$pos以降から
    function read($xml,$item=0){
        $this->pos=($this->pos>$item)?$this->pos:$item;
        $i=mb_stripos($xml,"<$this->name",$this->pos);
        if($i===false){return "";}
        $i=$i+mb_strlen("<$this->name>");
        $j=mb_stripos($xml,"</$this->name>",$i);
        $this->pos=$j+mb_strlen("</$this->name>");
        $len=($j-$i>0)?$j-$i:0;
        $len=($len>100)?100:$len;#----------取り出す文字数は100文字以内にする
        return mb_substr($xml,$i,$len);
    }
}
//if(isset($_SESSION['rss'])){
//  $cachefile=$_SESSION['rss'];
//}else{
$rs=$db->query("SELECT url FROM t41rss WHERE id='$id'");
while ($r=$rs->fetch()){$urls[]=$r['url'];}
if(isset($urls)){
    $cachefile="rss$id";
    if(file_exists(__DIR__."/../sys/rss/$cachefile.html")==false){touch(__DIR__."/../sys/rss/$cachefile.html",time()-1800);}
}else{
    $urls=$preurls;
    $cachefile='rss';
}
// $_SESSION['rss']=$cachefile;
//}
if(filemtime(__DIR__."/../sys/rss/$cachefile.html")<strtotime("-$min minute")||$reload){
    $i=0;$ii=0;$html='<div id="rss"><h2>リンク集</h2>';
    $tit=new xmltag();$tit->name='title';
    $link=new xmltag();$link->name='link';
    $des=new xmltag();$des->name='description';
    $date=new xmltag();
    foreach ((array)$urls as $url) {
        $xml=file_get_contents($url,false,null,0,30000);#30kB読む
        $item=mb_stripos($xml,"<item>");
        $tit->pos=0;$link->pos=0;$des->pos=0;$date->pos=0;
        $date->name=(mb_stripos($xml,"</pubdate>"))?'pubdate':'dc:date';#RSS2.0と1.0切り分け
        $l=$link->read($xml);
        if(strlen($l)){
            $HP[$i]['herf']=$l;
            $HP[$i]['txt']=$tit->read($xml);
            $HP[$i]['title']=$des->read($xml);
            $HP[$i]{'rss'}=$url;
        }
        for($j=0;$j<$row;$j++){
            $l=$link->read($xml,$item);
            if(strlen($l)){
                $diary[$ii]['HP']=$i;
                $diary[$ii]['herf']=$l;
                $diary[$ii]['txt']=$tit->read($xml,$item);
                $diary[$ii]['title']=$des->read($xml,$item);
                $diary[$ii]['date']=strtotime($date->read($xml,$item));
                $ii++;
            }
        }
        $i++;
    }
    foreach((array)$diary as $key=>$value){$sort[$key]=$value['date'];}
    array_multisort($sort, SORT_DESC, $diary);
    for($i=0;$i<$top;$i++){
        $HPna=$HP[$diary[$i]['HP']]['txt'];
        $html=$html. '<li style="display:flex;"><a href="'.$diary[$i]['herf']. '" title="'.$diary[$i]['title'].'" target="_blank">'.$diary[$i]['txt'].'</a>'
        .date("（n月j日）",$diary[$i]['date']).'<a href="'.$HP[$diary[$i]['HP']]['herf'].'" title="'.$HP[$diary[$i]['HP']]['title']
        .'" style="font-style:oblique;" target="_blank">'.$HPna.'_ </a><form ACTION="diary.php?p='.$p.'#rss" METHOD="post">
        <INPUT TYPE="submit" VALUE="削除" onclick="return confirm(\'「'.$HPna.'」を止めますか。\')"><INPUT TYPE="hidden" NAME="rssdel" value="'
        .$HP[$diary[$i]['HP']]['rss'].'"></form></li>';
    }
    $etc=(substr($id,4)=='acct')?'リンク集に表示するブログを変更できます。':'リンク集に表示するブログを変更するにはログインしてください。さもないと1か月で元に戻ります。';
    $html=$html.'<form ACTION="diary.php?p='.$p.'#rss" METHOD="post">URL<INPUT TYPE="url" size="40" placeholder="http://example.co.jp" NAME="rssurl"
    required><INPUT TYPE="submit" VALUE="追加">'.$etc.'</form></div>';
    file_put_contents(__DIR__."/../sys/rss/$cachefile.html",$html);
}
include(__DIR__."/../sys/rss/$cachefile.html");
if(strlen($error)){echo '<div style="color:#ff0000">'.$error.'</div>';}
if(strlen($message)){echo '<div style="color:#0000ff">'.$message.'</div>';}