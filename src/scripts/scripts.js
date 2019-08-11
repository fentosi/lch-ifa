$(document).ready(() => {
    toggleExemption();

    $('#exemption').change(toggleExemption);

    $('#submit').click(() => {
        isFormValid();
    });
});

const isFormValid = function() {
    let isValid = true;
    const requiredIDs = [
        'name',
        'zip',
        'reg_num',
        'dob',
        'nationality',
        'id_number',
        'arrival_date',
        'departure_date'
    ];

    requiredIDs.forEach((id) => {
        if (!isRequiredFieldValid(id)) {
            isValid = false;
        }
    });

    const exemptionSelected = $('#exemption').val() != 'Nincs';
    if (exemptionSelected) {
        if (!isRequiredFieldValid('exemption_proof_type')) {
            isValid = false;
        }
        if (!isRequiredFieldValid('exemption_proof_num')) {
            isValid = false;
        }
    }

    return isValid;
}

const toggleExemption = function() {
    const exemptionSelected = $('#exemption').val() != 'Nincs';
    if (exemptionSelected) {
        showExemption();
    }
    else {
        hideExemption();
    }
}

const hideExemption = function() {
    $('#exemption_proof_type').parent('.columns').hide();
    $('#exemption_proof_num').parent('.columns').hide();
}

const showExemption = function() {
    $('#exemption_proof_type').parent('.columns').show();
    $('#exemption_proof_num').parent('.columns').show();
}
function isRequiredFieldValid(id) {
    
    const errorCls = 'has-error';
    const element = $(`#${id}`);
    const parentColumn = $(element).parent('.columns');
    parentColumn.removeClass(errorCls);
    if (element.val().trim() == '') {
        parentColumn.addClass(errorCls);
        return false;
    }
    return true;
}

