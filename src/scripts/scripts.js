(function() {
    const errorCls = 'has-error';
    const tcsElement = $('#tcs');

    $(document).ready(() => {
        toggleExemption();

        $('#exemption').change(toggleExemption);

        $('#form_submit_button').click(() => {
            const form = $('#reg_form');
            hideError(tcsElement);
            if (isFormValid()) {
                if (tcsElement.is(":checked")) {
                    form.submit();
                } else {
                    showError(tcsElement);
                }
            }
        });
    });

    const isFormValid = function() {
        let isValid = true;
        const requiredIDs = [
            'last_name',
            'first_name',
            'zip',
            'city',
            'reg_num',
            'dob',
            'nationality',
            'id_number',
            'unit',
            'room',
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

        const dobElement = $('#dob');
        if (!dobElement[0].checkValidity()) {
            dobElement[0].reportValidity();
            showError(dobElement);
            isValid = false;
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
    };

    const hideExemption = function() {
        $('#exemption_proof_type').parent('.columns').hide();
        $('#exemption_proof_num').parent('.columns').hide();
    };

    const showExemption = function() {
        $('#exemption_proof_type').parent('.columns').show();
        $('#exemption_proof_num').parent('.columns').show();
    };

    const isRequiredFieldValid = function(id) {
        const element = $(`#${id}`);
        hideError(element);
        if (element.val().trim() == '') {
            showError(element);
            return false;
        }
        return true;
    };

    const hideError = function(element) {
        getParentColumn(element).removeClass(errorCls);
    };

    const showError = function(element) {
        getParentColumn(element).addClass(errorCls);
    };

    const getParentColumn = function(element) {
        return $(element).parent('.columns');
    };
})()



