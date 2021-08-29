
const ORGANIZATION_GROUP = 1;
const ORGANIZATION_OTHER = 3;

const caseSize1 = $('#breeder_examination_info_cage_size_1');
const caseSize2 = $('#breeder_examination_info_cage_size_2');
const caseSize3 = $('#breeder_examination_info_cage_size_3');

$(function () {
    // init validation
    handleOrganization();
    handleChangeCageSize();
    handleExerciseStatus();
    handlePuppiesAvailable();

    $('input[type=radio][name="breeder_examination_info[pedigree_organization]"]').on('change', handleOrganization);

    caseSize1.on('change', handleChangeCageSize);
    caseSize2.on('change', handleChangeCageSize);
    caseSize3.on('change', handleChangeCageSize);

    $('input[type=radio][name="breeder_examination_info[exercise_status]"]').on('change', handleExerciseStatus);

    $('input[type=radio][name="breeder_examination_info[is_now_publising]"]').on('change', handlePuppiesAvailable);

    $("form[name=breeder_examination_info]").on('submit', function (e) {
        e.preventDefault();
        $('#modal').modal('show');
    });

    $("#confirm-yes").on('click', function (e) {
        document.getElementsByName('breeder_examination_info')[0].submit();
    });
});

function handleChangeCageSize() {
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

function handleOrganization() {
    if ($('#breeder_examination_info_pedigree_organization_0').is(':checked')) {
        $('#breeder_examination_info_group_organization').prop('disabled', false).prop('required', true);
    } else {
        $('#breeder_examination_info_group_organization').prop('disabled', true).prop('required', false);
    }

    if ($('#breeder_examination_info_pedigree_organization_2').is(':checked')) {
        $('#breeder_examination_info_pedigree_organization_other').prop('disabled', false).prop('required', true);
    } else {
        $('#breeder_examination_info_pedigree_organization_other').prop('disabled', true).prop('required', false);
    }
}

function handleExerciseStatus() {
    if ($('#breeder_examination_info_exercise_status_3').is(':checked')) {
        $('#breeder_examination_info_exercise_status_other').prop('disabled', false).prop('required', true);
    } else {
        $('#breeder_examination_info_exercise_status_other').prop('disabled', true).prop('required', false);
    }
}

function handlePuppiesAvailable() {
    if ($('#breeder_examination_info_is_now_publising_0').is(':checked')) {
        $('#breeder_examination_info_publish_pet_count').prop('disabled', false).prop('required', true);
    } else {
        $('#breeder_examination_info_publish_pet_count').prop('disabled', true).prop('required', false);
    }
}