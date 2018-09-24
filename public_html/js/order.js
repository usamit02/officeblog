function beforeCancel() {
  if ($("#reason").css("display") == "none") {
    $("#reason").css("display", "block");
  } else {
    $("#reason").css('border', '');
    if ($("#reason").val().length > $("#reason").attr("maxlength")) {
      alert("キャンセル理由は" + $("#reason").attr("maxlength") + "文字以下です。あと" + ($("#reason").val().length - $("#reason").attr("maxlength")) + "文字減らしてください。");
      $("#reason").css('border', '2px solid red');
    } else if ($("#reason").val().length < $("#reason").attr("minlength")) {
      alert("キャンセル理由は" + $("#reason").attr("minlength") + "文字以上です。あと" + ($("#reason").attr("minlength") - $("#reason").val().length) + "文字以上入力してください。");
      $("#reason").css('border', '2px solid red');
    } else {
      return true;
    }
  }
  return false;
}

function itemOpen(payDay, row) {
  var a = $("#items" + row).html();
  if ($("#items" + row).html() == "") {
    $.ajax({
      url: 'ajax/order.php?pay_day=' + payDay,
      type: 'GET',
      dataType: 'text',
      error: function () {
        $("#items" + row).append('ajax通信に失敗しました。');
      },
      success: function (html) {
        $("#items" + row).append(html);
        $(this).css("display", "none");
        $(this).next().css("display", "inline");
      }
    })
  } else {
    $("#items" + row).empty();
  }
}