<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MCI
 */
class Mci_Payment_Orders {

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

    	if ( is_null( self::$instance ) && ! ( self::$instance instanceof Mci_Payment_Orders ) ) {
    		self::$instance = new self;

    		self::$instance->hooks();
    	}

    	return self::$instance;
    }

    /**
     * Plugin Hooks
     */
    public function hooks() {
    	add_action( 'admin_menu', [ $this, 'mci_payment_order_new_menu' ], 11 );
    	add_action( 'in_admin_footer', [ $this, 'mci_pop_up_edit_window' ] );
    	add_action( 'admin_enqueue_scripts', [ $this, 'mci_payment_order_include_js_file' ] );
        add_action( 'wp_ajax_mci_update_date_and_amount', [ $this, 'mci_ajax_form_data_request' ] );
        add_action( 'wp_ajax_mci_order_status_cancelled', [ $this, 'mci_ajax_order_status_cancalled' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'mci_include_css_file_front' ] );
        add_shortcode( 'mci_user_investment_record', [ $this, 'mci_user_investment_record_table' ] );
        add_action( 'wp_ajax_mci_pay_now', [ $this, 'mci_ajax_order_pay_now' ] );
        add_action( 'wp_ajax_mci_get_order_notes', [ $this, 'mci_get_order_notes' ] );
        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'mci_order_filters' ], 10, 2 );
    }

    /**
     * Payment data filter query
     */
    public function mci_order_filters( $query, $query_vars ) {

        if ( ! empty( $query_vars['mci_investment_order'] ) ) {
            $query['meta_query'][] = array(
                'key' => 'mci_investment_order',
                'value' => $query_vars['mci_investment_order'],
            );
        }

        if ( ! empty( $query_vars['_customer_user'] ) ) {
            $query['meta_query'][] = array(
                'key' => '_customer_user',
                'value' => $query_vars['_customer_user'],
            );
        }

        if ( ! empty( $query_vars['mci_order_number'] ) ) {
            $query['p'] = $query_vars['mci_order_number'];
        }

        if ( ! empty( $query_vars['mci_paid_times'] ) ) {
            $query['meta_query'][] = array(
                'key' => 'mci_payment_number',
                'value' => $query_vars['mci_paid_times'],
            );
        }

        if ( ! empty( $query_vars['mci_next_profit'] ) ) {
            $query['meta_query'][] = array(
                'key' => 'mci_next_profit',
                'value' => $query_vars['mci_next_profit'],
            );
        }

        if ( ! empty( $query_vars['mci_next_payment_start'] ) && ! empty( $query_vars['mci_next_payment_end'] ) ) {
            $query['meta_query'][] = array(
                'key' => 'mci_next_payment',
                'value' => array( $query_vars['mci_next_payment_start'], $query_vars['mci_next_payment_end'] ),
                'compare'   => 'BETWEEN'
            );
        }

        return $query;
    }

    /**
     * Get mci investment order notes
     */
    public function mci_get_order_notes() {

        add_thickbox();
        $notes = wc_get_order_notes( array(
            'order_id' => $_POST['order_id']
        ) );
        
        $html = '';
        if( $notes ) {
            foreach( $notes as $note ) {
                $html .= '<p class="mci_log_data mci_log_message_box">'.$note->content.'</p>';
            }
        }
        echo $html;
        wp_die();
    }

    /**
     * Pay now button ajax
     */
    public function mci_ajax_order_pay_now() { 
        $order_id = isset( $_POST['mci_paynow_order_id'] ) ? (int)$_POST['mci_paynow_order_id'] : '';
        $user_id = isset( $_POST['mci_paynow_user_id'] ) ? (int)$_POST['mci_paynow_user_id'] : '';
        $payment_number = $order_id ? ( int ) get_post_meta( $order_id, 'mci_payment_number', true ) - 1 : 1;
        $updated_amount = isset( $_POST['mci_pay_amount'][$payment_number] ) ? $_POST['mci_pay_amount'][$payment_number] : false;
        
        /**
         * Award profit
         */
        if( method_exists( 'MCI', 'mci_award_investor_profit' ) ) {

            $mci = new MCI();
            
            /**
             * Update next profit and payment date
             */
            $mci->mci_award_investor_profit( $order_id, $user_id, $updated_amount );
        }

        wp_die();
    }

    /**
     * user investment record shortcode
     */
    public function mci_user_investment_record_table() {
        ob_start();
        ?>
        <?php
        $orders = wc_get_orders( array(
            'meta_key'   => 'mci_investment_order',
            'meta_value' => get_current_user_ID(),
            'limit' => -1
        ) );
        
        ?>
        <form method="post" class="mci_sc_container">
            <table class="mci_sc_table">
                <thead>
                    <tr>
                        <th><?php _e( 'Date', MCI) ?></th>
                        <th><?php _e( 'Order number', MCI) ?></th>
                        <th><?php _e( 'Payment number', MCI) ?></th>
                        <th><?php _e( 'Amount', MCI) ?></th>
                        <th><?php _e( 'Status', MCI) ?></th>
                    </tr>
                </thead>
                <?php
                if( ! $orders ) {
                    return;
                }
                foreach( $orders as $order ) {

                    $pay_times = get_post_meta( $order->id, 'mci_pay_times', true );
                    $profit_dates = '';
                    $profit_amount = '';
                    $payment_number = '';
                    $investment_status_html = '';
                    
                    for ( $x = 1; $x <= $pay_times; $x++ ) {

                        /**
                         * Get next payment date
                         */
                        $meta_key = 'mci_next_payment_' . $x;
                        $date = get_post_meta( $order->id, $meta_key, true );

                        /**
                         * Get next Profit amount
                         */
                        $amount_meta_key = 'mci_next_profit_' . $x;
                        $amount = get_post_meta( $order->id, $amount_meta_key, true );

                        $profit_dates .= '<p>'.$date.'</p>';
                        $profit_amount .= '<p>'.$amount.'</p>';
                        $payment_number .= '<p>'.$x.'</p>';
                        $mci_paid_times = ( int ) get_post_meta( $order->id, 'mci_paid_times', true );
                        
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

                    $mci_payment_number = ( int )get_post_meta( $order->id, 'mci_payment_number', true );
                    if( $mci_payment_number > $pay_times ) {
                        $mci_payment_number = '-';
                    }
                    $paid = 'paid';
                    if( $order->status == 'completed' ) {
                        $order->status = 'paid';
                    }
                    ?>
                    <tbody>
                        <tr>
                            <td><?php echo $profit_dates; ?></td>
                            <td><?php echo $order->id; ?></td>
                            <td><?php echo $payment_number; ?></td>
                            <td><?php echo $profit_amount; ?></td>
                            <td><?php echo $investment_status_html; ?></td>
                        </tr>
                    </tbody>
                    <?php
                }
                ?>
            </table>
        </form>
        <?php return ob_get_clean();
    }


    /**
     * Update Order status ajax
     */
    public function mci_ajax_order_status_cancalled() {
        $can_order_id = isset( $_POST['mci_can_order_id'] ) ? (int)$_POST['mci_can_order_id'] : '';

        if( is_int( $can_order_id ) ) {
            $order = new WC_Order( $can_order_id );
            if ( ! empty($order) ) {
                $order->update_status( 'cancelled' );
            }
        }
        wp_die();
    }

    /**
     * Update Order date and amount ajax
     */
    public function mci_ajax_form_data_request() {

        $order_id = isset( $_POST['mci_order_id'] ) ? $_POST['mci_order_id'] : '';
        
        if( isset( $_POST['mci_date_values'] ) && isset( $_POST['mci_amount_values'] )  ) {
            foreach( $_POST['mci_date_values'] as $index => $date_value ) {
                $amount_values = $_POST['mci_amount_values'][$index];

                /**
                 * update amount value
                 */
                $payment_number = $index + 1;
                $amount_meta_key = 'mci_next_profit_' . $payment_number;

                $order = wc_get_order( $order_id );

                /**
                 * Add amounts notes
                 */
                $first_profit_amount = get_post_meta( $order_id, $amount_meta_key, true );
                $order->add_order_note( 'Change Profit '.$payment_number.' Amount From '.$first_profit_amount.' to '.$amount_values );

                update_post_meta( $order_id, $amount_meta_key, $amount_values );

                /**
                 * update date 
                 */
                $new_date = $index + 1;
                $date_meta_key = 'mci_next_payment_' . $new_date;

                /**
                 * Add dates notes
                 */
                $first_payment_date = get_post_meta( $order_id, $date_meta_key, true );
                $order->add_order_note( 'Change Payment '.$new_date.' Date From '.$first_payment_date.' to '.$date_value );

                update_post_meta( $order_id, $date_meta_key, $date_value );
            
            }   
        }
        wp_die();
    }

    /**
     * Add a new submenu in myCred investment
     */
    public function mci_payment_order_new_menu() {
    	add_submenu_page( 'mci-stats', __( 'Payment Orders', MCI ), __( 'Payment Orders', MCI ), 'manage_options', 'mci-pm-order', [ $this, 'mci_investment_payment_orders' ] );
    }

    /**
     * call back function of mci-stats sub-menu
     */
    public function mci_investment_payment_orders() {

    	ob_start();
        if( file_exists( MCI_INCLUDES_DIR.'mci-table-content/mci-table-content.php' ) ) {
            include_once MCI_INCLUDES_DIR.'mci-table-content/mci-table-content.php';
        }

        $template = ob_get_contents();
        ob_end_clean();

        echo $template;
    }

    /**
     * Add pop up window using thick box
     */
    public function mci_pop_up_edit_window() {
    	add_thickbox();
        ?>
    	<div id="mci_edit_data" style="display: none; margin-left: 25%;">
    		<form method="post" action="">
    			<div class="mci_thick_container">
    				<p class="thick_user_id">
    					<label for="user_id" class="thick_form_lable"> <?php _e( 'User ID :', MCI ); ?> </label>
    					<span class="mci_user_id_thick thick_form_text"></span>
    				</p>
    				<p class="thick_user_id">
    					<label for="user_name" class="thick_form_lable"> <?php _e( 'Name :', MCI ); ?> </label>
    					<span class="mci_name_thick thick_form_text"></span>
    				</p>
                    <div id="mci_profit_times"></div>
    				<p class="thick_user_id">
    					<label for="order_number" class="thick_form_lable"> <?php _e( 'Order number :', MCI ); ?> </label>
    					<span name="thick_order_id" class="mci_order_number_thick thick_form_text"></span> 
    				</p>
    				<p class="thick_user_id">
    					<label for="payment_number" class="thick_form_lable"> <?php _e( 'Payment number :', MCI ); ?> </label>
    					<span class="mci_payment_number_thick thick_form_text"></span>
    				</p>
    				<p class="thick_user_id">
    					<label for="status" class="thick_form_lable"> <?php _e( 'Status :', MCI ); ?> </label>
    					<span class="mci_status_thick thick_form_text"></span>
    				</p>
    				<p>
                        <span class="cancel_button"><input type="button" value="<?php _e( 'Update', MCI ); ?>" name="mci_update_value" class="button button-primary mci_data_update_thick" /></span>
                        <span class="cancel_button"><input type="button" value="<?php _e( 'Cancel Investment', MCI ); ?>" can-order_id="" name="mci_order_cancelled" class="button button-primary mci_status_cancel" /></span>
                        <span class="cancel_button"><input type="button" value="<?php _e( 'Pay Now', MCI ); ?>" data-pay_now="" name="mci_pay_now" class="button button-primary mci_pay_now" /></span>
                    </p>
                </div>
            </form>
        </div>
        <?php
        ?>
        <div id="mci_log_thick_box" style="display: none; margin-left: 25%;">
            <span class="thick_content"></span>
        </div>
        <?php
    }

    /**
     * Include styles files admin panel
     */
    public function mci_payment_order_include_js_file() {
        wp_enqueue_style( 'mci-payment-order-css', MCI_ASSETS_URL . 'css/mci-payment-order.css', [], MCI::VERSION, null );
        wp_enqueue_script( 'mci-payment-order-js', MCI_ASSETS_URL . 'js/payment-order.js', ['jquery'], MCI::VERSION, true );

        wp_localize_script( 'mci-payment-order-js', 'PAY_ORDERS', array(
            'ajaxURL' => admin_url( 'admin-ajax.php' )
        ) );
    }

    /**
     * Include styles files frondend
     */
    public function mci_include_css_file_front() {
        wp_enqueue_style( 'mci-shortcode-table-css', MCI_ASSETS_URL . 'css/mci-shortcode-table.css', [], MCI::VERSION, null );
    }
}

/**
 * class instance
 */
Mci_Payment_Orders::instance();

