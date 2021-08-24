$(function () {
    $('#breeder_pets_pet_kind').on('change', function () {
        getDataByPetKind(this);
    });

    $('#breeder_pets_pet_kind').trigger('change');

    function getDataByPetKind(petKindSelect) {
        let breed = color = null;
        const isEdit = !!$('#form-edit').length;
        if (isEdit) {
            breed = $('#breeder_pets_breeds_type').val();
            color = $('#breeder_pets_coat_color').val();
        }

        $.ajax({
            type: 'get',
            url: '/breeder_pet_data_by_pet_kind',
            dataType: 'json',
            data: {
                pet_kind: petKindSelect.value
            }
        })
            .done(function (data) {
                const { breeds, colors } = data;
                $('#breeder_pets_breeds_type').empty();
                breeds.forEach(breed => {
                    $('#breeder_pets_breeds_type').append(new Option(breed.name, breed.id));
                });
                $('#breeder_pets_coat_color').empty();
                colors.forEach(color => {
                    $('#breeder_pets_coat_color').append(new Option(color.name, color.id));
                });

                if (isEdit) {
                    $('#breeder_pets_breeds_type').val(breed);
                    $('#breeder_pets_coat_color').val(color);
                }
            })
            .fail(function (err) {
                console.error(err);
            });
    }
});