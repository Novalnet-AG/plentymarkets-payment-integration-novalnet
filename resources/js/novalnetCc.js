jQuery(document).ready(function () {
    var script = document.createElement('script');
    script.setAttribute('type', 'text/javascript');
    script.setAttribute('src', 'https://cdn.novalnet.de/js/v2/NovalnetUtility.js'); 
    document.head.appendChild(script);
    script.addEventListener("load", function(event) {
    loadCardForm();
    jQuery('#nn_cc_form').submit( function (e) {
        jQuery('#novalnet_form_btn').prop('disabled', true);
        if(jQuery('#nn_pan_hash').val().trim() == '') {
            NovalnetUtility.getPanHash();
            e.preventDefault();
            e.stopImmediatePropagation();
        }
     });
   });  
});

function loadCardForm() {
    var customData  = jQuery('#nn_cc_formfields').val() != '' ? JSON.parse(jQuery('#nn_cc_formfields').val()) : null;
    var transactionData = jQuery('#nn_cc_formdetails').val() != '' ? JSON.parse(jQuery('#nn_cc_formdetails').val()) : null;
    // Set your Client key
    NovalnetUtility.setClientKey((transactionData.client_key !== undefined) ? transactionData.client_key : '');
    // Request object
    var requestData = {
        callback: {
            on_success: function (result) {
                jQuery('#nn_pan_hash').val(result['hash']);
                jQuery('#nn_unique_id').val(result['unique_id']);
                jQuery('#nn_cc3d_redirect').val(result['do_redirect']);
                jQuery('#nn_cc_form').submit();
                jQuery('.modal').hide();
                return true;
            },
            on_error: function (result) {
                if(undefined !== result['error_message']) {
                    jQuery('#novalnet_form_btn').prop('disabled', false);
                    alert(result['error_message']);
                    return false;
                }
            }
        },
        // You can customize the iframe container style, text etc.
        iframe: {
            // Pass the iframe id
            id: "nn_iframe",
            // Display the normal cc form
            inline: (transactionData.inline_form !== undefined) ? transactionData.inline_form : 0,
            // Adjust the card field style and text
            style: { // For style
                container: (customData.standard_style_css !== undefined) ? customData.standard_style_css + '.input-group input { width: calc(100% - 0.8rem); position: relative; left: 3; }'  : '',
                input: (customData.standard_style_field !== undefined) ? customData.standard_style_field : '' ,
                label: (customData.standard_style_label !== undefined) ? customData.standard_style_label : '' ,
            },
            text: { // For text
                error: (customData.credit_card_error !== undefined) ? customData.credit_card_error : '',
                card_holder : {
                    label: (customData.template_novalnet_cc_holder_Label !== undefined) ? customData.template_novalnet_cc_holder_Label : '',
                    place_holder: (customData.template_novalnet_cc_holder_input !== undefined) ? customData.template_novalnet_cc_holder_input : '',
                    error: (customData.template_novalnet_cc_error !== undefined) ? customData.template_novalnet_cc_error : ''
                },
                card_number : {
                    label: (customData.template_novalnet_cc_number_label !== undefined) ? customData.template_novalnet_cc_number_label : '',
                    place_holder: (customData.template_novalnet_cc_number_input !== undefined) ? customData.template_novalnet_cc_number_input : '',
                    error: (customData.template_novalnet_cc_error !== undefined) ? customData.template_novalnet_cc_error : ''
                },
                expiry_date : {
                    label: (customData.template_novalnet_cc_expirydate_label !== undefined) ? customData.template_novalnet_cc_expirydate_label : '',
                    place_holder: (customData.template_novalnet_cc_expirydate_input !== undefined) ? customData.template_novalnet_cc_expirydate_input : '',
                    error: (customData.template_novalnet_cc_error !== undefined) ? customData.template_novalnet_cc_error : ''
                },
                cvc : {
                    label: (customData.template_novalnet_cc_cvc_label !== undefined) ? customData.template_novalnet_cc_cvc_label : '',
                    place_holder: (customData.template_novalnet_cc_cvc_input !== undefined) ? customData.template_novalnet_cc_cvc_input : '',
                    error: (customData.template_novalnet_cc_error !== undefined) ? customData.template_novalnet_cc_error : ''
                }
            }
        },
        // Customer data
        customer: {
            first_name: (transactionData.first_name !== undefined) ? transactionData.first_name : '',
            last_name: (transactionData.last_name !== undefined) ? transactionData.last_name : transactionData.first_name,
            email: (transactionData.email !== undefined) ? transactionData.email : '',
            billing: {
                street: (transactionData.street !== undefined) ? transactionData.street : '',
                city: (transactionData.city !== undefined) ? transactionData.city : '',
                zip: (transactionData.zip !== undefined) ? transactionData.zip : '',
                country_code: (transactionData.country_code !== undefined) ? transactionData.country_code : ''
            },
            shipping: {
                    same_as_billing: (transactionData.same_as_billing !== undefined) ? transactionData.same_as_billing : 0,
                    street: (transactionData.shipping.street !== undefined) ? transactionData.shipping.street : '',
                    city: (transactionData.shipping.city !== undefined) ? transactionData.shipping.city : '',
                    zip: (transactionData.shipping.zip !== undefined) ? transactionData.shipping.zip : '',
                    country_code: (transactionData.shipping.country_code !== undefined) ? transactionData.shipping.country_code : ''
            }
        },
        // Transaction data
        transaction: {
            amount: (transactionData.amount !== undefined) ? transactionData.amount : '',
            currency: (transactionData.currency !== undefined) ? transactionData.currency : '',
            test_mode: (transactionData.test_mode !== undefined) ? transactionData.test_mode : 0,
            enforce_3d: (transactionData.enforce_3d !== undefined) ? transactionData.enforce_3d : 0
        },
        // Custom data
        custom: {
            lang: (transactionData.lang !== undefined) ? transactionData.lang : 'de'
        }
    };
    NovalnetUtility.createCreditCardForm(requestData);
    jQuery('.loader').hide(700);
}
