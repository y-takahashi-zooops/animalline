var modalEl = document.getElementById('modal');
var bs_modal = new bootstrap.Modal(modalEl);
var image = document.getElementById('image');
var cropper, reader, file, size;


$("body").on("change", "#form_image", function (e) {
    e.preventDefault();
    var files = e.target.files;
    var done = function (url) {
        image.src = url;
        bs_modal.show();
    };


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

    modalEl.addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 2,
            preview: '.preview',
            dragMode: 'none',
            background: false,
        });
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
        $("#form_image").val('');
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
        reader.onloadend = function () {
            var base64data = reader.result;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/test/upload_image",
                data: {image: base64data},
                success: function (data) {
                    bs_modal.hide();
                    alert("success upload image");
                }
            });
        };
    });
});

