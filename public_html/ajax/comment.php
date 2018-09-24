<?php
if (isset($_POST['page'])&&isset($_POST['imgFilena'])) {
    $page = htmlspecialchars($_POST['page'], ENT_QUOTES);
    $imgFilena = htmlspecialchars($_POST['imgFilena'], ENT_QUOTES);
    if (is_uploaded_file($_FILES["img"]["tmp_name"])) {
        if (!(is_dir(__DIR__."/../sys/up/$page"))) {
            mkdir(__DIR__."/../sys/up/$page");
        }
        if (move_uploaded_file($_FILES["img"]["tmp_name"], __DIR__."/../sys/up/$page/$imgFilena")) {
            if (is_uploaded_file($_FILES["s-img"]["tmp_name"])) {
                if (move_uploaded_file($_FILES["s-img"]["tmp_name"], __DIR__."/../sys/up/$page/s-$imgFilena")) {
                    $json['msg']="ok";
                }else{
                    $json['msg']="縮小サイズイメージファイルの書き込みに失敗しました。";
                }
            }else{
                $json['msg']="縮小サイズイメージファイルのアップロードに失敗しました。";
            }
        } else {
            $json['msg'] = "イメージファイルの書き込みに失敗しました。";
        }
        
    } else {
        $json['msg']= "イメージファイルのアップロードに失敗しました。";
    }
}else if(isset($_POST['page'])&&isset($_POST['upd'])){
    $page = htmlspecialchars($_POST['page'], ENT_QUOTES);
    require_once __DIR__."/../sys/dbinit.php";
    if($db->exec("DELETE FROM t42comment WHERE pid=$page AND fix='".$_POST['upd']."';")==1){
        if(isset($_POST['filena'])&&strlen($_POST['filena'])){
            if(file_exists(__DIR__."/../sys/up/$page/".$_POST['filena'])){
                unlink(__DIR__."/../sys/up/$page/".$_POST['filena']);
            }
            if(substr($_POST['filena'],-3)=='jpg'){
                if(file_exists(__DIR__."/../sys/up/$page/s-".$_POST['filena'])){
                    unlink(__DIR__."/../sys/up/$page/s-".$_POST['filena']);
                }
            }
        }
        $json['msg']="ok";
    }else{
        $json['msg']="コメントの削除に失敗しました。";
    }
} else {
    $json['msg']= "ページが選択されていません";
}
header('Content-type: application/json');
echo json_encode($json);
?>