$(function () {
    $("form[name=breeder-house]").submit(function (e) {
        e.preventDefault();
        $('#modal').modal('show');
    });
});