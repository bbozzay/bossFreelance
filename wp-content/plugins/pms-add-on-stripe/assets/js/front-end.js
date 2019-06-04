jQuery( function( $ ) {

    /*
     * Handle Stripe JS token creation
     *
     */
    // Paid Member Subscription submit buttons
    var payment_buttons  = 'input[name=pms_register], ';
        payment_buttons += 'input[name=pms_new_subscription], ';
        payment_buttons += 'input[name=pms_upgrade_subscription], ';
        payment_buttons += 'input[name=pms_renew_subscription], ';
        payment_buttons += 'input[name=pms_confirm_retry_payment_subscription], ';

    // Profile Builder submit buttons
        payment_buttons += '.wppb-register-user input[name=register]';

    $(document).on( 'click', payment_buttons, function() {

        if( $('input[type=hidden][name=pay_gate]').val() != 'stripe' && $('input[type=radio][name=pay_gate]:checked').val() != 'stripe' )
            return;

        if( $('input[type=hidden][name=pay_gate]').is(':disabled') || $('input[type=radio][name=pay_gate]:checked').is(':disabled') )
            return;

        var stripe_pk = $( '#stripe-pk' ).val();
        //compatibility with PB conditional logic. if there are multiple subscription plans fields and the first one is hidden then it won't have a value attribute because of conditional logic
        if( stripe_pk == '' ) {
            stripe_pk = $('#stripe-pk').attr('conditional-value');
        }

        if( stripe_pk == '' )
            return false;

        Stripe.setPublishableKey( stripe_pk );

        // Needed fields
        var card_number     = $('input[name="pms_card_number"]').val().trim();
        var card_cvv        = $('input[name="pms_card_cvv"]').val().trim();
        var card_exp_month  = $('select[name="pms_card_exp_month"]').val().trim();
        var card_exp_year   = $('select[name="pms_card_exp_year"]').val().trim();

        // Optional fields
        var billing_name    = $('input[name="pms_billing_first_name"]').val().trim() + ' ' + $('input[name="pms_billing_last_name"]').val().trim();
        var billing_address = $('input[name="pms_billing_address"]').val().trim();
        var billing_city    = $('input[name="pms_billing_city"]').val().trim();
        var billing_zip     = $('input[name="pms_billing_zip"]').val().trim();
        var billing_country = $('select[name="pms_billing_country"]').val().trim();
        var billing_state   = $('input[name="pms_billing_state"]').val().trim();


        var has_error = false;
        $.pms_clean_field_errors();

        // Validate credit card
        if( !Stripe.card.validateCardNumber( card_number ) ) {
            $.pms_add_field_error( 'Please enter a valid card number.', 'pms_card_number' );
            has_error = true;
        }

        // Validate card expiration
        if( !Stripe.card.validateExpiry( card_exp_month, card_exp_year ) ) {
            $.pms_add_field_error( 'Please enter a valid card expiration date.', 'pms_card_exp_year' );
            has_error = true;
        }

        // Validate CVV
        if( !Stripe.card.validateCVC( card_cvv ) ) {
            $.pms_add_field_error( 'Please enter a valid card verification value.', 'pms_card_cvv' );
            has_error = true;
        }

        // Do nothing if validation fails
        if( has_error ) {

            $button = $(this);
            
            setTimeout( function() {
                $button.attr( 'disabled', false ).removeClass( 'pms-submit-disabled' ).val( $button.data( 'original-value' ) );    
            }, 1 );
            

            return false;
        
        }

        // Disable the button
        //$(this).attr( 'disabled', true );

        Stripe.card.createToken({
            number:         card_number,
            cvc:            card_cvv,
            exp_month:      card_exp_month,
            exp_year:       card_exp_year,
            name:           billing_name,
            address_line1:  billing_address,
            address_city:   billing_city,
            address_state:  billing_state,
            address_zip:    billing_zip,
            address_country:billing_country
        }, stripeResponseHandler );

        return false;

    });

    /*
     * Stripe response handler
     *
     */
    function stripeResponseHandler( status, response ) {

        if( !response.error ) {

            $form = $(payment_buttons).closest('form');
            $form.append( $('<input type="hidden" name="stripe_token" />').val( response.id ) );

            // We have to append a hidden input to the form to simulate that the submit
            // button has been clicked to have it to the $_POST
            var button_name = $form.find(payment_buttons).attr('name');
            var button_value = $form.find(payment_buttons).val();

            $form.append( $('<input type="hidden" />').val( button_value ).attr('name', button_name ) );

            $form.get(0).submit();

        }

    }

});