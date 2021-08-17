$(function () {
    $('#conservation_pets_pet_kind').on('change', function () {
        getDataByPetKind(this);
    });

    $('#conservation_pets_pet_kind').trigger('change');

    function getDataByPetKind(petKindSelect) {
        $.ajax({
            type: 'get',
            url: '/test/list_adoption_pets/by_pet_kind',
            dataType: 'json',
            data: {
                pet_kind: petKindSelect.value
            }
        })
            .done(function (data) {
                const { breeds, colors } = data;
                $('#conservation_pets_breeds_type').empty();
                breeds.forEach(breed => {
                    $('#conservation_pets_breeds_type').append(new Option(breed.name, breed.id));
                });
                $('#conservation_pets_coat_color').empty();
                colors.forEach(color => {
                    $('#conservation_pets_coat_color').append(new Option(color.name, color.id));
                });
            })
            .fail(function (err) {
                console.error(err);
            });
    }

    var modalId = '';
    var bs_modal = $('#modal');
    var image = document.getElementById('image');
    var cropper, reader, file, size;
    $(".form-control-file").change(function (e) {
        modalId = e.target.id;
        var files = e.target.files;
        var done = function (url) {
            image.src = url;
            bs_modal.modal('show');
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

    bs_modal.on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 2,
            preview: '.preview',
            dragMode: 'none',
            background: false,
        });
    }).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
        $(modalId).val('');
    });

    // $("#crop").click(function () {
    //     size = cropper.imageData.naturalHeight < cropper.imageData.naturalWidth ? cropper.imageData.naturalHeight : cropper.imageData.naturalWidth;
    //     size = size < 1000 ? size : 1000;
    //     canvas = cropper.getCroppedCanvas({
    //         width: size,
    //         height: size,
    //     });

    //     canvas.toBlob(function (blob) {
    //         url = URL.createObjectURL(blob);
    //         var reader = new FileReader();
    //         reader.readAsDataURL(blob);
    //         reader.onloadend = function () {
    //             var base64data = reader.result;
    //             $.ajax({
    //                 type: "POST",
    //                 dataType: "json",
    //                 url: "/test/upload_image",
    //                 data: {image: base64data},
    //                 success: function (data) {
    //                     bs_modal.modal('hide');
    //                     alert("success upload image");
    //                 }
    //             });
    //         };
    //     });
    // });
});
