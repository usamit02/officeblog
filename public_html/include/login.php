<?php
if(isset($_POST['logout'])&&session_status()===PHP_SESSION_ACTIVE){
    $_SESSION = array();unset($na);unset($mail);$rnk=0;
}
if(isset($_SESSION['na'])&&isset($_POST['logout'])===false){
    echo '<div style="background-color:blue;color:white;">こんにちは<br>'.$_SESSION['na'].'さん</div>';
    ?>
  <FORM ACTION="<?php basename(__FILE__) ?>" METHOD="post" ENCTYPE="multipart/form-data">
    <INPUT TYPE="submit" NAME="logout" VALUE="ログアウト">
  </FORM>
  <?php
}else{
    $_SESSION['state']=md5(microtime().mt_rand());
    $callback=parse_url($_SERVER['REQUEST_URI']);
    $_SESSION['callback']=$callback['path'];
    require_once(__DIR__."/../pay/payinit.php");
    ?>
    <script type="text/javascript" language="javascript">
      <!--
      function login(state) {
        ck = document.loginform.autologin.checked;
        if (ck == true) {
          document.loginform.state.value = "auto" + state;
        } else {
          document.loginform.state.value = state;
        }
        document.loginform.submit();
      }
      -->
    </script>
    <FORM name="loginform" ACTION="https://id.pay.jp/.oauth2/authorize" METHOD="GET">
      <a href="JavaScript:login('<?php echo $_SESSION['state']?>');"><img src="img/payjp.jpg" alt="PAY IDでログイン"></a>
      <div>
        <INPUT TYPE="checkbox" NAME="autologin" value="true">ログインしたままにする</div>
      <input type="hidden" name="client_id" value="<?php echo $clientID?>" />
      <input type="hidden" name="scope" value="accounts cards addresses" />
      <input type="hidden" name="response_type" value="code" />
      <input type="hidden" name="state" value="<?php echo $_SESSION['state']?>" />
    </FORM>
    <?php
}
?>