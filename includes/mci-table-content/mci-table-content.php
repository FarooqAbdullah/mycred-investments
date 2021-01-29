<?php 


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Investment Orders record table
 */
class Investment_Orders extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$url_order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		$url_order = isset( $_GET['order'] ) ? $_GET['order'] : '';

		$mci_search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';

		$datas = $this->mci_list_table_data( $url_order_by, $url_order, $mci_search_term );

		$per_page = 50;
		$current_page = $this->get_pagenum();
		$total_items = count( $datas );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );

		$this->items = array_slice( $datas, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$mci_columns = $this->get_columns();
		$mci_hidden = $this->get_hidden_columns();
		$mci_sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $mci_columns, $mci_hidden, $mci_sortable );
	}

	/**
	 * Display columns datas
	 */
	public function mci_list_table_data( $url_order_by = '', $url_order = '', $mci_search_term = '' ) {

		$data_array = array();

		/**
		 * Start filteration
		 */
		if( isset( $_POST['mci_filter'] ) && current_user_can( 'manage_options' ) && ! empty( $_POST ) && check_admin_referer( 'mci_nonce', 'mci_nonce_field' ) ) {
			$filter_user_id = isset( $_POST['mcif_user_id'] ) ? $_POST['mcif_user_id'] : '';
			$filter_name = isset( $_POST['mcif_name'] ) ? $_POST['mcif_name'] : '';
			$filter_start_date = isset( $_POST['mcif_start_date'] ) ? $_POST['mcif_start_date'] : '';
			$filter_end_date = isset( $_POST['mcif_end_date'] ) ? $_POST['mcif_end_date'] : '';
			$filter_order_number = isset( $_POST['mcif_order_number'] ) ? $_POST['mcif_order_number'] : '';
			$filter_payment_number = isset( $_POST['mcif_payment_number'] ) ? $_POST['mcif_payment_number'] : '';
			$filter_amount = isset( $_POST['mcif_amount'] ) ? (float) $_POST['mcif_amount'] : '';
			$filter_status = isset( $_POST['mcif_status'] ) ? $_POST['mcif_status'] : '';
		}

		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?page='.$_GET['page'].''; ?>" class="mci_filter_form">
			<p>	
				<label><?php _e( 'User ID', MCI ); ?></label>
				<input type="number" name="mcif_user_id" value="<?php echo $filter_user_id; ?>">
				<label><?php _e( 'Name', MCI ); ?></label>
				<input type="text" name="mcif_name" value="<?php echo $filter_name; ?>">
				<label><?php _e( 'Start Date', MCI ); ?></label>
				<input type="date" name="mcif_start_date" value="<?php echo $filter_start_date; ?>">
				<label><?php _e( 'End Date', MCI ); ?></label>
				<input type="date" name="mcif_end_date" value="<?php echo $filter_end_date; ?>">
			</p>
			<p>
				<label><?php _e( 'Order number', MCI ); ?></label>
				<input type="number" name="mcif_order_number" value="<?php echo $filter_order_number; ?>">
				<label><?php _e( 'Payment number', MCI ); ?></label>
				<input type="number" name="mcif_payment_number" value="<?php echo $filter_payment_number; ?>">
				<label><?php _e( 'Amount', MCI ); ?></label>
				<input type="number" name="mcif_amount" step=".01" value="<?php echo $filter_amount; ?>">
				<label for="status"> <?php _e( 'Status ', MCI ); ?> </label>
				<select name="mcif_status" class="mci_status">
					<option value="" disabled selected> <?php _e( 'select', MCI ); ?> </option>
					<option value="on-hold" <?php echo (isset($_POST['mcif_status']) && $_POST['mcif_status'] == 'on-hold') ? 'selected="selected"' : ''; ?> class="mci_status_on_hold"> <?php _e( 'On hold', MCI ); ?> </option>
					<option value="processing" <?php echo (isset($_POST['mcif_status']) && $_POST['mcif_status'] == 'processing') ? 'selected="selected"' : ''; ?> class="mci_status_processing"> <?php _e( 'Processing', MCI ); ?> </option>
					<option value="completed" <?php echo (isset($_POST['mcif_status']) && $_POST['mcif_status'] == 'completed') ? 'selected="selected"' : ''; ?> class="mci_status_completed"> <?php _e( 'Completed', MCI ); ?> </option>
					<option value="cancelled" <?php echo (isset($_POST['mcif_status']) && $_POST['mcif_status'] == 'cancelled') ? 'selected="selected"' : ''; ?> class="mci_status_cancelled"> <?php _e( 'Cancelled', MCI ); ?> </option>
				</select>
			</p>
			<input type="submit" name="mci_filter" value="<?php _e( 'Filter', MCI ); ?>" class="mci_filter_submit_button">
			<?php 
			/**
             * Retrieve or display nonce hidden field for forms.
             */
			wp_nonce_field( 'mci_nonce', 'mci_nonce_field' );
			?>
			<input type="hidden" name="action" value="mci_settings">
		</form>
		<?php

        if( isset( $_POST['action'] ) && $_POST['action'] == 'mci_settings'  ) {
            $f_user_id = isset( $_POST['mcif_user_id'] ) ? $_POST['mcif_user_id'] : 0;
            $f_user_name = isset( $_POST['mcif_name'] ) ? $_POST['mcif_name'] : '';
            $f_start_date = isset( $_POST['mcif_start_date'] ) ? $_POST['mcif_start_date'] : '';
            $f_end_date = isset( $_POST['mcif_end_date'] ) ? $_POST['mcif_end_date'] : '';
            $f_order_number = isset( $_POST['mcif_order_number'] ) ? (int) $_POST['mcif_order_number'] : 0;
            $f_payment_number = isset( $_POST['mcif_payment_number'] ) ? (int) $_POST['mcif_payment_number'] : 0;
            $f_amount = isset( $_POST['mcif_amount'] ) ? $_POST['mcif_amount'] : 0;
            $f_status = isset( $_POST['mcif_status'] ) ? $_POST['mcif_status'] : '';
        }

        $filter_order_argument = array(
            'limit'	=> -1,
            'meta_key'     => 'mci_investment_order',
            'meta_compare' => 'EXISTS',
        );

        /**
         * Filter by user id
         */
		if( $f_user_id ) {
            $filter_order_argument['mci_investment_order'] = (int) $f_user_id;
        }

        /**
         * Filter by username
         */
        if( ! empty( $f_user_name ) ) {
            $user = get_user_by( 'login', $f_user_name );
            if( $user ) {
                $filter_order_argument['_customer_user'] = (int) $user->ID;
            }
        }

        /**
         * Filter by date
         */
        if( ! empty( $f_start_date ) && ! empty( $f_end_date ) ) {
            $filter_order_argument['mci_next_payment_start'] = $f_start_date;
            $filter_order_argument['mci_next_payment_end'] = $f_end_date;
        }

        /**
         * Filter by order number
         */
        if( ! empty( $f_order_number ) ) {
            $filter_order_argument['mci_order_number'] = $f_order_number;
        }

        /**
         * Filter by payment number
         */
        if( ! empty( $f_payment_number ) ) {
            $filter_order_argument['mci_paid_times'] = $f_payment_number;
        }

        /**
         * Filter by amount
         */ 
        if( ! empty( $f_amount ) ) {
            $filter_order_argument['mci_next_profit'] = $f_amount;
        }

        /**
         * Filter by status
         */
        if( ! empty( $f_status ) ) {
            $filter_order_argument['status'] = $f_status;
        }

		$orders = wc_get_orders( $filter_order_argument );

		foreach( $orders as $order ) {
			$user = get_user_by( 'id', $order->customer_id );
			if( !$user ) {
				continue;
			}

			$order_id = $order->id;
			$user_id = $order->customer_id;

			$user_profile_url = add_query_arg( 'user_id', $user->data->ID, self_admin_url( 'user-edit.php'));
			if( ! $user_profile_url ) {
				continue;
			}

			$order_url = $order->get_edit_order_url();
			if( ! $order_url ) {
				continue;
			}

			global $wpdb;
			$order = wc_get_order( $order_id );
			$items = $order->get_items();

			foreach ( $items as $item ) {

				$pay_times = get_post_meta( $order_id, 'mci_pay_times', true );
				$profit_dates = '';
				$profit_amount = '';
				$payment_number = '';
				$investment_status_html = '';

				for ( $x = 1; $x <= $pay_times; $x++ ) {

					/**
					 * Get next payment date
					 */
					$meta_key = 'mci_next_payment_' . $x;
					$date = get_post_meta( $order_id, $meta_key, true );

					/**
					 * Get next Profit amount
					 */
					$amount_meta_key = 'mci_next_profit_' . $x;
					$amount = get_post_meta( $order_id, $amount_meta_key, true );

					$profit_dates .= '<p>'.$date.'</p>';
					$profit_amount .= '<p>'.$amount.'</p>';
					$payment_number .= '<p>'.$x.'</p>';

					$mci_paid_times = ( int ) get_post_meta( $order_id, 'mci_paid_times', true );
					
					$investment_status = '';
					if( $mci_paid_times >= $x ) {
						$investment_status = 'paid';
					} else {
						$investment_status = 'processing';
					}

					if( $order->status == 'cancelled' && $investment_status == 'processing' ) {
						$investment_status = 'cancelled';
					}

					$investment_status_html .= '<p>'.$investment_status.'</p>';
				}

				/**
				 * Get payment number
				 */
				$mci_payment_number = ( int )get_post_meta( $order_id, 'mci_payment_number', true );

				if( $mci_payment_number > $pay_times ) {
					$mci_payment_number = '-';
				}

			    $button = '-';

			    if( $order->status != 'processing' && $order->status != 'completed' ) {
			    	$mci_payment_number = '-';
                    // $profit_amount = '-';
                    $profit_dates = '-';
			    }

			    $paid = 'paid';
			    if( $order->status == 'completed' ) {
			    	$order->status = $paid;
			    }

			    $mci_payment_times = (int) get_post_meta( $order_id, 'mci_pay_times', true );
			    $mci_profits = '';
			    if( $mci_payment_times ) {
			    	
			    	for( $x = 1; $x <= $mci_payment_times; $x++ ) {
			    		$pay_time_amount_meta_key = 'mci_next_profit_' . $x;
			    		$pay_time_date_meta_key = 'mci_next_payment_' . $x;
			    		$mci_profits .= '<input type="hidden" class="data-profit-'. $x .'-amount" name="data-profit-'. $x .'-amount" value="'. get_post_meta( $order_id, $pay_time_amount_meta_key, true ) .'">';
			    		$mci_profits .= '<input type="hidden" class="data-profit-'. $x .'-date" name="data-profit-'. $x .'-date" value="'. get_post_meta( $order_id, $pay_time_date_meta_key, true ) .'">';
			    	}
			    }
			    $current_payment_number = get_post_meta( $order_id, 'mci_payment_number', true );
			    if( $order->status == 'processing' ) {
			    	$button = $mci_profits . '<a data-pay-times="'.  $mci_payment_times .'" href="#TB_inline?&width=500&height=530&inlineId=mci_edit_data" class="thickbox mci_form_update" title="Investment Actions" data-prdid="'.$product_id.'" data-userid="'.$user_id.'" data-current-payment="'.$current_payment_number.'">'.__( 'Edit', MCI ).'</a>';
			    }
				
			    $data_array[] = array(
			    	'mci_user_id'		=> '<a href="'.$user_profile_url.'">'. $user_id .'</a>',
			    	'mci_name'			=> '<a href="'.$user_profile_url.'">'. $user->data->display_name .'</a>',
			    	'mci_date'			=> $profit_dates,
			    	'mci_order_number'	=> '<a href="'.$order_url.'">'. $order_id .'</a>',
			    	'mci_payment_number'=> $payment_number,
			    	'mci_amount'		=> $profit_amount,
			    	'mci_status'		=> __( $investment_status_html, MCI ),
			    	'mci_button'		=> $button,
			    	'mci_log_button'	=> '<a href="#TB_inline?&width=450&height=530&inlineId=mci_log_thick_box&order_id='.$order_id.'" class="thickbox mci_log_update" title="Investment Logs" data-orderid="'.$order_id.'">'.__( 'Check Logs', MCI ).'</a>'
			    );
			}
		}
		return $data_array;
	}

	public function get_hidden_columns() {
		return array();
	}

	/**
	 * All comlumn names and unique id
	 */
	public function get_columns() {
		$columns = array(
			'cb'				=> '<input type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />',
			'mci_user_id'		=> __( 'User ID', MCI ),
			'mci_name'			=> __( 'Name', MCI ),
			'mci_date'			=> __( 'Date', MCI ),
			'mci_order_number'	=> __( 'Order number', MCI ),
			'mci_payment_number'=> __( 'Payment number', MCI ),
			'mci_amount'		=> __( 'Amount', MCI ),
			'mci_status'		=> __( 'Status', MCI ),
			'mci_button'		=> __( 'Actions', MCI ),
			'mci_log_button'	=> __( 'Logs', MCI ),
		);
		return $columns;
	}

	/**
	 * Return column value
	 *
	 * @param $item, $column name
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ($column_name) {
			case 'mci_user_id':
			case 'mci_name':
			case 'mci_date':
			case 'mci_order_number':
			case 'mci_payment_number':
			case 'mci_amount':
			case 'mci_status':
			case 'mci_button':
			case 'mci_log_button':
			return $item[$column_name];
			default:
			return 'no value';
		}
	}

	/**
	 * Rows check box
	 *
	 * @param $items
	 * @return field
	 */
	public function column_cb( $items ) {
		$top_checkbox = '<input type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />';
		return $top_checkbox; 
	}

}

/**
 * The main function responsible for returning the one true WP_list_table instance to functions everywhere
 */
function mci_list_table_layout() {

	$mci_list_table = new Investment_Orders();

	$mci_list_table->prepare_items();
	
	$mci_list_table->display();
}
mci_list_table_layout();