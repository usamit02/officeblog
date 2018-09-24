<?php
session_start();
if(!isset($_GET['code'])){ header( "Location: ../diary.php" ) ;}

// アプリケーション設定
define('Client_ID', '20c0fd5202c8725f389fc7bf36475a4d0eff4489');
define('Client_Secret', 'a4f357ea2998f42a84f985f0a824a810d005c9c1086ac6bf5ece8ad');
//define('Client_ID', '62b97976af129b1e99f9fa4d26d6cad2edf46bc5');
//define('Client_Secret', '8bbba729b1961cae1dcd19b91be2ef55d9553949b828094c7c8b2e3');
// Endpoint
define('Token_URL', 'https://api.pay.jp/u/.oauth2/token');
define('API_URL', 'https://api.pay.jp/u/v1/');

$params = array(
'grant_type' => 'authorization_code',
'code' => $_GET['code'],
'client_id' => Client_ID,
);
$headers = array(
'Content-Type: application/x-www-form-urlencoded',
'Authorization: Basic '.base64_encode(Client_ID.':'.Client_Secret),
);
$options = array('http' => array(
'method' => 'POST',
'header' => implode("\r\n", $headers),
'content' => http_build_query($params)
));
$res = file_get_contents(Token_URL, false, stream_context_create($options));
$token = json_decode($res, true);
if(isset($token['error'])){
    echo 'エラー発生';
    exit;
}
$access_token = $token['access_token'];
$params = array(
'access_token' => $access_token
);
$res = file_get_contents(API_URL.'accounts?'.http_build_query($params));
$account = json_decode($res, true);
$res = file_get_contents(API_URL.'cards?'.http_build_query($params));
$cards = json_decode($res, true);
$res = file_get_contents(API_URL.'addresses?'.http_build_query($params));
$address = json_decode($res, true);
$headers = array(
'Content-Type: application/x-www-form-urlencoded',
'Authorization: Bearer '.$access_token
);
$options = array('http' => array(
'method' => 'POST',
'header' => implode("\r\n", $headers)
));
$res = file_get_contents(API_URL.'cards/default/tokenize', false, stream_context_create($options));
$token = json_decode($res, true);
?>

  <!DOCTYPE html>
  <html>

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>payidのOAuth2.0を使ってプロフィールを取得</title>
  </head>

  <body>
    <h2>ユーザー情報</h2>
    <table>
      <tr>
        <td>ID</td>
        <td>
          <?php echo $account['id']; ?>
        </td>
      </tr>
      <tr>
        <td>ユーザー名</td>
        <td>
          <?php echo $account['email']; ?>
        </td>
      </tr>
      <tr>
        <td>苗字</td>
        <td>
          <?php echo $account['first_name']; ?>
        </td>
      </tr>
      <tr>
        <td>名前</td>
        <td>
          <?php echo $account['last_name']; ?>
        </td>
      </tr>
      <tr>
        <td>都道府県</td>
        <td>
          <?php echo $address['address_state']; ?>
        </td>
      </tr>
      <tr>
        <td>トークン</td>
        <td>
          <?php echo $token['id']; ?>
        </td>
      </tr>

    </table>

  </body>

  </html>