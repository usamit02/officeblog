var prefs = new Array("北海道", "青森県", "岩手県", "秋田県", "山形県", "宮城県", "福島県", "茨城県", "栃木県", "群馬県", "埼玉県", "千葉県", "東京都", "神奈川県", "山梨県", "新潟県", "長野県", "富山県", "石川県", "福井県", "静岡県", "愛知県", "岐阜県", "三重県", "滋賀県", "京都府", "大阪府", "兵庫県", "奈良県", "和歌山県", "鳥取県", "岡山県", "島根県", "広島県", "山口県", "香川県", "徳島県", "愛媛県", "高知県", "福岡県", "佐賀県", "大分県", "熊本県", "長崎県", "宮崎県", "鹿児島県", "沖縄県");
$(document).ready(function () {
  culcPostage();
});
$('#pref').change(function () {
  culcPostage();
});
function culcPostage() {
  var pref = prefs.indexOf($("#pref").val());
  var ret = true;
  if (pref >= 0) {
    $.ajax({
      url: 'ajax/postage.php',
      type: 'POST',
      async: false,
      cache: false,
      dataType: 'json',
      data: {
        'pref': pref + 1,
        'size': size,
        'g': g,
        'pack': pack
      },
      timeout: 1000,
      error: function () {
        alert('ajax通信に失敗しました。');
      },
      success: function (json) {
        if (json.length == 0) {
          alert("json取得に失敗しました。");
        } else {
          if (!isNaN(json)) {
            $(".postagePay").val(json);
            $("#postage").text(json + "円");
            var total = parseInt($("#subtotal").text().replace('円', '')) + parseInt(json);
            $("#total").text(total.toString() + "円");
            ret = false;
          } else {
            alert(json);
          }
        }
      }
    })
  }
  return ret;
}

function beforePay() {
  var err = false;
  $("#address input").each(function () {
    $(this).css('border', '');
    if ($(this).val().length == 0) {
      senderr(this, "は必須入力です。");
      err = true;
    } else if ($(this).val().length > $(this).attr("maxlength")) {
      senderr(this, "は" + $(this).attr("maxlength") + "文字以下です。あと" + ($(this).val().length - $(this).attr("maxlength")) + "文字減らしてください。");
      err = true;
    } else if ($(this).val().length < $(this).attr("minlength")) {
      senderr(this, "は" + $(this).attr("minlength") + "文字以上です。あと" + ($(this).attr("minlength") - $(this).val().length) + "文字以上入力してください。");
      err = true;
    }
  })
  if (!err && prefs.indexOf($("#pref").val()) == -1) {
    senderr($("#pref"), "は北海道、東京都、◎▼府、〇×県などと入力してください。");
    err = true;
  } else if (!err) {
    err = culcPostage();
  }
  if (!err && !$("#post").val().match(/^\d{3}-\d{4}$/)) {
    senderr($("#post"), "は半角数字3桁-（ハイフン）半角数字4桁で正しく入力してください。");
    err = true;
  }
  if (!err && !$("#tel").val().match(/^\d{2,5}-\d{1,4}-\d{4}$/)) {
    senderr($("#tel"), "を半角数字と-（ハイフン）区切りで正しく入力してください。");
    err = true;
  }
  if (err) {
    return false;
  }
  var ret = false;
  $.ajax({
    url: 'ajax/address.php',
    type: 'POST',
    async: false,
    cache: false,
    dataType: 'json',
    data: {
      'post': $("#post").val(),
      'pref': $("#pref").val(),
      'addr': $("#addr").val(),
      'na': $("#na").val(),
      'tel': $("#tel").val()
    },
    timeout: 1000,
    error: function () {
      alert('ajax通信に失敗しました。');
    },
    success: function (json) {
      if (json.length == 0) {
        alert("json取得に失敗しました。");
      } else {
        if (json != "ok") {
          alert(json);
        } else {
          ret = true;
        }
      }
    }
  })
  return ret;
}

function senderr(e, message) {
  $(e).focus();
  $(e).css('border', '2px solid red');
  alert($(e).prev("label").text() + message);
}
function guestPay() {
  if (beforePay()) {
    $("#payjp_checkout_box input[type='button']").click();
  }
}