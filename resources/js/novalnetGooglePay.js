jQuery(document).ready(function() {
    // Load the Google Pay button
    try {
        // Load the payment instances
        var NovalnetPaymentInstance  = NovalnetPayment();
        var NovalnetWalletPaymentObj = NovalnetPaymentInstance.createPaymentObject();
        // Setup the payment intent
        var requestData = {
            clientKey: String(jQuery('#nn_client_key').val()),
            paymentIntent: {
                merchant: {
                    paymentDataPresent: false,
                    countryCode : String(jQuery('#nn_google_pay').attr('data-country')),
                    partnerId: jQuery('#nn_merchant_id').val(),
                },
                transaction: {
                    setPendingPayment: true,
                    amount: String(jQuery('#nn_google_pay').attr('data-total-amount')),
                    currency: String(jQuery('#nn_google_pay').attr('data-currency')),
                    enforce3d: Boolean(jQuery('#nn_enforce').val()),
                    paymentMethod: "GOOGLEPAY",
                    environment: jQuery('#nn_environment').val(),
                },
                custom: {
                    lang: String(jQuery('#nn_google_pay').attr('data-order-lang'))
                },
                order: {
                    paymentDataPresent: false,
                    merchantName: String(jQuery('#nn_business_name').val()),
                },
                button: {
                    type: jQuery('#nn_button_type').val(),
                    locale: ( String(jQuery('#nn_google_pay').attr('data-order-lang')) == 'EN' ) ? "en-US" : "de-DE",
                    boxSizing: "fill",
                    dimensions: {
                        height: parseInt(jQuery('#nn_button_height').val())
                    }
                },
                callbacks: {
                    onProcessCompletion: function (response, processedStatus) {
                        processedStatus({status: "SUCCESS", statusText: ""});
                        // Only on success, we proceed further with the booking
                        if(response.result.status == "SUCCESS") {
                            jQuery('#nn_google_pay_token').val(response.transaction.token);
                            jQuery('#nn_google_pay_do_redirect').val(response.transaction.doRedirect);                               
                            jQuery('#nn_google_pay_form').submit();
                            jQuery('#nn_google_pay').find('button').prop('disabled', true);
                        } else {
                            // Upon failure, displaying the error text
                            if(response.result.status_text) {
                                alert(response.result.status_text);
                            }
                        }
                    },
                    onPaymentButtonClicked: function(clickResult) {
                        if(((jQuery('.widget-gtc-check input[type="checkbox"]').length > 0 && !jQuery('.widget-gtc-check input[type="checkbox"]').is(':checked')) || (jQuery('.form-check input[type="checkbox"]').length > 0 && !jQuery('.form-check input[type="checkbox"]').is(':checked'))) && jQuery('#nn_reinitializePayment').val() != 1) {
                           alert(jQuery('#nn_accept_gtc').val());
                           clickResult({status: "FAILURE"});
                        } else {
                           clickResult({status: "SUCCESS"});    
                        }
                    }
                }
            }
        };
        NovalnetWalletPaymentObj.setPaymentIntent(requestData);
        // Checking for the Payment method availability
        NovalnetWalletPaymentObj.isPaymentMethodAvailable(function(displayGooglePayButton) {
            var mopId = jQuery('#nn_google_pay_mop').val();
            if(displayGooglePayButton) {
                // Display the Google Pay payment
                if(jQuery('#nn_reinitializePayment').val() == 1) {
                    // Initiating the payment request for the wallet payment
                    NovalnetWalletPaymentObj.addPaymentButton("#nn_google_pay");
                } else {
                    jQuery('li[data-id="'+mopId+'"]').show();
                    jQuery('li[data-id="'+mopId+'"]').click(function() {
                        jQuery('#nn_google_pay').empty();
                        // Initiating the payment request for the wallet payment
                        NovalnetWalletPaymentObj.addPaymentButton("#nn_google_pay");
                        jQuery('.widget-place-order').children('div').hide();
                        jQuery('#nn_google_pay_form').prev('div').hide();
                    });
                    if(jQuery('input[type="radio"][id*='+mopId+']').is(':checked')) {
                        jQuery('li[data-id="'+mopId+'"]').click();
                    } else {
                        jQuery('.widget-place-order').children('div').show();
                        jQuery('#nn_google_pay_form').prev('div').show();
                        jQuery('.gpay-card-info-container-fill').hide();
                    }
                }
            } else {
                // Hide the Google Pay payment if it is not possible
                jQuery('li[data-id="'+mopId+'"]').hide();
            }

            jQuery('.method-list-item').on('click',function() {
                var clickedId = jQuery(this).attr('data-id');
                if(clickedId !== undefined && clickedId != mopId) {
                    jQuery("#nn_google_pay").hide();  
                    jQuery('.widget-place-order').children('div').show();
                    jQuery('#nn_google_pay_form').prev('div').show();
               } else {
                    jQuery("#nn_google_pay").show();                    
                    jQuery('.widget-place-order').children('div').hide();
                    jQuery('#nn_google_pay_form').prev('div').hide();
               }
            });
        });
    } catch (e) {
        // Handling the errors from the payment intent setup
        console.log(e.message);
    }
});
