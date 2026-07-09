jQuery(document).ready(function() {
    jQuery('.nn_number').on('input', function() {
        // Get the current input value
        var inputValue = jQuery(this).val();

        // Use a regular expression to allow only numbers (0-9)
        var numericValue = inputValue.replace(/[^0-9]/g, '');

        // Update the input field with the numeric value
        jQuery(this).val(numericValue);
    });

    function isValidAccountHolder(event) {
        var charCode = (event.which) ? event.which : event.keyCode;
        if ( !( charCode >= 65 && charCode <= 90 ) && // A-Z
            !( charCode >= 97 && charCode <= 122 ) && // a-z
            charCode !== 38 && // &
            charCode !== 45 && // -
            charCode !== 46 && // .
            charCode !== 32 // space
        ) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    // Attach events
    jQuery('#nn_account_holder').on('keypress keyup change', function (event) {
        return isValidAccountHolder(event);
    });
});



