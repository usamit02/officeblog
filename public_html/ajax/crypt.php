<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>

<body>
  <?php
if(isset($_POST['na'])&&isset($_POST['pw'])){
    echo crypt($_POST['pw'],substr($_POST['na'],2));
}
?>
</body>

</html>