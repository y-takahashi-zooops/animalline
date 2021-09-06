$(function () {
  $('#pet_type').on('change', function () {
    location.href = `?pet_type=${this.value}`;
  });
});