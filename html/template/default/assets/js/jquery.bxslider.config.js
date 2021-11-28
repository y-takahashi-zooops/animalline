$(function(){

  var ua = navigator.userAgent;
  var touch_flag;
  var windowWidth = jQuery(window).width();
  if (ua.indexOf('iPhone') > 0 || ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0 || ua.indexOf('iPad') > 0 || ua.indexOf('Android') > 0) {
    touch_flag = true;
    config_min = 1;
    config_max = 1;
    config_margin = 4;
    // config_width = windowWidth;
    config_width = 0;
  } else {
    touch_flag = false;
    config_min = 1;
    config_max = 3;
    config_margin = 4;
    config_width = 214;
  }


  $(".edit-area.lineup .lineup-list").bxSlider({
    auto: false,
    mode: 'horizontal',
    pause: 1000,
    speed: 500,
    minSlides: config_min,
    maxSlides: config_max,
    slideWidth: config_width,
    slideMargin: config_margin,
    moveSlides: 1,
    prevText: '',
    nextText: '',
    pager: false,
    autoHover: true,
    touchEnabled: touch_flag,
    onSlideBefore: function () {
      $('.bx-controls-direction a').css('pointer-events', 'none');
      $('.bx-pager a').css('pointer-events', 'none');
    },
    onSlideAfter: function () {
      $('.bx-pager a').css('pointer-events', 'auto');
      $('.bx-controls-direction a').css('pointer-events', 'auto');
      slider.startAuto();
    }
    });

});