jQuery(function($){

/*-----------------------------
　ページ内リンク
-----------------------------*/
  var w = $(window).width();
  if(w <= 480){
  var headerHight = 0;
  } else {
  var headerHight = 80;
  }
  
  $('a[href^="#"]').click(function(e){
  var anchor = $(this),
  href = anchor.attr('href'),
  pagename = window.location.href;
  // 現在のurlのハッシュ以降を削除
  pagename = pagename.replace(/#.*/,'');
  // リンク先のurlから現在の表示中のurlを削除
  href = href.replace( pagename , '' );
  if( href.search(/^#/) >= 0 ){
  // 整形したリンクがページ内リンクの場合はページ無いスクロールの対象とする
  // 通常の遷移処理をキャンセル
  e.preventDefault();
  var speed = 500;
  // 前段階で整形したhrefを使用する
  // var href= $(this).attr("href");
  var target = $(href == "#" || href == "" ? 'html' : href);
  var position = target.offset().top-headerHight;
  $("html, body").animate({scrollTop:position}, speed, "swing");
  // ロケーションバーの内容を書き換え
  location.hash = href ;
  return false;
  }
  });

 $('.sm-close-menu').click(function(e){
  $('.btn-trigger').trigger('click');
 });
/*-----------------------------
　スクロール
-----------------------------*/

var header = $('header'),
nav = $('nav'),
offset = nav.offset();
	
$(window).on("scroll", function() {

	if ($(this).scrollTop() > 100) {
		$('.pagetop').addClass('fixed');
	} else {
		$('.pagetop').removeClass('fixed');
	}
	
	/*if($(window).scrollTop() > offset.top) {
		//header.addClass('fixed');
		nav.addClass('fixed');
	} else {
		//header.addClass('fixed');
		nav.removeClass('fixed');
	}*/
      
  // フッター固定する
  var scrollHeight = $(document).height(); 
  // ドキュメントの高さ
  var scrollPosition = $(window).height() + $(window).scrollTop(); 
  //　ウィンドウの高さ+スクロールした高さ→　現在のトップからの位置
  var footHeight = $("footer").innerHeight();
  // フッターの高さ
      
  if ( scrollHeight - scrollPosition  <= footHeight ) {
  // 現在の下から位置が、フッターの高さの位置にはいったら
  //  ".gotop"のpositionをabsoluteに変更し、フッターの高さの位置にする		
    $(".pagetop").css({
      "position":"absolute",
      "bottom": -40
    });
  } else {
  // それ以外の場合は元のcssスタイルを指定
    $(".pagetop").css({
      "position":"fixed",
      "bottom": 20
    });
  }
});

$('#side-dog-type-list-toggle').on('click', function() {
  $('#side-dog-type-list').show();
});
$('#side-dog-list-toggle').on('click', function() {
  $('#side-dog-list').show();
});

$('#side-cat-type-list-toggle').on('click', function() {
  $('#side-cat-type-list').show();
});
$('#side-cat-list-toggle').on('click', function() {
  $('#side-cat-list').show();
});

/*-----------------------------
　スマホメニュー
-----------------------------*/
  
$('.btn-trigger').on('click', function() {
    $(this).toggleClass('active');
    $("#side-menu").stop().slideToggle(400);
    return false;
});
  
var w = $(window).width();
$(window).on('load resize', function(){
w = $(window).width();
/*if(w <= 980){
  $('#side-menu ul li a').on('click', function() {
		$("#side-menu").stop().slideUp(400);
    $('.btn-trigger').removeClass('active');
    return false;
});
} else if(w > 980) {
    $('.btn-trigger').removeClass('active');
    $("#side-menu").css({"display":"","height":""});
}*/
});

/*-----------------------------
　スマホ電話リンク
-----------------------------*/
  
if(!navigator.userAgent.match(/(iPhone|iPad|Android)/)){
	$("a.tel-link").each(function(){
		$(this).replaceWith("<span>" + $(this).html() + "</span>");
	});
}

/*-----------------------------
　高さ調整
-----------------------------*/
$('.mh').matchHeight();
  
});