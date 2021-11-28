
$(document).ready(function() {

  objectFitImages('.object-fit');

  /* スクロールスムース */
  $('a[href^="#"]').click(function() {
    var speed = 500;
    var href = $(this).attr("href");
    var target = $(href == "#" || href == "" ? "html" : href);
    var position = target.offset().top;
    $("body,html").animate({ scrollTop: position }, speed, "swing");
    return false;
  });

 //変数セット
 var $elem = $('.changeImg');
 var sp = '_sp.';
 var pc = '_pc.';
 var replaceWidth = 768;
 function imageSwitch() {
   var windowWidth = parseInt($(window).width());
   $elem.each(function () {
     var $this = $(this);
     if (windowWidth >= replaceWidth) {
       $this.attr('src', $this.attr('src').replace(sp, pc));
     } else {
       $this.attr('src', $this.attr('src').replace(pc, sp));
     }
   });
 }
 imageSwitch();
 var delayStart;
 var delayTime = 200; //ミリSec
 $(window).on('resize', function () {
   clearTimeout(delayStart);
   delayStart = setTimeout(function () {
     imageSwitch();
   }, delayTime);
 });
});


/* ------------------------------------------- */
/* メニューボタン用function */
/* ------------------------------------------- */
function menu(){
  $(function(){
    if($("html").hasClass("menu-active")){
      $("html").removeClass("menu-active");
      // $("#header .menu-btn").removeClass("on");
      // $("#header .nav").css("height",winh+"px");
    }else{
      $("html").addClass("menu-active");
      // $("#header .menu-btn").addClass("on");
      // var winh = $(window).outerHeight(); //ウィンドウの高さ
      // $("#header .nav").css("height",winh+"px");
    }
  });
}

$(window).on("load scroll resize", function(){
  var scroll = $(window).scrollTop();
  if (scroll > 300){
    $("#footer .pageup").addClass("on");
  }else if (scroll < 300){
    $("#footer .pageup").removeClass("on");
  }
});
