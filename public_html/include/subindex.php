<?php
$rs=$db->query("SELECT COUNT(id) AS countid FROM t11page WHERE parent=$p");
$count=$rs->fetchcolumn();
if($count){
    $rs=$db->query("SELECT id,imgpath,title,h1,upd,txt100 FROM q11subindex WHERE parent=$p ORDER BY upd DESC");
    echo '<div class="subindexwarp">';
    while($r=$rs->fetch()){
        $str='<div class="subindex"><a href="./diary.php?p='.$r['id'].'"><img src="./img/'.$r['imgpath'].'"></a>';
        echo $str.'<div class="subtxt">'.$r['title'].'<br>'.$r['h1'].'<br>'.date('Y年n月j日', strtotime($r['upd'])).'　記</div></div>';
    }
    echo '</div>';
}
?>