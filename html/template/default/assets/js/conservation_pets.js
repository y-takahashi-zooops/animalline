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

    var inputImageId = '';
    var bs_modal = $('#modal');
    var image = document.getElementById('image');
    var cropper, reader, file, size;
    $(".form-control-file").change(function (e) {
        inputImageId = e.target.id;
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
        $(`#${ inputImageId }`).val('');
    });

    $("#crop").click(function () {
        console.log(cropper.imageData.naturalHeight);
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
                    url: "/test/list_adoption_pets/upload",
                    data: {image: base64data},
                    success: function (data) {
                        bs_modal.modal('hide');
                        $(`#${ inputImageId }`).hide();
                        $(`#${ inputImageId }`).parent().find('.img_preview').attr('src', '/' + data)
                        $(`#${ inputImageId }`).parent().find('.img_preview').show();
                    }
                });
            };
        });
    });

    $(document).on('mouseover', '.img_preview', function() {
        $(this).parent().find('.btn_remove').show();
    });

    $(document).on('mouseout', '.img_preview', function() {
        $(this).parent().find('.btn_remove').hide();
    });

    $(document).on('click', '.btn_remove', function(e) {
        e.preventDefault();
        $(this).hide();
        $(this).parent().find('.img_preview').hide();
        $(this).parent().find('.form-control-file').show();
    });
});
