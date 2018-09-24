$(function () {
  // 親メニュー処理
  $('span').click(function () {
    // メニュー表示/非表示
    $(this).next('ul').slideToggle('fast');

  });

  // 子メニュー処理
  $('li').click(function (e) {
    // メニュー表示/非表示
    $(this).children('ul').slideToggle('fast');
    var ex = $(this).children('span').text();
    if (ex == "▼") {
      ex = ex.replace("▼", "▲");
      $(this).css({ "flex": "10" });
    } else {
      ex = ex.replace("▲", "▼");
      $(this).css({ "flex": "1 1 50px" });
    }
    $(this).children('span').text(ex);
    e.stopPropagation();
  });

  //ツリービュー方式に変更
  $(window).on('load resize', function () {
    if (window.matchMedia('screen and (min-width:1000px) and (max-width:1399px)').matches) {
      $(".navi").unwrap();
    }
  });

  $('#menu').click(function () {
    $('nav').toggleClass('open');
    $(this).toggleClass('active');
  }
  );

});