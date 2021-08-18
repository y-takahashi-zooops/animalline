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

    $(document).on('mouseover', '.img_preview', function () {
        $(this).parent().find('.btn_remove').show();
    });

    $(document).on('mouseout', '.img_preview', function () {
        $(this).parent().find('.btn_remove').hide();
    });

    $(document).on('click', '.btn_remove', function (e) {
        e.preventDefault();
        $(this).hide();
        $(this).parent().find('.img_preview').hide();
        $(this).parent().find('.form-control-file').show();
    });
});