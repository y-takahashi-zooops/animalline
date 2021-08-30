$(function () {
    $("form[name=adoption-house]").submit(function (e) {
        e.preventDefault();
        $('#modal').modal('show');
    });
    $("#confirm-yes").click(function (e) {
        document.getElementsByName('adoption-house')[0].submit();
    });
});