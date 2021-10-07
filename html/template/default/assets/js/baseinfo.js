$("form[name=update-baseinfo-form]").submit(function (e) {
    e.preventDefault();
    $('#modal').modal('show');
});
$("#confirm-yes").click(function (e) {
    document.getElementsByName('update-baseinfo-form')[0].submit();
});

var breederImgFlg = false;
$("body").on("change", "#breeders_thumbnail_path", function (e) {
    breederImgFlg = true;
});
$("body").on("change", "#breeders_license_thumbnail_path", function (e) {
    breederImgFlg = false;
});
$("body").on("change", ".thumbnail_path", function (e) {
    readURL(this)
});

function readURL(input) {
    if (input.files && input.files[0] && input.files[0].size < 5000000) {
        let reader = new FileReader();
        reader.readAsDataURL(input.files[0]);
        reader.onloadend = function () {
            var base64data = reader.result;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/breeder/configration/pets/upload",
                data: {image: base64data},
                success: function (data) {
                    if (breederImgFlg === true) {
                        $('#img_thumbnail').attr('src', '/' + data).removeClass('hidden')
                        $('#breeder_src_path').val('/' + data);
                    } else {
                        $('#img_license_thumbnail').attr('src', '/' + data).removeClass('hidden')
                        $('#breeder_license_src_path').val('/' + data);
                    }
                }
            });
        };
    } else {
        alert('File too large!')
    }
}
