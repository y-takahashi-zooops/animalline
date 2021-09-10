$(function () {
  if ($('#is-scroll').length) scroll();
});

function scroll(target = '#chat-area') {
  location.href = "#"; // required to work around a bug in WebKit (Chrome / Safari)
  location.href = target;
}