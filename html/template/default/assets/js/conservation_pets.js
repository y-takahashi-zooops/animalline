$(function () {
    $('#conservation_pets_pet_kind').on('change', function () {
        getDataByPetKind(this);
    });

    $('#conservation_pets_pet_kind').trigger('change');

    function getDataByPetKind(petKindSelect) {
        let breed = color = null;
        const isEdit = !!$('#form-edit').length;
        if (isEdit) {
            breed = $('#conservation_pets_BreedsType').val();
            color = $('#conservation_pets_CoatColor').val();
        }

        $.ajax({
            type: 'get',
            url: '/pet_data_by_pet_kind',
            dataType: 'json',
            data: {
                pet_kind: petKindSelect.value
            }
        })
            .done(function (data) {
                const { breeds, colors } = data;
                $('#conservation_pets_BreedsType').empty();
                breeds.forEach(breed => {
                    $('#conservation_pets_BreedsType').append(new Option(breed.name, breed.id));
                });
                $('#conservation_pets_CoatColor').empty();
                colors.forEach(color => {
                    $('#conservation_pets_CoatColor').append(new Option(color.name, color.id));
                });

                if (isEdit) {
                    $('#conservation_pets_BreedsType').val(breed);
                    $('#conservation_pets_CoatColor').val(color);
                }
            })
            .fail(function (err) {
                console.error(err);
            });
    }
});