$(function () {
    const ORGANIZATION_GROUP = 1;
    const ORGANIZATION_OTHER = 3;
    $('input[type=radio][name="breeder_examination_info[pedigree_organization]"]').on('change', (function () {
        if (this.value == ORGANIZATION_GROUP) {
            $('#breeder_examination_info_group_organization').prop('disabled', false).prop('required', true);
        } else {
            $('#breeder_examination_info_group_organization').prop('disabled', true).prop('required', false);
        }

        if (this.value == ORGANIZATION_OTHER) {
            $('#breeder_examination_info_pedigree_organization_other').prop('disabled', false).prop('required', true);
        } else {
            $('#breeder_examination_info_pedigree_organization_other').prop('disabled', true).prop('required', false);
        }
    }));

    const caseSize1 = $('#breeder_examination_info_cage_size_1');
    const caseSize2 = $('#breeder_examination_info_cage_size_2');
    const caseSize3 = $('#breeder_examination_info_cage_size_3');
    caseSize1.on('change', function () {
        handleChangeCageSize(caseSize1, caseSize2, caseSize3);
    });
    caseSize2.on('change', function () {
        handleChangeCageSize(caseSize1, caseSize2, caseSize3);
    });
    caseSize3.on('change', function () {
        handleChangeCageSize(caseSize1, caseSize2, caseSize3);
    });

    const EXERCISE_STATUS_OTHER = 4;
    $('input[type=radio][name="breeder_examination_info[exercise_status]"]').on('change', (function () {
        if (this.value == EXERCISE_STATUS_OTHER) {
            $('#breeder_examination_info_exercise_status_other').prop('disabled', false).prop('required', true);
        } else {
            $('#breeder_examination_info_exercise_status_other').prop('disabled', true).prop('required', false);
        }
    }));

    const PUPPIES_AVAILABLE_YES = 1;
    $('input[type=radio][name="breeder_examination_info[is_now_publising]"]').on('change', (function () {
        if (this.value == PUPPIES_AVAILABLE_YES) {
            $('#breeder_examination_info_publish_pet_count').prop('disabled', false).prop('required', true);
        } else {
            $('#breeder_examination_info_publish_pet_count').prop('disabled', true).prop('required', false);
        }
    }));

    if ($('#form-edit').length) {
        $('input[type=radio][name="breeder_examination_info[pedigree_organization]"]').trigger("change");
        $('input[type=radio][name="breeder_examination_info[exercise_status]"]').trigger("change");
        $('input[type=radio][name="breeder_examination_info[is_now_publising]"]').trigger("change");
    }

    $("form[name=breeder_examination_info]").submit(function (e) {
        e.preventDefault();
        $('#modal').modal('show');
    });

    $("#confirm-yes").click(function (e) {
        document.getElementsByName('breeder_examination_info')[0].submit();
    });
});

function handleChangeCageSize(caseSize1, caseSize2, caseSize3) {
    if (caseSize3.is(':checked')) {
        $('#breeder_examination_info_cage_size_other').prop('disabled', false).prop('required', true);
    } else {
        $('#breeder_examination_info_cage_size_other').prop('disabled', true).prop('required', false);
    }

    if (caseSize1.is(':checked') || caseSize2.is(':checked') || caseSize3.is(':checked')) {
        caseSize1.prop('required', false);
    } else {
        caseSize1.prop('required', true);
    }
}