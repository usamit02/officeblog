<?php
$urls = array(
"http://inakalib.com/feed",
"http://dodon-jp.blogspot.com/feeds/posts/default?alt=rss",
"http://henan.blog.fc2.com/?xml",
"http://i401gou.blog.fc2.com/?xml",
"http://ibaya.hatenablog.com/rss",
"http://owl888.blog.fc2.com/?xml",
"http://tinyhouse-story.com/feed",
"http://senninkyou.blog.fc2.com/?xml",
"http://mugigin4841.blog.fc2.com/?xml",
"http://ron2012simplelife.blog.fc2.com/?xml"
);
$row=3;#------------------------------------１ブログあたり取得記事数
$top=15;#-----------------------------------表示する最新記事数
$min=60;#-----------------------------------更新間隔（分）

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

if(filemtime('include/rsssort.html')<strtotime("-$min minute")){
    $i=0;$ii=0;$html="<h2>リンク集</h2>";
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
        $html=$html. '<li><a href="'.$diary[$i]['herf']. '" title="'.$diary[$i]['title']
        .'">'.$diary[$i]['txt'].'</a>'.date("（n月j日）", $diary[$i]['date']).'<a href="'
        .$HP[$diary[$i]['HP']]['herf'].'" title="'.$HP[$diary[$i]['HP']]['title']
        .'" style="font-style:oblique;">'.$HP[$diary[$i]['HP']]['txt'].'</a></li>';
    }
    file_put_contents('include/rsssort.html',$html);
}
include('include/rsssort.html');