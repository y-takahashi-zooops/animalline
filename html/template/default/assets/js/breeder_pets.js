$(function () {
    $('.preview_box').append(`<svg fill="grey" onclick="$(this).parent().find('input[type=file]').click()" width="30%" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.387 5.807a.387.387 0 1 0-.774 0v5.806H5.806a.387.387 0 1 0 0 .774h5.807v5.807a.387.387 0 1 0 .774 0v-5.807h5.807a.387.387 0 1 0 0-.774h-5.807V5.807z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12zm0-.774c6.2 0 11.226-5.026 11.226-11.226C23.226 5.8 18.2.774 12 .774 5.8.774.774 5.8.774 12 .774 18.2 5.8 23.226 12 23.226z"></path></svg>`);

    $('#breeder_pets_pet_kind').on('change', function () {
        getDataByPetKind(this);
    });

    //$('#breeder_pets_pet_kind').trigger('change');

    function getDataByPetKind(petKindSelect) {
        let breed = null;
        const isEdit = !!$('#form-edit').length;
        if (isEdit) {
            breed = $('#breeder_pets_breeds_type').val();
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
                const { breeds } = data;
                $('#breeder_pets_breeds_type').empty();
                $('#breeder_pets_breeds_type').append(new Option('選択してください', ''));
                breeds.forEach(breed => {
                    $('#breeder_pets_breeds_type').append(new Option(breed.name, breed.id));
                });
                $(`#breeder_pets_breeds_type option[value=${breed.id}]`).prop("selected", true)
            })
            .fail(function (err) {
                console.error(err);
            });
    }
});