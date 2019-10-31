jQuery(document).ready( function() {
    jQuery('#nn_sepa_iban').on('input',function ( event ) {
        let iban = jQuery(this).val().replace( /[^a-zA-Z0-9]+/g, "" ).replace( /\s+/g, "" );
            $(this).val(iban);      
    });
    
  
  jQuery( '#nn_sepa_cardholder' ).keypress(
                function (event) {
        var keycode = ( 'which' in event ) ? event.which : event.keyCode,
                reg     = /[^0-9\[\]\/\\#,+@!^()$~%'"=:;<>{}\_\|*?`]/g;
            return ( reg.test( String.fromCharCode( keycode ) ) || 0 === keycode || 8 === keycode );
                    }
            );
            
    jQuery('#novalnet_form').on('submit',function(){
      $('#novalnet_form_btn').attr('disabled',true);      
    });
  
});
