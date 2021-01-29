
// For log section
jQuery( '.mci_log_update' ).on( 'click', function() {

	var order_id = jQuery( this ).data( 'orderid' );

	var data = {
		'action': 'mci_get_order_notes',
		'order_id': order_id
	};
	jQuery.post( ajaxurl, data, function( response ) {	
		jQuery( '.thick_content' ).html( response );
	});

} );

// for updated date and amount
jQuery('.mci_form_update').click( function( $ ) {

	var self = jQuery( this );
	var parent = self.parents( 'tr' );

	var userId = parent.find( '.mci_user_id' ).find( 'a' ).text();
	var name = parent.find( '.mci_name' ).find( 'a' ).text();
	var date = parent.find( '.mci_date' ).text();
	var orderNumber = parseInt( parent.find( '.mci_order_number' ).find( 'a' ).text() );
	var paymentNumber = jQuery( this ).data( 'current-payment' );

	var amount = parent.find( '.mci_amount' ).text();
	// var status = parent.find( '.mci_status :first-child' ).text();
	var status = 'processing';
	var payPrdId = jQuery( this ).data( 'prdid' );
	var payTimes = parseInt( self.attr( 'data-pay-times' ) );
	var payUserID = jQuery( this ).data( 'userid' );

    var payTimeCount = 1;
    var payTimeHTML = '';
	for( payTimeCount = 1; payTimeCount <= payTimes; payTimeCount++ ) {
		payTimeHTML += '<p class="thick_user_id">';
		payTimeHTML += '<label for="date'+ payTimeCount +'" class="thick_form_lable">Lucro '+ payTimeCount + ":" + '</label>';
		var payTimeDateInLoop = '.data-profit-'+ payTimeCount +'-date';
		var payTimeAmountInLoop = '.data-profit-'+ payTimeCount +'-amount';
		var payTimeDateValue = self.siblings( payTimeDateInLoop ).val();
		var payTimeAmountValue = self.siblings( payTimeAmountInLoop ).val();
		payTimeHTML += '<input type="date" class="mci_date_thick'+ payTimeCount +' mci_date_thick" value="'+ payTimeDateValue +'" />';
		payTimeHTML += '<input type="number" class="mci_amount_thick'+ payTimeCount +' mci_amount_thick" value="'+ payTimeAmountValue +'" />';
		payTimeHTML += '</p>'
	}

	jQuery( '#mci_edit_data' ).find( '.mci_user_id_thick' ).text( userId );
	jQuery( '#mci_edit_data' ).find( '.mci_name_thick' ).text( name );
	jQuery( '#mci_edit_data' ).find( '.mci_date_thick' ).val( date );
	jQuery( '#mci_edit_data' ).find( '.mci_order_number_thick' ).text( orderNumber );
	jQuery( '#mci_edit_data' ).find( '.mci_payment_number_thick' ).text( paymentNumber );
	jQuery( '#mci_edit_data' ).find( '.mci_amount_thick' ).val( amount );
	jQuery( '#mci_edit_data' ).find( '.mci_status_thick' ).text( status );
	jQuery( '#mci_edit_data' ).find( '.mci_data_update_thick' ).attr( 'data-order_id', orderNumber );
	jQuery( '#mci_edit_data' ).find( '.mci_status_cancel' ).attr( 'can-order_id', orderNumber );
	jQuery( '#mci_edit_data' ).find( '.mci_pay_now' ).attr( 'data-pay_now', orderNumber );
	jQuery( '#mci_edit_data' ).find( '.mci_pay_now' ).attr( 'data-prd-id', payPrdId );
	jQuery( '#mci_edit_data' ).find( '.mci_pay_now' ).attr( 'data-userid', payUserID );
	// jQuery( '#mci_profit_times' ).replaceWith( payTimeHTML ).change();
	jQuery( '#mci_profit_times' ).html( payTimeHTML );
} );
jQuery( '.mci_data_update_thick' ).click( function( e ) {
	e.preventDefault();
	
	var allPaymentValues = [],
		allPaymentDates = [];

	if( jQuery( '.mci_amount_thick' ).length > 0 && jQuery( '.mci_date_thick' ).length > 0 ) {
		jQuery.each( jQuery( '.mci_amount_thick' ), function( index, elem ) {
			allPaymentValues.push( jQuery( elem ).val() );
			allPaymentDates.push( jQuery( '.mci_date_thick' )[index].value );
		} );
	}
	if ( confirm( 'Do you want to update date and amount.?' ) ) {
		var parent = jQuery( '.mci_form_update' ).parents( 'tr' );
		var self = jQuery( this );
		var orderId = self.attr('data-order_id');
		var thickAmounts = jQuery( '.mci_amount_thick' ).val();
		var thickDate = jQuery('.mci_date_thick').val();

		var ajaxurl = PAY_ORDERS.ajaxURL;

		jQuery( this ).attr( 'disabled', true );
		jQuery( this ).val( 'Updating...' );

		var data = {
			'action': 'mci_update_date_and_amount',
			'mci_order_id': orderId,
			'mci_amount_values': allPaymentValues,
			'mci_date_values': allPaymentDates,
		};
		jQuery.post( ajaxurl, data, function( response ) {
			alert( 'Successfully Updated' );
			location.reload(true);
			console.log( response );
		});
	}
});

// for cancelled investment
jQuery('.mci_status_cancel').click( function( e ) {
	e.preventDefault();
	if ( confirm( 'Do you want to cancel investment.?' ) ) {
		var parent = jQuery( '.mci_form_update' ).parents( 'tr' );
		var self = jQuery( this );
		var orderId = self.attr('can-order_id');
		var ajaxurl = PAY_ORDERS.ajaxURL;

		jQuery( this ).attr( 'disabled', true );
		jQuery( this ).val( 'Cancelling...' );

		var data = {
			'action': 'mci_order_status_cancelled',
			'mci_can_order_id': orderId
		};
		jQuery.post( ajaxurl, data, function( response ) {
			alert( 'Investment Cancelled' );
			location.reload(true);
			console.log( response );
		});
	}
});

jQuery('.mci_pay_now').click( function( e ) {
	e.preventDefault();

	if ( confirm( 'Do you want to pay now.?' ) ) {
		var parent = jQuery( '.mci_form_update' ).parents( 'tr' );
		var self = jQuery( this );
		var payOrderId = self.attr('data-pay_now');
		// var payUserId = parent.find( '.mci_user_id' ).find( 'a' ).text();
		var payUserId = jQuery( this ).data( 'userid' );
		var payAmounts = [];

		if( jQuery( '.mci_amount_thick' ).length > 0 ) {
			jQuery.each( jQuery( '.mci_amount_thick' ), function( index, elem ) {
				payAmounts.push( jQuery( elem ).val() );
			} );
		}

		var ajaxurl = PAY_ORDERS.ajaxURL;
		jQuery( this ).attr( 'disabled', '' );
		jQuery( this ).val( 'Processing...' );
		var data = {
			action: 'mci_pay_now',
			mci_paynow_order_id: payOrderId,
			mci_paynow_user_id: payUserId,
			mci_pay_amount: payAmounts,
		};
		jQuery.post( ajaxurl, data, function( response ) {
			alert( 'Profit Awarded' );
			location.reload();
		});
	}	
});

