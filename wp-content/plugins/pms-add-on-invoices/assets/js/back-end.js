jQuery( function($) {

	/**
	 * Enable/disable the "Next Invoice Number" when checking "Reset Invoice Counter" checkbox
	 *
	 */
	$(document).on( 'click', '#invoices-reset-invoice-counter', function() {

		if( $(this).is(':checked') )
			$('#invoices-next-invoice-number').attr( 'disabled', false ).attr( 'readonly', false ).focus();
		else
			$('#invoices-next-invoice-number').attr( 'disabled', true ).attr( 'readonly', true );

	});

});