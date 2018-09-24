<?php
$message="";
$error = "";
$filena="";
$txt="";
$title="";
if (isset($_POST['commtxt'])) {
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES);
    $txt = htmlspecialchars($_POST['commtxt'], ENT_QUOTES);
    $url = htmlspecialchars($_POST['url'], ENT_QUOTES);
    $rid = htmlspecialchars($_POST['rid'], ENT_QUOTES);
    $typ = htmlspecialchars($_POST['typ'], ENT_QUOTES);//-1非公開0公開1会員のみ
    if(isset($rnk)){$typ=$typ==1?$rnk:$typ;}//会員公開は自分のランク以上に公開
    $ip=$_SERVER['REMOTE_ADDR'];
    $host=gethostbyaddr($ip);
    $now = date('Y-m-d H:i:s');
    if (isset($_FILES['myf'])) {
        $file=$_FILES['myf'];
        if ($file['size'] > 0) {
            if ($file['size'] > 1024*1024) {
                unlink($file['tmp_name']);
                $error='アップするファイルのサイズは１MB以下にしてください';
            } else {
                $filena=date('YmdHis').$file['name'];
                if (!(is_dir("./up/$p"))) {
                    mkdir("./up/$p");
                }
                move_uploaded_file($file['tmp_name'], ".sys/up/$p/$filena");
            }
        }
    }else{
        $filena=$_POST['imgFilena'];
    }
    if ($error==""&&isset($_SESSION['key'])&&isset($_POST['key'])&&$_SESSION['key']==$_POST['key']) {
        if($rid){
            $commid=$rid;
            $rid=$db->query("SELECT max(rid) as maxrid FROM t42comment WHERE pid=$p AND id=$commid;")->fetchcolumn() +10;
        }else{
            $commid=$db->query("SELECT max(id) AS maxid FROM t42comment WHERE pid=$p;")->fetchcolumn() + 1;
        }
        $ps=$db->prepare("INSERT INTO t42comment(mid,upd,fix,title,txt,url,pid,id,rid,typ,ip,host,filena) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $ps->execute(array($id,$now,$now,$title,$txt,$url,$p,$commid,$rid,$typ,$ip,$host,$filena));
        if ($ps->rowCount()) {
            $title="";$txt="";
            unset($_SESSION['key']);
            if (isset($name)) {
                $message='コメントありがとうございます！';
                //mb_send_mail($mymail, $na.'さんから<a href="https://sharecarsblog.shop/diary.php?p='.$p.'#comment">'.$pna.'</a>にコメント', $title.'<br>'.$txt);
            }else{
                $message=$typ==-1?'コメントを受け取りました。':'コメントを仮保存しました。公開するにはログインしてください。';
            }
        } else {
            $error="データーベースエラー";
        }
    } else {
        $message='';
    }
}
$rs=$db->query("SELECT t42comment.id as id,rid,typ,mid,na,title,url,txt,filena,upd FROM t42comment LEFT JOIN t14member ON t42comment.mid=t14member.id WHERE pid=$p AND (t14member.id is not null OR t42comment.mid='$id') ORDER BY id,rid;");
echo '<div id="comment"><h2>お問い合わせ</h2>';
$i=0;
$typ=isset($rnk)?$rnk:0;
while ($r=$rs->fetch()) {
    if($r['typ']==0||$r['typ']>0&&$r['typ']<=$typ){
        if($r['id']>1&&$r['rid']){
            echo"<div>------------------------------------------------------</div>";
        }else if($r['id']>1){
            echo"<div>----------------------------------------------------------------------</div>";
        }
        $n=(strlen($r['url'])&&strlen($r['na']))?"<a href='".$r['url']."'>".$r['na']."</a>":$r['na'];
        $img=(substr($r['filena'],-3)=='jpg')?"<a href='sys/up/$p/".$r['filena']."' target='_blank'><img src='sys/up/$p/s-".$r['filena']."'></a>":"<a href='sys/up/$p/".$r['filena']."'>".substr($r['filena'],14)."</a>";
        $filena=(isset($r['filena']))?',"'.$r['filena'].'"':"";
        $del=($r['mid']==$id&&substr($id,0,4)!="acct")?"<button onclick='commDel($i,$p,".'"'.$r['upd'].'"'.$filena.")'>削除</button>":"";
        $re=$r['rid']?"":"<button onclick='re(".$r['id'].",".'"'.$r['na'].'"'.",".'"'.$r['title'].'"'.")'>返信</button>";
        echo"<div id='comm$i'><div class='comm'><div>題:".$r['title']."</div>　<div>$n</div>　<div>".date('Y年n月j日 H:i', strtotime($r['upd']))."記</div>$del $re</div>";
        echo'<div>'.$r['txt']."</div>$img</div>";
        $i++;
    }
}
echo "</div>";
$key = md5(microtime() . mt_rand());
$_SESSION['key'] = $key;            //連投防止
?>
  <FORM id="commform" ACTION="diary.php?p=<?php print $p; ?>#comment" METHOD="post" ENCTYPE="multipart/form-data">
    <div id="commctl">
      <div class="comminp">
        <div>題
          <INPUT id="commtitle" TYPE="text" NAME="title" size="40" VALUE="<?php print $title; ?>">
        </div>
        <select name="typ" id="typ">
          <option value="0" selected>全ての人に公開する</option>
          <option value="-1">非公開、管理者のみ</option>
          <option value="1">部分公開、会員のみ</option>
        </select>
      </div>
      <div class="comminp">
        <div>　添付
          <INPUT ID="myf" NAME="myf" TYPE="file" onchange='inputFile(this)'>
        </div>
        <div>あなたのHP
          <INPUT TYPE="url" NAME="url" size="30" placeholder="http://example.co.jp">
        </div>
      </div>
      <div class="comminp">
        <div><img id='s-img'></div>
        <div><img id='img' style='display:none;'></div>
      </div>
    </div>
    <TEXTAREA NAME="commtxt" ROWS="10" minlength="5" maxlength=1000 id="commtxt" required>
    </TEXTAREA>
    <input type="hidden" name="key" value="<?php echo $key; ?>" />
    <input type='hidden' id='rid' name='rid' value='0' />
    <input type="hidden" id="imgFilena" name="imgFilena" />
    <button id='send' type='button' onclick='upload(<?php echo $p; ?>)'>送信</button>
  </FORM>
  <div id='error'>
    <?php echo $error; ?>
      </font>
  </div>
  <div id='message'>
    <?php echo $message; ?>
      </font>
  </div>
  <canvas style="display:none"></canvas>
  <script type="text/javascript" src="js/comment.js"></script>
