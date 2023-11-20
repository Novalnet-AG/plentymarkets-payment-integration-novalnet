jQuery(document).ready(function() {
    jQuery('.nn_number').on('input', function() {
        // Get the current input value
        var inputValue = jQuery(this).val();

        // Use a regular expression to allow only numbers (0-9)
        var numericValue = inputValue.replace(/[^0-9]/g, '');

        // Update the input field with the numeric value
        jQuery(this).val(numericValue);
    });
});



