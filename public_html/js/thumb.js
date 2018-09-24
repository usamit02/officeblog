$(function () {
  $(".thumblist li a").click(function () {
    var url = $(this).attr("href");
    var img = new Image();
    var $img = $(this).parents(".row1").find('.thumbimg img');
    $img.attr({ 'src': "img/load.gif" });
    $(img).on("load", function () {
      $img.attr({ 'src': url });
    });
    img.src = url;
    return false;
  });
});
