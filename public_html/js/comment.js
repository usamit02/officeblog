var captureForm = document.querySelector('#inputFile');
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');
var displayimgid = "img0";

function inputFile(obj) {
  var file = obj.files[0];
  var image = new Image();
  var reader = new FileReader();
  if (file.type.match(/image.*/)) {
    reader.onloadend = function () {
      image.onload = function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        var h = 112;
        var w = image.width * (h / image.height);
        canvas.width = w;
        canvas.height = h;
        ctx.drawImage(image, 0, 0, w, h);
        $('#s-img').attr('src', canvas.toDataURL("image/jpeg", 0.5)); //圧縮
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        var h = 480;
        var w = image.width * (h / image.height);
        canvas.width = w;
        canvas.height = h;
        ctx.drawImage(image, 0, 0, w, h);
        $('#img').attr('src', canvas.toDataURL("image/jpeg", 1.0));
      }
      image.src = reader.result;
    }
    reader.readAsDataURL(file);
  }
}

function upload(page) {
  var txtlen = $("#commtxt").val().trim().length;
  if (txtlen == 0) {
    $('#error').html('コメントを入力してください。');
  } else if (txtlen > 500) {
    $('#error').html('コメントは500文字以内にしてください。あと' + (txtlen - 500) + '文字減らしてくささい。');
  } else {
    if ($("#img").attr('src')) {
      $("#myf").remove();
      $("#message").html("アップロード中です・・・");
      document.body.style.cursor = 'wait';
      var base64ToBlob = function (base64) { // 引数のBase64の文字列をBlob形式にしている
        var base64Data = base64.split(',')[1], // Data URLからBase64のデータ部分のみを取得
          data = window.atob(base64Data), // base64形式の文字列をデコード
          buff = new ArrayBuffer(data.length),
          arr = new Uint8Array(buff),
          blob, i, dataLen;
        for (i = 0, dataLen = data.length; i < dataLen; i++) { // blobの生成
          arr[i] = data.charCodeAt(i);
        }
        blob = new Blob([arr], {
          type: 'image/jpeg'
        });
        return blob;
      }
      var formData = new FormData();
      var blob;
      var todate = new Date();
      var imgFilena = String(todate.getFullYear()) + (todate.getMonth() + 1) + todate.getDay() + todate.getHours() + todate.getMinutes() + todate.getSeconds() + ".jpg";
      formData.append("page", page);
      formData.append("imgFilena", imgFilena);
      blob = base64ToBlob($('#s-img').attr('src'));
      formData.append('s-img', blob);
      blob = base64ToBlob($('#img').attr('src'));
      formData.append('img', blob);
      $("#imgFilena").val(imgFilena);
      $.ajax({
        type: 'POST',
        url: 'ajax/comment.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json, dataType) {
          txt = (json.msg == 'ok') ? "<div>写真のアップロードに成功しました。</div>" : "<div>" + json.msg + "</div>";
          $("#message").html(txt);
          document.body.style.cursor = 'auto';
          if (json.msg == 'ok') {
            $('#commform').submit();
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          $("#message").html("<div>写真のアップロードに失敗しました。</div>");
          document.body.style.cursor = 'auto';
        }
      });
    } else {
      $('#commform').submit();
    }
  }
}
function re(id, na, title) {
  $("#rid").val(id);
  $("#send").text(na + "さんへ返信");
  if ($("#commtitle").val().trim().length == 0) {
    $("#commtitle").val("Re." + title);
  }
}
function commDel(i, page, upd, filena) {
  $.ajax({
    type: 'POST',
    url: 'ajax/comment.php',
    data: {
      'page': page,
      'upd': upd,
      'filena': filena
    },
    success: function (json) {
      if (json.msg == 'ok') {
        $('#comm' + i + ' + div').remove();
        $('#comm' + i).remove();
      } else {
        alert(json.msg);
      }
    },
    error: function () {
      alert("コメント削除のためのAJAX通信に失敗しました。");
    }
  });
}