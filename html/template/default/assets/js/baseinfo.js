$("form[name=update-baseinfo-form]").submit(function (e) {
    e.preventDefault();
    $('#modal').modal('show');
});
$("#confirm-yes").click(function (e) {
    document.getElementsByName('update-baseinfo-form')[0].submit();
});

var bs_modal = $('#modal-crop');
var image = document.getElementById('image');
var cropper, reader, file, size;
var breederImgFlg = false;

var breederImg = document.getElementById('img_thumbnail');
var breederImgSrc = breederImg.getAttribute('src');
if (breederImgSrc) {
    $('#breeder_img_error').hide();
}

var breederLicenseImg = document.getElementById('img_license_thumbnail');
var breederLicenseImgSrc = breederLicenseImg.getAttribute('src');
if (breederLicenseImgSrc) {
    $('#breeder_license_img_error').hide();
}


$("body").on("change", "#breeders_thumbnail_path", function (e) {
    breederImgFlg = true;
    $('#breeder_img_error').hide();
});
$("body").on("change", "#breeders_license_thumbnail_path", function (e) {
    breederImgFlg = false;
    $('#breeder_license_img_error').hide();
});

$("body").on("change", ".license_thumbnail_path", function (e) {
    inputImageId = e.target.id;
    var files = e.target.files;

    var done = function (url) {
        var files = e.target.files;

        var fileReader = new FileReader();

        fileReader.onload = function(event) {
            $.ajax({
                url:'/ajax/imageconvert',
                type:'POST',
                dataType: 'json',
                data: {
                    'filename': file.name,
                    'filedata': event.target.result,
                },
            })
            .done( (data) => {
                bs_modal.modal('hide');
                $('#img_license_thumbnail').attr('src',data.url).removeClass('hidden')
                $('#breeder_license_src_path').val(data.url);
            })
            .fail( (jqXHR, textStatus, errorThrown) => {
                alert('ファイルのアップロードに失敗しました。');
            });
        }

        fileReader.readAsDataURL(file);
    }

    if (files && files.length > 0) {
        file = files[0];
        done(URL.createObjectURL(file));
    }
});

$("body").on("change", ".thumbnail_path", function (e) {
    inputImageId = e.target.id;
    var files = e.target.files;

    var done = function (url) {
        var files = e.target.files;

        var fileReader = new FileReader();

        fileReader.onload = function(event) {
            $.ajax({
                url:'/ajax/imageconvert',
                type:'POST',
                dataType: 'json',
                data: {
                    'filename': file.name,
                    'filedata': event.target.result,
                },
            })
            .done( (data) => {
                bs_modal.modal('show');
                image.src = data.url;
            })
            .fail( (jqXHR, textStatus, errorThrown) => {
                alert('ファイルのアップロードに失敗しました。');
            });
        }

        fileReader.readAsDataURL(file);
    }

    if (files && files.length > 0) {
        file = files[0];

        if (URL) {
            done(URL.createObjectURL(file));
        } else if (FileReader) {
            reader = new FileReader();
            reader.onload = function (e) {
                done(reader.result);
            };
            reader.readAsDataURL(file);
        }
    }
});

bs_modal.on('shown.bs.modal', function () {
    cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 2,
        preview: '.preview',
        dragMode: 'none',
    });
}).on('hidden.bs.modal', function () {
    cropper.destroy();
    cropper = null;
    $("#breeders_thumbnail_path").val('');
});

$("#crop").click(function () {
    size = cropper.imageData.naturalHeight < cropper.imageData.naturalWidth ? cropper.imageData.naturalHeight : cropper.imageData.naturalWidth;
    size = size < 1000 ? size : 1000;
    canvas = cropper.getCroppedCanvas({
        width: size,
        height: size,
    });

    canvas.toBlob(function (blob) {
        url = URL.createObjectURL(blob);
        var reader = new FileReader();
        reader.readAsDataURL(blob);
        upload(reader)
    });
});

function upload(reader) {
    reader.onloadend = function () {
        var base64data = reader.result;
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/breeder/configration/pets/upload",
            data: {image: base64data},
            success: function (data) {
                bs_modal.modal('hide');
                if (breederImgFlg == true) {
                    $('#img_thumbnail').attr('src', '/' + data).removeClass('hidden')
                    $('#breeder_src_path').val('/' + data);
                } else {
                    $('#img_license_thumbnail').attr('src', '/' + data).removeClass('hidden')
                    $('#breeder_license_src_path').val('/' + data);
                }
            }
        });
    };
}
