$(function () {
    $("form[name=breeder-house]").submit(function (e) {
        e.preventDefault();
        $('#modal').modal('show');
    });
    $("#confirm-yes").click(function (e) {
        document.getElementsByName('breeder-house')[0].submit();
    });
});