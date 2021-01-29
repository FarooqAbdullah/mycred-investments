<?php
/**
 * Plugin Name: myCred Investments
 * Version: 1.0
 * Description: This addon allows to create investments products.
 * Author: Farooq Abdullah
 * Author URI: https://www.fiverr.com/farooq14162
 * Plugin URI: https://www.fiverr.com/farooq14162
 * Text Domain: mycred_investments
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MCI
 */
class MCI {

    const VERSION = '1.0';

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof MCI ) ) {
            self::$instance = new self;

            self::$instance->setup_constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Upgrade function hook
     *
     * @since 1.0
     * @return void
     */
    public function upgrade() {
        if( get_option ( 'mci_version' ) != self::VERSION ) {
        }
    }

    /**
     * defining constants for plugin
     */
    public function setup_constants() {

        // Directory
        define( 'MCI_DIR', plugin_dir_path ( __FILE__ ) );
        define( 'MCI_DIR_FILE', MCI_DIR . basename ( __FILE__ ) );
        define( 'MCI_INCLUDES_DIR', trailingslashit ( MCI_DIR . 'includes' ) );
        define( 'MCI_TEMPLATES_DIR', trailingslashit ( MCI_DIR . 'templates' ) );
        define( 'MCI_BASE_DIR', plugin_basename(__FILE__));

        // URLS
        define( 'MCI_URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
        define( 'MCI_ASSETS_URL', trailingslashit ( MCI_URL . 'assets/' ) );

        // Text Domain
        define( 'MCI', 'mycred_investments' );
    }
    
    /**
     * Includes
     */
    public function includes() {
    	if( file_exists( MCI_INCLUDES_DIR . 'payment-orders.php' ) ) {
    		require_once MCI_INCLUDES_DIR . 'payment-orders.php';
    	}
    	if( file_exists( MCI_INCLUDES_DIR . 'woo-withdrawal-methods.php' ) ) {
    		require_once MCI_INCLUDES_DIR . 'woo-withdrawal-methods.php';
    	}
    }

    /**
     * Plugin Hooks
     */
    public function hooks() {
    	add_action( 'admin_enqueue_scripts', [ $this, 'mci_admin_scripts' ] );
    	add_action( 'wp_enqueue_scripts', [ $this, 'mci_theme_scripts' ] );
    	add_filter( 'product_type_selector', [ $this, 'mci_create_investment_type' ] );
    	add_filter( 'woocommerce_product_data_tabs', [ $this, 'mci_create_investment_options_tab' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'mci_investment_options_tab_data' ] );
		add_action( 'save_post', [ $this, 'mci_save_investment_product_type' ], 10, 3 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'mci_add_investment_product_options' ] );
		add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'mci_remove_add_to_cart_link' ], 10, 3 );
		add_action( 'wp', [ $this, 'mci_added_investment_to_cart' ] );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'mci_update_cart_investment_price' ] );
		add_action( 'woocommerce_new_order', [ $this, 'mci_save_investment_product_meta' ], 10, 2 );
		add_filter( 'woocommerce_is_purchasable', [ $this, 'mci_update_purchase_status' ], 10, 2 );
		add_shortcode( 'mci_coins_balance', [ $this, 'mci_coins_balance_shortcode' ] );
		add_shortcode( 'mci_active_investments', [ $this, 'mci_active_investments_shortcode' ] );
		add_shortcode( 'mci_referral_earnings', [ $this, 'mci_referral_earnings_shortcode' ] );
		add_shortcode( 'mci_earning_chart', [ $this, 'mci_earning_chart_shortcode' ] );
		add_shortcode( 'mci_earning_investments', [ $this, 'mci_earning_investments_shortcode' ] );
		add_shortcode( 'mci_invested', [ $this, 'mci_earning_invested_shortcode' ] );
		add_action( 'admin_menu', [ $this, 'mci_admin_menu' ] );
		add_action( 'wp_ajax_mci_get_investment_data', [ $this, 'mci_get_investment_data' ] );
		add_action( 'woocommerce_order_status_changed', [ $this, 'mci_start_cron_schedule' ], 10, 4 );
		add_action( 'woocommerce_order_status_changed', [ $this, 'mci_cancel_cron_schedule' ], 10, 4 );
		add_action( 'wp_ajax_mci_get_matching_user', [ $this, 'mci_get_matching_user' ] );
		add_action( 'wp_ajax_mci_get_investment_data_specific_user', [ $this, 'mci_get_investment_data_specific_user' ] );
		add_action( 'admin_post_mci_submit_action', [ $this, 'mci_save_settings' ] );
		add_action( 'mci_award_investment_profit', [ $this, 'mci_award_user_investment_profit' ] );
		add_filter( 'woocommerce_product_needs_shipping', [ $this, 'mci_exlude_shipping' ], 10, 2 );
		add_action( 'add_meta_boxes', [ $this, 'mci_award_investment_metabox' ], 10, 2 );
		add_action( 'wp_ajax_mci_award_investment_by_button', [ $this, 'mci_award_investment_by_button' ] );
		add_action( 'init', [ $this, 'mci_load_plugin_text_domain' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'mci_update_order_status_on_thankyou' ] );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'mci_update_order_status' ] );
		add_filter( 'mycred_new_log_entry_id', [ $this, 'mci_remove_wc_buy_log' ], 10, 3 );
		add_filter( 'bp_get_the_notification_description', [ $this, 'mci_hide_empty_notification' ], 10, 2 );
		add_filter( 'woocommerce_payment_complete_order_status', [ $this, 'woocommerce_payment_complete_order_status' ], 10, 3 );
		add_action( 'woocommerce_order_status_changed', [ $this, 'mci_award_coins_on_hold_status' ], 10, 4 );
		add_action( 'woocommerce_add_to_cart_validation', [ $this, 'mci_restrict_adding_product' ], 10, 5 );
		add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'mci_display_cart_product_error' ] );
    	add_action( 'woocommerce_before_checkout_form', [ $this, 'mci_display_cart_items_removed_message' ] );
    	add_filter( 'woo_wallet_withdrawal_payment_gateways', [ $this, 'mci_woo_withdrawal_gateways' ] );
    	add_action( 'init', [ $this, 'mci_generate_csv' ] );
    }

    /**
     * Generate withdrawal CSV
     */
    public function mci_generate_csv() {

    	if( ! isset( $_GET['mci_dw'] ) ) return;

    	global $wpdb;
		$wd_posts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'wallet_withdrawal'" );    		
    	if( $wd_posts && count( $wd_posts ) > 0 ) {
    		ob_start();
	        header('Content-type: html/csv');
	        header('Content-Disposition: attachment; filename="withdraw-' . date('d-m-y') . '.csv"');
	        echo 'User Email'.',';
	        echo 'Amount'.',';
	        echo 'Gateway Charge'.',';
	        echo 'Status'.',';
	        echo 'Method'.',';
	        echo 'Date'.','."\n";
	        foreach( $wd_posts as $wd_post ) {

	        	if( $wd_post->post_status == 'auto-draft' ) continue;

	        	$email = get_userdata( $wd_post->post_author )->user_email;
	            $amount = get_post_meta( $wd_post->ID, '_wallet_withdrawal_amount', true) ? get_post_meta( $wd_post->ID, '_wallet_withdrawal_amount', true) : 0;
	            $currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
	            $charge = get_post_meta( $wd_post->ID, '_wallet_withdrawal_transaction_charge', true ) ? get_post_meta( $wd_post->ID, '_wallet_withdrawal_transaction_charge', true ) : 0;
	            $status = $wd_post->post_status;
	            $method = get_post_meta( $wd_post->ID, '_wallet_withdrawal_method', true ) ? get_post_meta( $wd_post->ID, '_wallet_withdrawal_method', true ) : 0;
	            $date = $wd_post->post_date_gmt;
	            if( $status == 'ww-pending' ) {
	            	$status = 'Pending';
	            } elseif( $status == 'ww-approved' ) {
	            	$status = 'Approved';
	            } elseif( $status == 'ww-cancelled' ) {
	            	$status = 'Cancelled';
	            }

	            if( function_exists( 'woo_wallet_withdrawal' ) && isset( woo_wallet_withdrawal()->gateways->payment_gateways[$method] ) ) {
	            	$method = woo_wallet_withdrawal()->gateways->payment_gateways[$method]->method_title;
	            } 

	            echo esc_html( $email ) . ',';
	            echo esc_html( $amount ) . ',';
	            echo esc_html( $charge ) . ',';
	            echo esc_html( $status ) . ',';
	            echo esc_html( $method ) . ',';
	            echo esc_html( $date ) . ',' . "\n";
	        }
        	die();
    	}
    }

    /**
     * Add custom withdrawal gateways to Woo withdrawal
     */
    function mci_woo_withdrawal_gateways( $gateways ) {
		$gateways[] = 'American_Express';
		$gateways[] = 'Bitcoin';
		return $gateways;
	}

    /**
     * Display message if cart items removed and added investment product
     */
    public function mci_display_cart_items_removed_message() { 
    	if( isset( $_COOKIE['mci_removed_cart_items'] ) ) {
			$opts = get_option( 'mci_opts' );
			$items_remove_msg = isset( $opts['items_remove_msg'] ) ? $opts['items_remove_msg'] : '';
	    ?>
	    	<div class="woocommerce-error"><?php _e( $items_remove_msg, MCI ); ?></div>
	    <?php
	    	/**
	    	 * Remove cookie
	    	 */
	    	setcookie( 'mci_removed_cart_items', 'true', time() - 3600, '/' );
    	}
    }

    /**
     * Display error on product detail page
     */
    public function mci_display_cart_product_error() {
        if( get_post_meta( get_the_ID(), 'mci_investment_product', true ) != 'true' ) {
            foreach( WC()->cart->get_cart() as $key => $item ) {
                $cart_product_id = $item['product_id'];
                if( get_post_meta( $cart_product_id, 'mci_investment_product', true ) == 'true' ) {
                    echo '<div style="padding: 5px;background-color: #b22222;color:#fff;">Cant add product</div>';
                }
            }
        }
    }

    /**
     * Restrict adding product
     *
     * @param $passed
     * @param $product_id
     * @param $quantity
     * @param int $variation_id
     * @param int $variations
     * @return bool
     */
    public function mci_restrict_adding_product( $passed, $product_id, $quantity, $variation_id = 0, $variations = 0 ) {

        foreach( WC()->cart->get_cart() as $key => $item ) {
           $cart_product_id = $item['product_id'];
            if( get_post_meta( $cart_product_id, 'mci_investment_product', true ) == 'true' ) {
               return false;
           }
        }

        return $passed;
    }

	/**
	 * Award coins on-hold status
	 */
	public function mci_award_coins_on_hold_status( $order_id, $from_status, $to_status, $order ) {

		$is_investment = get_post_meta( $order_id, 'mci_investment_order', true );

		if( $is_investment && $to_status == 'on-hold' && $order->get_payment_method() != 'bacs' && $order->get_payment_method() != 'coinbase' ) {
			$coins = 0;
			foreach( $order->get_items() as $item ) {
					$coins += $item->get_total();
			}
			$customer_id = get_post_meta( $order_id, '_customer_user', true );
			mycred_add( __( 'Compra de Coins #' . $order_id, MCI ), $customer_id, $coins, __( 'Compra de Coins #' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );
		}
	}

	/**
	 * Filter order status if investment order payment is completed
	 */
	public function woocommerce_payment_complete_order_status( $status, $order_id, $order ) {

		/**
		 * Filter order status for paypal, coinbase
		 */
		$order = new WC_Order( $order_id );
		if( $order->get_payment_method() == 'paypal' || $order->get_payment_method() == 'ppec_paypal' || $order->get_payment_method() == 'coinbase' || $order->get_payment_method() == 'mycred' || $order->get_payment_method() == 'wallet' ) {
			$is_investment = get_post_meta( $order_id, 'mci_investment_order', true );
			if( $is_investment ) {
				$status = 'on-hold';	
			}
		}

		return $status;
	}

	/**
	 * Hide buddyboss empty notifications
	 */
	public function mci_hide_empty_notification( $description, $notif ) {

		if( strlen( $description ) <= 24 ) { ?>
			<script>
				( function( $ ) {
					$( 'input[value="<?php echo $notif->id; ?>"]' ).parents( 'li' ).hide();
				} )( jQuery );
				</script>	
		<?php
		}
		return $description;
	}

	/**
	 * Delete Order notes created on order creation
	 */
	public function mci_delete_order_notes( $order_id ) {
		$notes = wc_get_order_notes( array( 
			'order_id' => $order_id
		) );
		

		if( $notes  ) {
			foreach( $notes as $note ) {
				wp_delete_comment( $note->id );
			}
		}
	}

	/**
	 * Remove mycred log created for woocommerce purchase by mycred 
	 */
	public function mci_remove_wc_buy_log( $insert_id, $insert, $data ) {

		if( $insert['ref'] == 'woocommerce_payment' && get_post_meta( $insert['ref_id'], 'mci_investment_order', true ) ) {
			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->prefix}myCRED_log WHERE id = {$insert_id}" );
		}

		return $insert_id;
	} 


	/**
	 * Shortcode to show invested earnings of users
	 */
	public function mci_earning_invested_shortcode( $atts ) {
		ob_start();

		if( ! is_user_logged_in() ) return;

		$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();

		$orders = wc_get_orders( array(
		    'meta_key' 		=> 'mci_investment_order',
		    'meta_value' 	=> $user_id,
		    'numberposts' => -1
		) );
		$amount = 0;
		if( $orders ) {
		    foreach( $orders as $order ) { 
		     	if( $order->get_status() == 'completed' || $order->get_status() == 'processing' ) {
			      	$amount += get_post_meta( $order->get_id(), 'mci_prd_price', true );
				}
		    }
		} else {
			$amount = 0;
		}

		if( is_admin() ) {
			echo str_replace( ',', '', $amount ); 
		} else {
			echo number_format( $amount, 2 );	
		}
		
		return ob_get_clean();
	}


	/**
	 * Helper function to get 5 months date for chart data
	 */
	public function get_5_months_date() {
		$date = date( 'Y-m-31 00:00:00' );
		$date2 = '';
			
		$months = array(
			'0'  => '12',
			'-1' => '11',
			'-2' => '10',
			'-3' => '09' 
		);

		$curr_month = date('m');
		$prev_year = 0;
		$last_month = 0;
		for( $x = 0; $x < 5; $x++ ) {
			
			/**
			 * Get curr/prev month
			 */
			$temp_month = 0;
			if( $curr_month - $x > 0 ) {
				$temp_month = $curr_month - $x;
				$temp_month = strlen( $temp_month ) < 2 ? '0'.$temp_month : $temp_month;
				$all_months[] = $curr_month - $x;
			} else {
				$temp_month = $months[$curr_month - $x];
				$prev_year =  ( int ) date( 'Y' ) - 1; 
				$all_months[] = $curr_month - $x;
			}
			$last_month = $temp_month;
		}

		if( $prev_year == '' ) {
			$prev_year = date('Y');
		}
		$last_month = strlen( $last_month ) < 2 ? '0' . $last_month : $last_month;
		$date2 = $prev_year . '-' . $last_month . '-01 00:00:00';

		return array(
			'date1' 	=> $date2, 
			'date2' 	=> $date,
			'months' 	=> $all_months
		);
	}

	/**
	 * Get chart logs data
	 */
	public function mci_get_log_data( $user_id ) {
		
		$dates = $this->get_5_months_date();

		$user_id_filter = [];

		$data = [];
		$all_months = [];
		$args = array(
			'time' => array(
				'dates'   => array( $dates['date1'], $dates['date2'] ),
				'compare' => 'BETWEEN'
			),
			'number'  => -1
		);
		$prev_months = array(
			'0'  => '12',
			'-1' => '11',
			'-2' => '10',
			'-3' => '09' 
		);
		if( ! empty( $user_id ) ) {
			$args = [ 'user_id' => $user_id ];
		}

		$log  = new myCRED_Query_Log( $args );

		$data = [];
		if ( $log->have_entries() ) {
				
			foreach( $log->results as $log_entry ) {
				$log_month = date( 'm', $log_entry->time );
				$data[$log_month] = isset( $data[$log_month] ) ? ( float ) $data[$log_month] + ( float ) $log_entry->creds : ( float ) $log_entry->creds;
			}
		}
		
		if( $dates['months'] ) {
			foreach( $dates['months'] as $month ) {
				$month = $month > 0 ? $month : $prev_months[$month];
				$month = strlen( $month ) < 2 ? '0'.$month : $month;
				if( ! isset( $data[$month] ) ) {
					$month = strlen( $month ) < 2 ? '0'.$month : $month;
					$data[$month] = 0;
				}
			}
		}

		return $data;
	}



	/**
	 * Get chart investment data
	 */
	public function mci_get_investment_chart_data( $user_id ) {

		$dates = $this->get_5_months_date();
		$data = [];
		$all_months = [];
		$prev_months = array(
			'0'  => '12',
			'-1' => '11',
			'-2' => '10',
			'-3' => '09' 
		);
		$args = array(
			'date_query' 	=> array(
			'after' 		=> $dates['date1'],
			'before' 		=> $dates['date2']
			),
			'numberposts' => -1
		);

		if( ! empty( $user_id ) ) {
			$args['meta_key'] = '_customer_user';
            $args['meta_value'] = $user_id;
		}

		$customer_orders = wc_get_orders( $args );

		if( $customer_orders ) {
			foreach( $customer_orders as $order ) {
				if( ! get_post_meta( $order->get_id(), 'mci_investment_order', true ) ) continue;

				$month = $order->get_date_created()->date( 'm' );
				$data['investments'][$month] = isset( $data['investments'][$month] ) ? ( float ) $data['investments'][$month] + ( float ) $this->mci_get_order_investment_amount( $order->get_id() ) : ( float ) $this->mci_get_order_investment_amount( $order->get_id() );
				
				$data['earnings'][$month] = isset( $data['earnings'][$month] ) ? ( float ) $data['earnings'][$month] + ( float ) $this->mci_get_order_investment_earning_amount( $order->get_id() ) : ( float ) $this->mci_get_order_investment_earning_amount( $order->get_id() );

				if( $order->get_status() == 'completed' || $order->get_status() == 'processing' ) {
					$data['invested'][$month] = isset( $data['invested'][$month] ) ? ( float ) $data['invested'][$month] + ( float ) get_post_meta( $order->get_id(), 'mci_prd_price', true ) : ( float ) get_post_meta( $order->get_id(), 'mci_prd_price', true );
				}
			}
		}

		$data['sorted_months'] = [];
		if( $dates['months'] ) {
			foreach( $dates['months'] as $month ) {
				$month = $month > 0 ? $month : $prev_months[$month];
				$month = strlen( $month ) < 2 ? '0'.$month : $month;
				$data['sorted_months'][] = $month;
				if( ! isset( $data['investments'][$month] ) ) {
					$data['investments'][$month] = 0;
				}
				if( ! isset( $data['earnings'][$month] ) ) {
					$data['earnings'][$month] = 0;
				}
				if( ! isset( $data['invested'][$month] ) ) {
					$data['invested'][$month] = 0;
				}
			}
		}

		/**
		 * Sort data according to if previous year months exists 
		 */
		if( in_array( 0, $dates['months'] ) ) {
			asort( $dates['months'] );
			$data['sorted_months'] = [];
			foreach( $dates['months'] as $srt_month ) {
				$month = $srt_month > 0 ? $srt_month : $prev_months[$srt_month];
				$month = strlen( $month ) < 2 ? '0'.$month : $month;
				$data['sorted_months'][] = $month;
			}
		}
		return $data;
	}

	/**
	 * Get chart referrals data
	 */
	public function mci_get_chart_data( $user_id ) {
		global $wpdb;

		$table_name = '';
		if( class_exists( 'Affiliate_WP_Referrals_DB' ) ) {
			$ref_db = new Affiliate_WP_Referrals_DB();
			$table_name = $ref_db->table_name;
		}

		$months = array(
			'0'  => '12',
			'-1' => '11',
			'-2' => '10',
			'-3' => '09' 
		);

		$curr_month = date('m');
		$data = [];
		$all_months = []; 
		
		for( $x = 0; $x < 5; $x++ ) {
			
			/**
			 * Get curr/prev month
			 */
			$temp_month = 0;
			if( $curr_month - $x > 0 ) {
				$temp_month = $curr_month - $x;

				$temp_month = strlen( $temp_month ) < 2 ? '0'.$temp_month : $temp_month;
			} else {
				$temp_month = $months[$curr_month - $x];
			}

			/**
			 * Get curr/prev month referrals
			 */
			if( ! empty( $user_id ) && affwp_is_affiliate( $user_id ) ) {
				$referrals = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE date LIKE '%-{$temp_month}-%' AND affiliate_id = '".affwp_get_affiliate_id( $user_id )."'" );
			} elseif( ! empty( $user_id ) && ! affwp_is_affiliate( $user_id ) ) {
				$referrals = [];
			}

			if( empty( $user_id ) ) {
				$referrals = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE date LIKE '%-{$temp_month}-%'" );	
			}
			
			if( $referrals ) {
				foreach( $referrals as $referral ) {

					$all_months[] = date( 'm', strtotime( $referral->date ) );

					/**
					 * Check investment referrals
					 */
					if( get_post_meta( $referral->reference, 'mci_investment_order', true ) && $referral->status == 'paid' ) {
						$data['investment_referrals'][$temp_month] = isset( $data['investment_referrals'][$temp_month] ) ? ( float ) $data['investment_referrals'][$temp_month] + ( float ) $referral->amount : ( float ) $referral->amount;
					}

					/**
					 * Check other referrals
					 */
					if( /*! get_post_meta( $referral->reference, 'mci_investment_order', true ) &&*/ $referral->status == 'paid' ) {
						$data['other_referrals'][$temp_month] = isset( $data['other_referrals'][$temp_month] ) ? ( float ) $data['other_referrals'][$temp_month] + ( float ) $referral->amount : ( float ) $referral->amount;
					}
				}
			} else {
				$data['investment_referrals'][$temp_month] = 0;
				$data['other_referrals'][$temp_month] = 0;
			}
		} 

		if( $all_months ) {
			foreach( $all_months as $month ) {
				if( ! isset( $data['investment_referrals'][$month] ) ) {
					$month = strlen( $month ) < 2 ? '0'.$month : $month;
					$data['investment_referrals'][$month] = 0;
				}
				if( ! isset( $data['other_referrals'][$month] ) ) {
					$month = strlen( $month ) < 2 ? '0'.$month : $month;
					$data['other_referrals'][$month] = 0;
				}
			}
		}

		$sp_user = ! empty( $user_id ) ? $user_id : null;

		$data['logs'] = $this->mci_get_log_data( $sp_user );
		$inv_chart_data = $this->mci_get_investment_chart_data( $sp_user );
		$data['investments'] = $inv_chart_data['investments'];
		$data['earnings'] = $inv_chart_data['earnings'];
		$data['invested'] = $inv_chart_data['invested'];
		$data['sorted_months'] = $inv_chart_data['sorted_months'];

		return $data;
	}

	/**
	 * Get order earning amount 
	 */
	public function mci_get_order_investment_earning_amount( $order_id ) {
		// global $wpdb;
		
		$amount = 0;

		if( get_post_status( $order_id ) == 'wc-completed' || get_post_status( $order_id ) == 'wc-processing' ) {
			$profit = get_post_meta( $order_id, 'mci_profit_awarded', true ) ? ( float ) get_post_meta( $order_id, 'mci_profit_awarded', true ) : 0;
			$amount = $profit; 
		}
		return $amount;
	}

	/**
	 * Get order investment amount 
	 */
	public function mci_get_order_investment_amount( $order_id ) {
		$amount = 0;
		if( get_post_status( $order_id ) == 'wc-processing' ) {
			$amount = get_post_meta( $order_id, 'mci_prd_price', true );
		}
		return $amount;
	}

	/**
	 * Load plugin text domain
	 */
	public function mci_load_plugin_text_domain() {
		load_plugin_textdomain( 'mycred_investments', false, dirname( plugin_basename( __FILE__ ) ) );
	}

	/**
	 * Calculate investment profit
	 */
	public function mci_calculate_profit( $order_id, $prd_id, $paid_times, $last_profit_payment ) {
		global $wpdb;
		$prd_price = get_post_meta( $order_id, 'mci_prd_price', true );
					
		if( ! $prd_id || ! $prd_price ) return;
						
		$i = 0;
						
		$referrals = [];
		if( affwp_is_affiliate( $user->ID ) ) {
			$aff_id = affwp_get_affiliate_id( $user->ID );
			$referrals = affiliate_wp()->referrals->get_referrals( array(
				'affiliate_id' => $aff_id,
				'number' 		=> -1,
				'status'		=> 'paid'
			), false );
		}

		if( $referrals && count( $referrals ) > 10000 && count( $referrals ) < 20000 ) {
			$i = 0.3;
		} elseif( $referrals && count( $referrals ) > 20000 && count( $referrals ) < 200000 ) {
			$i = 0.5;
		} elseif( $referrals && count( $referrals ) > 200000 && count( $referrals ) < 800000 ) {
			$i = 0.5;
		}

		$base_interest = get_post_meta( $order_id, 'mci_prd_interest_rate', true ) ? ( float ) get_post_meta( $order_id, 'mci_prd_interest_rate', true ) : 0;
		$profit1 = ( ( $base_interest + $i ) / 100 ) * ( float ) $prd_price;
		$term = get_post_meta( $order_id, 'mci_term', true ) ? get_post_meta( $order_id, 'mci_term', true ) : 1;
		$term_type = get_post_meta( $order_id, 'mci_term_type', true ) ? get_post_meta( $order_id, 'mci_term_type', true ) : 'monthly';
		$wd_type = get_post_meta( $order_id, 'mci_prd_wd', true ) ? get_post_meta( $order_id, 'mci_prd_wd', true ) : 'at the end';
		$pay_times = get_post_meta( $order_id, 'mci_pay_times' , true ) ? ( int ) get_post_meta( $order_id, 'mci_pay_times' , true ) : 1;

		$profit = 0;

		/**
		 * Apply compound interest
		 */
		$interest_type = get_post_meta( $order_id, 'mci_prd_interest_type', true );
		$p_times = 0;

		if( ! $last_profit_payment ) {
			$last_comp_pay = get_post_meta( $order_id, 'mci_compound_last_pay' , true ) ? get_post_meta( $order_id, 'mci_compound_last_pay' , true ) : 0;
		} else {
			$last_comp_pay = $last_profit_payment;
		}
		
		
		if( $wd_type == 'monthly' ) {
						
			if( $interest_type == 'compound' && $paid_times > 1 ) {

				if( $last_comp_pay ) {
					$inv = ( float ) $prd_price + ( float ) $last_comp_pay;
					$profit1 = ( ( $base_interest + $i ) / 100 ) * $inv;
				}

				$profit = $profit1;
			} else {
				$profit = $profit1;
			}

		} elseif( $wd_type != 'monthly' ) {

			$last_pay_temp = 0;
			if( $term_type == 'monthly' && $term > 1 ) {
							
				for( $x = 1; $x <= $term; $x++ ) {

					if( $interest_type == 'compound' ) {

						if( $p_times > 0 ) {
							$inv = ( float ) $prd_price + ( float ) $last_pay_temp;
							$profit1 = ( ( $base_interest + $i ) / 100 ) * $inv;
							$profit += $profit1;
						} else {
							$profit += $profit1;
						}
						$p_times++;
					} else {
						/**
						 * Simple interest
						 */
						$profit += $profit1;
					}
						$last_pay_temp = $profit;
					}

			} elseif( $term_type == 'yearly' ) {
							
				$term_temp = $term * 12; 
				for( $x = 1; $x <= $term_temp; $x++ ) {
					if( $interest_type == 'compound' ) {

					/**
					 * Compound interest
					 */
					if( $p_times > 0 ) {
						$inv = ( float ) $prd_price + ( float ) $last_pay_temp;
						$profit1 = ( ( $base_interest + $i ) / 100 ) * $inv;
						// $prof_temp = $profit1;
						$profit += $profit1;
					} else {
						$profit += $profit1;
					}

					$p_times++;

					} else {
						/**
					  	 * Simple interest
					  	 */
						$profit += $profit1;
					}

					$last_pay_temp = $profit;
				}
			}
		}
	
		return $profit;
	}

	/**
	 * Update next profit amount and date on order meta
	 */
	public function mci_update_next_profit_meta( $order_id, $paid_time ) {
		if( ! $order_id || ! $paid_time  ) return;
		$customer_id = get_post_meta( $order_id, '_customer_user', true );
		$customer = new WC_Customer( $customer_id );
		$user = get_userdata( $customer->get_id() );
		$prd_price = get_post_meta( $order_id, 'mci_prd_price', true );
		$prd_id = get_post_meta( $order_id, 'mci_prd_id', true );
		$pay_times = get_post_meta( $order_id, 'mci_pay_times' , true ) ? ( int ) get_post_meta( $order_id, 'mci_pay_times' , true ) : 1;
		$term = get_post_meta( $order_id, 'mci_term', true ) ? get_post_meta( $order_id, 'mci_term', true ) : 1;
		$term_type = get_post_meta( $order_id, 'mci_term_type', true ) ? get_post_meta( $order_id, 'mci_term_type', true ) : 'monthly';
		$wd_type = get_post_meta( $order_id, 'mci_prd_wd', true ) ? get_post_meta( $order_id, 'mci_prd_wd', true ) : 'at the end';
		$interest_type = get_post_meta( $order_id, 'mci_prd_interest_type', true );
		$profit = 0;

		/**
		 * Get withdraw option for product
		 */
		$prd_term_days = get_post_meta( $order_id, 'mci_prd_wd', true );
		$term = get_post_meta( $order_id, 'mci_term', true );
		$term_type = get_post_meta( $order_id, 'mci_term_type', true );
		$at_the_end = $term_type == 'monthly' ? $term * 30 : $term * 365;

		/**
		 * Create profit award schedule
		 */ 
		$prd_term_days = $prd_term_days == 'monthly' ? 30 : $at_the_end;
		$curr_date = get_post_meta( $order_id, 'mci_next_payment_'.( $paid_time - 1 ), true ) ? get_post_meta( $order_id, 'mci_next_payment_'.( $paid_time - 1 ), true ) : date( 'Y-m-d' );
		update_post_meta( $order_id, 'mci_next_payment_'.$paid_time, date( 'Y-m-d', strtotime( '+'.$prd_term_days.' days', strtotime( $curr_date ) ) ) );
		$profit_amount = $this->mci_calculate_profit( $order_id, $prd_id, $paid_time, false );
		update_post_meta( $order_id, 'mci_next_profit_'.$paid_time, number_format( $profit_amount, 2 ) );

		if( $interest_type == 'compound' ) {
			$last_profit = get_post_meta( $order_id, 'mci_compound_last_pay' , true ) ? ( float ) get_post_meta( $order_id, 'mci_compound_last_pay' , true ) : 0;
			$last_profit = ( float ) $last_profit + $profit_amount;
			update_post_meta( $order_id, 'mci_compound_last_pay', $last_profit );
		}
	}

	/**
	 * Update next profit amount and date on order meta
	 */
	public function mci_update_next_profit( $order_id, $prd_id ) {
		if( ! $order_id || ! $prd_id  ) return;
	
		$customer_id = get_post_meta( $order_id, '_customer_user', true );
		$customer = new WC_Customer( $customer_id );
		$user = get_userdata( $customer->get_id() );
		$prd_price = get_post_meta( $order_id, 'mci_prd_price', true );
		$paid_times = get_post_meta( $order_id, 'mci_paid_times' , true ) ? ( int ) get_post_meta( $order_id, 'mci_paid_times', true ) + 1 : 1;
		$pay_times = get_post_meta( $order_id, 'mci_pay_times' , true ) ? ( int ) get_post_meta( $order_id, 'mci_pay_times' , true ) : 1;
		$term = get_post_meta( $order_id, 'mci_term', true ) ? get_post_meta( $order_id, 'mci_term', true ) : 1;
		$term_type = get_post_meta( $order_id, 'mci_term_type', true ) ? get_post_meta( $order_id, 'mci_term_type', true ) : 'monthly';
		$wd_type = get_post_meta( $order_id, 'mci_prd_wd', true ) ? get_post_meta( $order_id, 'mci_prd_wd', true ) : 'at the end';
		$interest_type = get_post_meta( $order_id, 'mci_prd_interest_type', true );
		$profit = 0;
		$all_profits_awarded = false;
					
		/**
		 * Award profit by schedule
		 */
		if( $paid_times <= $pay_times ) {

			$profit = get_post_meta( $order_id, 'mci_next_profit', true ) ? ( float ) get_post_meta( $order_id, 'mci_next_profit', true ) : 0;
			mycred_add( __( 'Rendimientos de la inversión #' . $order_id, MCI ), $user->ID, $profit, __( 'Rendimientos de la inversión #' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );
						
			update_post_meta( $order_id, 'mci_paid_times', $paid_times );
			$total_profit_awarded = get_post_meta( $order_id, 'mci_profit_awarded', true ) ? ( float ) get_post_meta( $order_id, 'mci_profit_awarded', true ) : 0;
			$total_profit_awarded = $total_profit_awarded + ( float ) $profit;
			update_post_meta( $order_id, 'mci_profit_awarded', $total_profit_awarded );

			if( $interest_type == 'compound' ) {
				$last_profit = get_post_meta( $order_id, 'mci_compound_last_pay' , true ) ? ( float ) get_post_meta( $order_id, 'mci_compound_last_pay' , true ) : 0;
				$last_profit = ( float ) $last_profit + $profit;
				update_post_meta( $order_id, 'mci_compound_last_pay' , $last_profit );
			} 
		} 

		/**
		 * Remove profit paying schedule
		 */
		if( $paid_times >= $pay_times ) {
			wp_update_post( array(
				'ID' 			=> $order_id,
				'post_status' 	=> 'wc-completed'
			) );
			delete_post_meta( $order_id, 'mci_next_payment' );
			delete_post_meta( $order_id, 'mci_next_profit' );
			delete_post_meta( $order_id, 'mci_compound_last_pay' );
			mycred_add( __( 'Retorno de la inversión # ' . $order_id, MCI ), $user->ID, $prd_price, __( 'Retorno de la inversión # ' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );
			$all_profits_awarded = true;
		} else {
			/**
			 * Get withdraw option for product
			 */
			$prd_term_days = get_post_meta( $order_id, 'mci_prd_wd', true );
			$term = get_post_meta( $order_id, 'mci_term', true );
			$term_type = get_post_meta( $order_id, 'mci_term_type', true );

			$at_the_end = $term_type == 'monthly' ? $term * 30 : $term * 365;

			/**
			 * Create profit award schedule
			 */
			$prd_term_days = $prd_term_days == 'monthly' ? 30 : $at_the_end;
			$curr_date = get_post_meta( $order_id, 'mci_next_payment', true );
			$paid_times++;
			update_post_meta( $order_id, 'mci_next_payment', date( 'Y-m-d', strtotime( '+'.$prd_term_days.' days', strtotime( $curr_date ) ) ) );
			$profit_amount = $this->mci_calculate_profit( $order_id, $prd_id, $paid_times, false );
			update_post_meta( $order_id, 'mci_next_profit', number_format( $profit_amount, 2 ) );
			$last_payment_number = ( int ) get_post_meta( $order_id, 'mci_payment_number', true );
			$last_payment_number++;
			update_post_meta( $order_id, 'mci_payment_number', $last_payment_number );
		}
		return $all_profits_awarded;
	}

	/**
	 * Award investment by button
	 */
	public function mci_award_investment_by_button() {
		global $wpdb;

		$payments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '%mci_next_payment%' AND post_id = '".$_POST['order_id']."'" );
		$locked = false;
		if( $payments ) {
			foreach( $payments as $data ) {
				$order_id = $data->post_id;
				$customer_id = get_post_meta( $order_id, '_customer_user', true );

				// $all_prof_awarded = $this->mci_update_next_profit( $order_id, $prd_id );
				$all_prof_awarded = $this->mci_award_investor_profit( $order_id, $customer_id, false );
				$locked = $all_prof_awarded ? $all_prof_awarded : false;
				break;
			}
		}
		
		echo json_encode( [ 'locked' => $locked ] );

		wp_die();
	}

	/**
	 * Award investment button metabox
	 */
	public function mci_award_investment_metabox( $post_type, $post ) {
		$is_investment = get_post_meta( $post->ID, 'mci_investment_order', true );
		if( $post_type == 'shop_order' && $is_investment ) {
			add_meta_box( __( 'Investment Options', MCI ), __( 'Investment Options', MCI ), [ $this, 'mci_award_investment_profit_button' ], '', 'side' );
		}
	}

	/**
	 * Award investment button metabox HTML
	 */
	public function mci_award_investment_profit_button() {
		$order_id = isset( $_GET['post'] ) ? $_GET['post'] : '';
		$disabled = get_post_status( $order_id ) == 'wc-completed' ? 'disabled="true"' : '';
	?>
		<button class="button button-primary mci-award-investment" data-id="<?php echo $order_id; ?>" <?php echo $disabled; ?>><?php _e( 'Award Investment', MCI ); ?></button>
		<p class="mci-info-box">
			<?php if( $disabled != '' ) {
				_e( 'All profits awarded', MCI );
			} ?>
		</p>
	<?php
	}

	/**
	 * Exclude shipping from investment product
	 */
	public function mci_exlude_shipping( $cond, $product ) {
			
		$is_investment = get_post_meta( $product->get_id(), 'mci_investment_product', true );
		$excluded_shipping = get_post_meta( $product->get_id(), 'mci_exlude_shipping', true );
		
		if( $is_investment && $excluded_shipping ) {
			$cond = false;
		}

		return $cond;
	}

	/**
	 * Daily cron to return profit
	 */
	public function mci_award_user_investment_profit() {

		global $wpdb;
		$postmeta_table = $wpdb->prefix.'postmeta';
		$curr_date = date( 'Y-m-d' );
		$payments = $wpdb->get_results( "SELECT * FROM {$postmeta_table} WHERE meta_key LIKE '%mci_next_payment%' AND meta_value = '{$curr_date}'" );

		if( $payments ) {
			foreach( $payments as $data ) {
				$order_id = $data->post_id;
				$is_schedule_started = get_post_meta( $order_id, 'mci_schedule_started' );
				if( ! $is_schedule_started ) continue;
				$customer_id = get_post_meta( $order_id, '_customer_user', true );
				$this->mci_award_investor_profit( $order_id, $customer_id, false );
			}
		}
	}

	/**
	 * Award profit to investor 
	 */
	public function mci_award_investor_profit( $order_id, $user_id, $profit ) {
		$payment_number = get_post_meta( $order_id, 'mci_payment_number', true ) ? ( int ) get_post_meta( $order_id, 'mci_payment_number', true ) : false;
		if( ! $payment_number ) return;

		if( $profit ) {
			update_post_meta( $order_id, 'mci_next_profit_'.$payment_number, $profit );
		}

		$paid_times = get_post_meta( $order_id, 'mci_paid_times', true ) ? ( int ) get_post_meta( $order_id, 'mci_paid_times', true ) + 1 : 1;
		$pay_times = get_post_meta( $order_id, 'mci_pay_times', true ) ? ( int ) get_post_meta( $order_id, 'mci_pay_times', true ) : 1;
		$profit = ! $profit ? get_post_meta( $order_id, 'mci_next_profit_'.$payment_number, true ) : $profit;
		
		if( $paid_times <= $pay_times ) {
			mycred_add( __( 'Rendimientos de la inversión #' . $order_id, MCI ), $user_id, $profit, __( 'Rendimientos de la inversión #' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );
			$payment_number = $payment_number + 1;
			update_post_meta( $order_id, 'mci_paid_times', $paid_times );
			update_post_meta( $order_id, 'mci_payment_number', $payment_number );
			$total_profit_awarded = get_post_meta( $order_id, 'mci_profit_awarded', true ) ? ( float ) get_post_meta( $order_id, 'mci_profit_awarded', true ) : 0;
			$total_profit_awarded = $total_profit_awarded + $profit;
			update_post_meta( $order_id, 'mci_profit_awarded', $total_profit_awarded );
		} 

		/**
		 * Remove profit paying schedule
		 */
		if( $paid_times >= $pay_times ) {
			wp_update_post( array(
				'ID' 			=> $order_id,
				'post_status' 	=> 'wc-completed'
			) );
			$prd_price = get_post_meta( $order_id, 'mci_prd_price', true );
			mycred_add( __( 'Retorno de la inversión # ' . $order_id, MCI ), $user_id, $prd_price, __( 'Retorno de la inversión # ' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );
			delete_post_meta( $order_id, 'mci_schedule_started' );
			return true;
		}
		return false;
	}

	public function mci_save_settings() {
		if( isset( $_POST['mci_submit_settings'] ) && check_admin_referer( 'mci_nonce', 'mci_nonce_field' ) && current_user_can( 'manage_options' ) ) {

			$opts = [];
			if( isset( $_POST['mci_ref_amount'] ) ) {
				$opts['ref_amount'] = $_POST['mci_ref_amount'];
			}
			if( isset( $_POST['mci_items_remove_msg'] ) ) {
				$opts['items_remove_msg'] = $_POST['mci_items_remove_msg'];
			}
			update_option( 'mci_opts', $opts );
		}
		wp_safe_redirect( $_POST['_wp_http_referer'] );
		exit;
	}

	/**
	 * Get investment data of specific user - AJAX
	 */
	public function mci_get_investment_data_specific_user() {

		if( ! isset( $_POST['user_id'] ) ) return;

		$data['coin_balance'] += ( float ) do_shortcode( '[mci_coins_balance user_id="'.$_POST['user_id'].'"]' );
		$data['investments_referrals'] += ( float ) do_shortcode( '[mci_referral_earnings ref_type="investments" user_id="'.$_POST['user_id'].'"]' );
		$data['other_referrals'] += ( float ) do_shortcode( '[mci_referral_earnings ref_type="other" user_id="'.$_POST['user_id'].'"]' );
		$data['investments'] += ( float ) do_shortcode( '[mci_active_investments user_id="'.$_POST['user_id'].'"]' );
		$data['investments_earnings'] += ( float ) do_shortcode( '[mci_earning_investments user_id="'.$_POST['user_id'].'"]' ); 

		echo json_encode( $data );

		wp_die();
	}

	/**
     * Get matching user names - AJAX
     */
	public function mci_get_matching_user() {

		if( ! isset( $_POST['username'] ) ) return;

		global $wpdb;

		$get_users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users WHERE user_login LIKE '%{$_POST['username']}%' LIMIT 5" );

		// $users = [];
		$li = '';
		if( $get_users ) {
			foreach( $get_users as $user ) {
				$li .= '<li class="mci-user-select" data-id="'.$user->ID.'">'.$user->user_login.'</li>'	;
			}
		}
		echo $li;
		// echo json_encode( $users );
		wp_die();
	}

	/**
     * Cancel investment profit award schedule
     */
	public function mci_cancel_cron_schedule( $order_id, $from_status, $to_status, $order ) {
		$is_investment = get_post_meta( $order_id, 'mci_investment_order', true );
		$is_schedule_started = get_post_meta( $order_id, 'mci_schedule_started', true );
		
		if( ! $is_investment || $to_status != 'cancelled' || ! $is_schedule_started || ! is_admin() ) return;

		foreach( $order->get_items() as $item ) {
			if( get_post_meta( $order_id, 'mci_next_payment', true ) ) {
				delete_post_meta( $order_id, 'mci_next_payment' );
			}
		}
	} 

	/**
     * Create investment profit award schedule
     */
	public function mci_start_cron_schedule( $order_id, $from_status, $to_status, $order ) {
		
		$is_investment = get_post_meta( $order_id, 'mci_investment_order', true );
		$is_schedule_started = get_post_meta( $order_id, 'mci_schedule_started', true );

		if( $is_investment && $to_status == 'on-hold' && ! is_admin() && $order->get_payment_method() == 'bacs' ) {
		    $order->update_status( 'pending-payment' );
		}

		/**
		 * Award coins on on-hold status
		 */
		if( $is_investment && $to_status == 'on-hold' && is_admin() ) {
			$coins = 0;
			foreach( $order->get_items() as $item ) {
				$coins += $item->get_total();
			}
			$customer_id = get_post_meta( $order_id, '_customer_user', true );
			$customer = new WC_Customer( $customer_id );
			$user = get_userdata( $customer->get_id() );
			mycred_add( __( 'Compra de Coins #' . $order_id, MCI ), $user->ID, $coins, __( 'Compra de Coins #' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );
		}

		/**
		 * Deduct coins on on-hold status
		 */
		if( ! $is_investment || $to_status != 'processing' || $is_schedule_started || ! is_admin() ) return;
		
		$coins = 0;
		foreach( $order->get_items() as $item ) {
			
			$coins += $item->get_total();
			/**
			 * Get withdraw option for product
			 */
			$prd_term_days_type = get_post_meta( $order_id, 'mci_prd_wd', true );
			$term = get_post_meta( $order_id, 'mci_term', true );
			$term_type = get_post_meta( $order_id, 'mci_term_type', true );

			$at_the_end = $term_type == 'monthly' ? $term * 30 : $term * 365;

			/**
			 * Create profit award schedule
			 */
			$prd_term_days = $prd_term_days_type == 'monthly' ? 30 : $at_the_end;

			$check_term = $prd_term_days_type == 'monthly' ? $term : 1;
			$check_term = $term_type == 'yearly' && $prd_term_days_type == 'monthly' ? ( $term * 12 ) : $check_term;

			for( $x = 1; $x <= $check_term; $x++ ) {
		    	$this->mci_update_next_profit_meta( $order_id, $x );
		    }
		    update_post_meta( $order_id, 'mci_payment_number', 1 );
		}

		$customer_id = get_post_meta( $order_id, '_customer_user', true );
        $customer = new WC_Customer( $customer_id );
        $user = get_userdata( $customer->get_id() );

        mycred_subtract( __( 'Coins ingresadas a inversión #' . $order_id, MCI ), $user->ID, $coins, __( 'Coins ingresadas a inversión #' . $order_id, MCI ), '', '', MYCRED_DEFAULT_TYPE_KEY );

		/**
		 * If this order has referred by any referral
		 */
		$ref_user_id = get_post_meta( $order_id, 'mci_order_ref', true );
		if( $ref_user_id ) {
			$opts = get_option( 'mci_opts' );
			$ref_amount = isset( $opts['ref_amount'] ) ? $opts['ref_amount'] : '';
			$data = array(
				'affiliate_id' 	=> $ref_user_id,
				'amount' 		=> $ref_amount,
				'description' 	=> __( 'Investment Referral', MCI ),
				'reference' 	=> $order_id,
				'parent_id' 	=> '',
				'currency'		=> '',
				'campaign' 		=> '',
				'context' 		=> '',
				'custom' 		=> 'mci_investment',
				'date'  		=> '',
				'type' 			=> 'sale',
				'products' 		=> ''
			);

			affiliate_wp()->referrals->add( array(
				'affiliate_id' => absint( $data['affiliate_id'] ),
				'amount'       => ! empty( $data['amount'] )      ? sanitize_text_field( $data['amount'] )      : '',
				'description'  => ! empty( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '',
				'reference'    => ! empty( $data['reference'] )   ? sanitize_text_field( $data['reference'] )   : '',
				'parent_id'    => ! empty( $data['parent_id'] )   ? absint( $data['parent_id'] )                : '',
				'currency'     => ! empty( $data['currency'] )    ? sanitize_text_field( $data['currency'] )    : '',
				'campaign'     => ! empty( $data['campaign'] )    ? sanitize_text_field( $data['campaign'] )    : '',
				'context'      => ! empty( $data['context'] )     ? sanitize_text_field( $data['context'] )     : '',
				'custom'       => ! empty( $data['custom'] )      ? $data['custom']                             : '',
				'date'         => ! empty( $data['date'] )        ? $data['date']                               : '',
				'type'         => ! empty( $data['type'] )        ? $data['type']                               : '',
				'products'     => ! empty( $data['products'] )    ? $data['products']                           : '',
				'status'       => 'pending',
			) );	
		}
		

		/**
		 * Save meta for schedule started
		 */
		 update_post_meta( $order_id, 'mci_schedule_started', 'true' );
	}

	/**
	 * Update order status
	 */
	public function mci_update_order_status_on_thankyou( $order_id ) {
		$is_investment = get_post_meta( $order_id, 'mci_investment_order', true );
		
		if( $is_investment ) {
			$order = new WC_Order( $order_id );


			if( $order->get_payment_method() == 'bacs' ) {
		        $order->update_status( 'pending-payment' );
		    }
		}
	}

	/**
	 * Update order status
	 */
	public function mci_update_order_status( $order ) {
		$is_investment = get_post_meta( $order->get_id(), 'mci_investment_order', true );

		if( $order ) {
			if( $order->get_payment_method() == 'bacs' ) {
		        $order->update_status( 'pending-payment' );
		    }
		}
	}

	/**
	 * Get investment data - AJAX
	 */
	public function mci_get_investment_data() {

		if( ! isset( $_POST['req'] ) ) return;

		$users = get_users();

		if( $_POST['req'] == 1 ) {
			$_POST['req'] = ( int ) $_POST['req'] - 1;
		}

		$req = ( int ) $_POST['req'];
		$count = 0;
		$data = [];
		$counted_users = [];
		if( $users ) {
			for( $x = $_POST['req']; $x < count( $users ); $x++ ) {

				if( in_array( $users[$x]->ID, $counted_users ) ) continue;

				$counted_users[] = $users[$x]->ID;
				$data['users'][] = $users[$x]->ID;
				$data['coin_balance'] += ( float ) do_shortcode( '[mci_coins_balance user_id="'.$users[$x]->ID.'"]' );
				$data['investments_referrals'] += ( float ) do_shortcode( '[mci_referral_earnings ref_type="investments" user_id="'.$users[$x]->ID.'"]' );
				$data['other_referrals'] += ( float ) do_shortcode( '[mci_referral_earnings ref_type="other" user_id="'.$users[$x]->ID.'"]' );
				$data['investments'] += ( float ) do_shortcode( '[mci_active_investments user_id="'.$users[$x]->ID.'"]' );
				$data['investments_earnings'] += ( float ) do_shortcode( '[mci_earning_investments user_id="'.$users[$x]->ID.'"]' ); 
				$data['total_invested'] += ( float ) do_shortcode( '[mci_invested user_id="'.$users[$x]->ID.'"]' );

				$req++;
				$count++;
				if( $count == 300 ) break;
			}
		}

		$resp = array(
			'req_num'	=> $req,
			'inv_data' 	=> $data
		);

		echo json_encode( $resp );
		wp_die();
	}

	/**
	 * Plugin menu page
	 */
	public function mci_admin_menu() {
		add_menu_page( __( 'Investments Stats', MCI ), __( 'Investments Stats', MCI ), 'manage_options', 'mci-stats', [ $this, 'mci_investment_stats' ], 'dashicons-chart-bar' );
		add_submenu_page( 'mci-stats', __( 'Settings', MCI ), __( 'Settings', MCI ), 'manage_options', 'mci-opts', [ $this, 'mci_investment_settings' ] );
	}

	public function mci_investment_settings() {
		$opts = get_option( 'mci_opts' );
		$ref_amount = isset( $opts['ref_amount'] ) ? $opts['ref_amount'] : '';
		$items_remove_msg = isset( $opts['items_remove_msg'] ) ? $opts['items_remove_msg'] : '';
	?>
		<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
			<div class="mci-wrapper">
				<h1 class="mci-main-heading"><?php _e( 'Settings', MCI ); ?></h1>
				<div class="mci-row mci-data-row">
					<div class="mci-title">
						<?php _e( 'Shortcodes', MCI ); ?>
					</div>
					<div class="mci-data">
						<p><code class="mci-info-shortcode"><?php _e( '[mci_coins_balance]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows coin balance of current logged in user', MCI ); ?></span></p>
							<p><code>{user_id}</code> <span class="mci-info"><?php _e( 'Use user_id parameter to show specific user data', MCI ); ?></span></p>
						</p>
						<p><code class="mci-info-shortcode"><?php _e( '[mci_earning_investments]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows investment earnings of current logged in user', MCI ); ?></span></p>
							<p><code>{user_id}</code> <span class="mci-info"><?php _e( 'Use user_id parameter to show specific user data', MCI ); ?></span></p>
						</p>
						<p><code class="mci-info-shortcode"><?php _e( '[mci_active_investments]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows active investment of current logged in user', MCI ); ?></span></p>
							<p><code>{user_id}</code> <span class="mci-info"><?php _e( 'Use user_id parameter to show specific user data', MCI ); ?></span></p>
						</p>

						<p><code class="mci-info-shortcode"><?php _e( '[mci_referral_earnings]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows referral earnings of current logged in user', MCI ); ?></span></p>
							<p><code>{user_id}</code> <span class="mci-info"><?php _e( 'Use user_id parameter to show specific user data', MCI ); ?></span></p>
						</p>

						<p><code class="mci-info-shortcode"><?php _e( '[mci_earning_chart]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows investment, earnings, referrals earnings of current logged in user in chart view', MCI ); ?></span></p>
							<p><code>{user_id}</code> <span class="mci-info"><?php _e( 'Use user_id parameter to show specific user data', MCI ); ?></span></p>
						</p>
						<p><code class="mci-info-shortcode"><?php _e( '[mci_invested]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows total invested amount.', MCI ); ?></span></p>
							<p><code>{user_id}</code> <span class="mci-info"><?php _e( 'Use user_id parameter to show specific user data', MCI ); ?></span></p>
						</p>
						<p><code class="mci-info-shortcode"><?php _e( '[mci_user_investment_record]', MCI ); ?></code>
							<p><span class="mci-info"><?php _e( 'Shows user\'s investments.', MCI ); ?></span></p>
						</p>
					</div>
				</div>
				<div class="mci-row mci-data-row">
					<div class="mci-title">
						<?php _e( 'Investment Referral Amount', MCI ); ?>
					</div>
					<div class="mci-data">
						<input type="text" name="mci_ref_amount" placeholder="<?php _e( 'amount', MCI ); ?>" value="<?php echo $ref_amount; ?>" />
					</div>
				</div>
				<div class="mci-row mci-data-row">
					<div class="mci-title">
						<?php _e( 'Cart Items Removed Msg', MCI ); ?>
					</div>
					<div class="mci-data">
						<?php wp_editor( $items_remove_msg, 'mci_items_remove_msg', array(
							'textarea_rows' => 5
						) ); ?>
					</div>
				</div>
				<div class="mci-row mci-data-row">
					<?php wp_nonce_field( 'mci_nonce', 'mci_nonce_field' ); ?>
					<input type="hidden" name="action" value="mci_submit_action" />
					<input type="submit" class="button button-primary" value="<?php _e( 'Save Settings', MCI ); ?>" name="mci_submit_settings" />
				</div>
			</div>
		</form>
	<?php
	}

	/**
	 * Investment Stats Page  
	 */
	public function mci_investment_stats() {
	?>
		<div class="mci-wrapper">
			<h1 class="mci-main-heading"><?php _e( 'Investments Statistics', MCI ); ?>
				<div class="mci-search-box">
					<input type="text" class="mci_user" placeholder="<?php _e( 'search data by user', MCI ); ?>" />
					<!-- <button class="button button-primary mci-filter-btn"><?php _e( 'Filter', MCI ); ?></button> -->
					<ul class="mci-suggestions"></ul>
				</div>
			</h1>
			<div class="mci-row mci-boxes-row">
				<div class="mci-data-box">
					<h2><?php _e( 'Total coins', MCI ); ?></h2>
					<div class="mci-amount-row">

					<span class="mci-coin-bal"><img src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" /></span></div></div>
				<div class="mci-data-box">
					<h2><?php _e( 'Total Invested', MCI ); ?></h2>
					<div class="mci-amount-row">
					<span class="mci-total-invested"><img src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" /></span></div></div>
				<div class="mci-data-box">
					<h2><?php _e( 'Active investments', MCI ); ?></h2>
					<div class="mci-amount-row">
					<span class="mci-total-investments"><img src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" /></span></div></div>
				<div class="mci-data-box ">
					<h2><?php _e( 'Earnings', MCI ); ?></h2>
					<div class="mci-amount-row">
					<span class="mci-total-earnings"><img src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" /></span></div></div>
				<!-- <div class="mci-data-box">
					<h2><?php _e( 'Investments Referrals', MCI ); ?></h2>
					<div class="mci-amount-row">
					<span class="mci-investment-referrals"><img src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" /></span></div></div> -->
				<div class="mci-data-box">
					<h2><?php _e( 'Referrals', MCI ); ?></h2>
					<div class="mci-amount-row">
					<span class="mci-other-referrals"><img src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" /></span></div></div>
			</div>
			
			<div class="mci-row mci-chart-row">
				<div class="mci-chart-loader">
					<?php _e( 'Loading Chart', MCI ); ?>
					<img class="mci-spinner mci-chart-spinner" src="<?php echo MCI_ASSETS_URL . 'imgs/loader.gif'; ?>" />
				</div>
			<canvas class="mci-chart mci-earning-stats-chart"></canvas>
			</div>
		</div>
	<?php
	}

	/**
	 * Get investment earning amount of user (SHORTCODE)
	 */
	public function mci_earning_investments_shortcode( $atts ) {
		ob_start();
		
		if( ! is_user_logged_in() ) return;

		global $wpdb;
		$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
		$investments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'mci_investment_order' AND meta_value = '".$user_id."'" );
		$amount = 0;
		
		if( $investments ) {
			foreach( $investments as $invest ) {
				if( get_post_status( $invest->post_id ) == 'wc-completed' || get_post_status( $invest->post_id ) == 'wc-processing' ) {
					$prds = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'mci_prd_id' AND post_id = '".$invest->post_id."'" );
					if( $prds ) {
						foreach( $prds as $prd ) {
							$prd_id = $prd->meta_value;
							$order_id = $invest->post_id;
							$profit = get_post_meta( $order_id, 'mci_profit_awarded', true ) ? ( float ) get_post_meta( $order_id, 'mci_profit_awarded', true ) : 0;
							$amount += $profit;
						}
					}
				}
			}
		}
		if( is_admin() ) {
			echo str_replace( ',', '', $amount );
		} else {
			echo number_format( $amount, 2 );
		}
		return ob_get_clean();
	}

	public function mci_earning_chart_shortcode( $atts ) {
		ob_start();

		$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
		$ref_data = $this->mci_get_chart_data( $user_id );

		$filtered_earnings = [];
		$filtered_investment = [];
		$filtered_referrals = [];
		$months = [];
		foreach( $ref_data['sorted_months'] as $month ) {
			$months[] = $month;
			$filtered_earnings[] = $ref_data['earnings'][$month];
			$filtered_investment[] = $ref_data['investments'][$month];
			$filtered_referrals[] = $ref_data['other_referrals'][$month];
		}

		// $ref_data['earnings'] = implode( ',', $ref_data['earnings'] );
		// $ref_data['investments'] = implode( ',', $ref_data['investments'] );
		// $ref_data['other_referrals'] = implode( ',', $ref_data['other_referrals'] );
		$ref_data['invested'] = implode( ',', $ref_data['invested'] );
		$ref_data['investment_referrals'] = implode( ',', $ref_data['investment_referrals'] );
		$ref_data['logs'] = implode( ',', $ref_data['logs'] );
		$months = implode( ',', $months );
		$ref_data['earnings'] = implode( ',', $filtered_earnings );
		$ref_data['investments'] = implode( ',', $filtered_investment);
		$ref_data['other_referrals'] = implode( ',', $filtered_referrals );

	?>
		<canvas class="mci-chart mci-chart-earning" data-earning="<?php echo $ref_data['earnings']; ?>" data-act-inv="<?php echo $ref_data['investments']; ?>" data-invested="<?php echo $ref_data['invested']; ?>" data-inv-ref="<?php echo $ref_data['investment_referrals']; ?>" data-ot-ref="<?php echo $ref_data['other_referrals']; ?>" data-bal="<?php echo $ref_data['logs']; ?>" data-months="<?php echo $months; ?>"></canvas>
	<?php

		return ob_get_clean();
	}

	/**
	 * Referral earning shortcode
	 */
	public function mci_referral_earnings_shortcode( $atts ) {
		ob_start();

		$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
		$aff_id = affwp_get_affiliate_id( $user_id );

		if( ! is_user_logged_in() || ! $aff_id || ! function_exists( 'affiliate_wp' ) ) return;

		global $wpdb;

		$amount = 0;

		/**
		 * Investment referrals
		 */
		if( isset( $atts['ref_type'] ) && $atts['ref_type'] == 'investments' ) {
		    $refferals = affiliate_wp()->referrals->get_referrals( array(
				'affiliate_id' 	=> $aff_id,
				'number'		=> -1,
				'status'		=> 'paid'
			), false );
			
			if( $refferals ) {
				foreach( $refferals as $ref ) {
					if( ! get_post_meta( $ref->reference, 'mci_investment_order', true ) ) continue;
					$amount += ( float ) $ref->amount;
				}
			}
		} 

		/**
		 * Other referrals
		 */
		elseif( isset( $atts['ref_type'] ) && $atts['ref_type'] == 'other' || ! isset( $atts['ref_type'] ) ) {

			$refferals = affiliate_wp()->referrals->get_referrals( array(
				'affiliate_id' 	=> $aff_id,
				'number'		=> -1,
				'status'		=> 'paid'
			), false );

			$amount = 0;
			if( $refferals ) {
				foreach( $refferals as $ref ) {
					//if( get_post_meta( $ref->reference, 'mci_investment_order', true ) ) continue;
					$amount += ( float ) $ref->amount;
				}
			}
		} else {
			$amount = 0;
			if( $refferals ) {
				foreach( $refferals as $ref ) {
					$amount += ( float ) $ref->amount;
				}
			}
		}

		if( is_admin() ) {
			echo str_replace( ',', '', $amount );
		} else {
			echo number_format( $amount, 2 );
		}

		return ob_get_clean();
	}

	/**
	 * Active investments shortcode
	 */
	public function mci_active_investments_shortcode( $atts ) {
		ob_start();

		if( ! is_user_logged_in() ) return;
		
		global $wpdb;

		$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
		$investments = wc_get_orders( array(
		    'meta_key' 		=> 'mci_investment_order',
		    'meta_value' 	=> $user_id,
		    'numberposts' => -1
		) );
		$amount = 0;
		if( $investments ) {
			foreach( $investments as $invest ) {

				if( $invest->get_status() == 'processing' ) {

					$prds = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'mci_prd_id'  AND post_id = '".$invest->get_id()."'" );

					if( $prds ) {
						foreach( $prds as $prd ) {
							$prd_id = $prd->meta_value;
							$amount += get_post_meta( $invest->get_id(), 'mci_prd_price', true );
						}
					}
				}
			}
		}
		if( is_admin() ) {
			echo str_replace( ',', '', $amount );
		} else {
			echo number_format( $amount, 2 );
		}
		
		return ob_get_clean();
	}

	/**
	 * Coin balance shortcode
	 */
	public function mci_coins_balance_shortcode( $atts ) {
		ob_start();

		if( ! is_user_logged_in() ) return;
		$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();

		if( is_admin() ) {
			echo str_replace( ',', '', mycred_display_users_balance( $user_id, MYCRED_DEFAULT_TYPE_KEY ) );
		} else {
			echo mycred_display_users_balance( $user_id, MYCRED_DEFAULT_TYPE_KEY );
		}
		

		return ob_get_clean();
	}

	/**
     * Update status for investment product
     */
	public function mci_update_purchase_status( $status, $prd ) {

		if( get_post_meta( $prd->get_id(), 'mci_investment_product', true ) ) {
			$status = true;
		}

		return $status;
	}

	/**
     * Save investment order meta
     */
	public function mci_save_investment_product_meta( $order_id, $order ) {
		// $order = new WC_Order( $order_id );

		if( isset( $_COOKIE['affwp_ref'] ) ) {
			update_post_meta( $order_id, 'mci_order_ref', $_COOKIE['affwp_ref'] );
		}

		$is_inv_product = false;
		$coins = 0;
		foreach( $order->get_items() as $item ) {
			if( isset( $_COOKIE['mci_wd_'.$item->get_product_id()] ) ) {
				
				$is_inv_product = true;
				$coins += $item->get_total();

				/**
				 * Save product id
				 */
				update_post_meta( $order_id, 'mci_prd_id', $item->get_product_id() );

				/**
				 * Save withdraw option for product
				 */
				update_post_meta( $order_id, 'mci_prd_wd', $_COOKIE['mci_wd_'.$item->get_product_id()] );
				$prd_term_days = $_COOKIE['mci_wd_'.$item->get_product_id()];
				
				$term = get_post_meta( $item->get_product_id(), 'mci_term', true ) ? ( int ) get_post_meta( $item->get_product_id(), 'mci_term', true ) : 1;
				$term_type = get_post_meta( $item->get_product_id(), 'mci_term_type', true ) ? get_post_meta( $item->get_product_id(), 'mci_term_type', true ) : 'monthly';

				update_post_meta( $order_id, 'mci_term', $term );
				update_post_meta( $order_id, 'mci_term_type', $term_type );

				$at_the_end = $term_type == 'monthly' ? $term * 30 : $term * 365;
				
				if( $term_type == 'monthly' ) {
					$wd_type = $prd_term_days == 'monthly' ? 30 : ( $term * 30 );
					$times = ( $term * 30 ) / $wd_type;
					update_post_meta( $order_id, 'mci_pay_times', $times );
				} else {
					$wd_type = $prd_term_days == 'monthly' ? 30 : ( $term * 365 );
					$times = ( $term * 365 ) / $wd_type;
					update_post_meta( $order_id, 'mci_pay_times', floor( $times ) );
				}

				/**
				 * Save product price
				 */
				if( isset( $_COOKIE['mci_prd_' . $item['product_id']] ) ) {
		    		update_post_meta( $order_id, 'mci_prd_price', $_COOKIE['mci_prd_' . $item['product_id']] );
		    	}

				$interest_type = get_post_meta( $item->get_product_id(), 'mci_interest_type', true ) ? get_post_meta( $item->get_product_id(), 'mci_interest_type', true ) : '';
				update_post_meta( $order_id, 'mci_prd_interest_type', $interest_type );

				$interest_rate = get_post_meta( $item->get_product_id(), 'mci_base_interest', true ) ? ( float ) get_post_meta( $item->get_product_id(), 'mci_base_interest', true ) : 0;
				update_post_meta( $order_id, 'mci_prd_interest_rate', $interest_rate );
			}
		}

		if( $is_inv_product ) {
			$user_id = get_current_user_id();
			update_post_meta( $order_id, 'mci_investment_order', $user_id );
		}
	}

	/**
     * Add investment product to cart
     */
	public function mci_added_investment_to_cart() {
		if( isset( $_POST['mci_prd_id'] ) && isset( $_POST['mci_points'] ) && class_exists( 'WC_Cart' ) ) {

			/**
			 * Remove other products from cart
			 */
			$removed_cart_items = 0;
            foreach( WC()->cart->get_cart() as $key => $item ) {
                WC()->cart->remove_cart_item( $key );
                $removed_cart_items = 1;
            }

            if( $removed_cart_items ) {
            	setcookie( 'mci_removed_cart_items', 'true', 0, '/' );
            }

			$cart = new WC_Cart;
			$cart->add_to_cart( $_POST['mci_prd_id'], 1 );
			setcookie( 'mci_prd_' . $_POST['mci_prd_id'], $_POST['mci_points'], 0, '/' );

			if( isset( $_POST['mci_withdraw'] ) ) {
				setcookie( 'mci_wd_' . $_POST['mci_prd_id'], $_POST['mci_withdraw'], 0, '/' );
			} else {
				$withdraw_type = isset( $_POST['mci_prd_id'] ) && get_post_meta( $_POST['mci_prd_id'], 'mci_withdraw_type', true ) ? get_post_meta( $_POST['mci_prd_id'], 'mci_withdraw_type', true ) : '';
				setcookie( 'mci_wd_' . $_POST['mci_prd_id'], $withdraw_type, 0, '/' );
			}

            wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}
	}

	/**
     * Update investment product price
     */
	public function mci_update_cart_investment_price( $cart ) {

	    // Loop through cart items
	    foreach ( $cart->get_cart() as $item ) {

	    	if( isset( $_COOKIE['mci_prd_' . $item['product_id']] ) ) {
	    		$item['data']->set_price( $_COOKIE['mci_prd_' . $item['product_id']] );
	    	}
	        
	    }
	}

	/**
     * Remove add to cart link from investment products
     */
	public function mci_remove_add_to_cart_link( $link, $product, $args ) {
		
		if( get_post_meta( $product->get_id(), 'mci_investment_product', true ) ) {
			?>
			<style type="text/css">
				li.product.post-<?php echo $product->get_id(); ?> span.price {
					display: none;
				}
			</style>
			<?php
			return '<a href="'.get_permalink( $product->get_id() ).'" class="button product_type_simple add_to_cart_button ajax_add_to_cart">'.__( 'Read More', MCI ).'</a>';
		}

		return $link;
	}

	/**
     * Add investment options on product main page
     */
	public function mci_add_investment_product_options() {

		global $post;

		if( $post->post_type != 'product' || ! get_post_meta( $post->ID, 'mci_investment_product', true )  ) return;

		global $wpdb;

		$referrals = [];
		if( affwp_is_affiliate( get_current_user_id() ) ) {
			$aff_id = affwp_get_affiliate_id( get_current_user_id() );
			$referrals = affiliate_wp()->referrals->get_referrals( array(
				'affiliate_id' => $aff_id,
				'number' 		=> -1,
				'status'		=> 'paid'
			), false );
		}
		$min_priv = isset( $post->ID ) && get_post_meta( $post->ID, 'mci_min_amount_priv', true ) ? get_post_meta( $post->ID, 'mci_min_amount_priv', true ) : '';
		$min = get_post_meta( $post->ID, 'mci_min_amount', true ) ? get_post_meta( $post->ID, 'mci_min_amount', true ) : '';
		$max = get_post_meta( $post->ID, 'mci_max_amount', true ) ? get_post_meta( $post->ID, 'mci_max_amount', true ) : '';
		$withdraw = get_post_meta( $post->ID, 'mci_show_withdraw', true ) ? true : '';
		$min_priv_ref = isset( $post->ID ) && get_post_meta( $post->ID, 'mci_min_amount_ref', true ) ? ( int ) get_post_meta( $post->ID, 'mci_min_amount_ref', true ) : '';

		if( $referrals && count( $referrals ) >= $min_priv_ref ) {
			$min = $min_priv ? $min_priv : 50;
		}

		?>
		<style type="text/css">
			#product-<?php echo $post->ID; ?> .price {
				display: none;
			}
			#product-<?php echo $post->ID; ?> form.cart {
				display: none;
			}
		</style>
		<form method="post">
			<div class="mci-investment-options-box">
				<div class="mci-row mci-points-row">
					<input type="hidden" value="<?php echo $post->ID; ?>" name="mci_prd_id" />
					<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" name="mci_points" placeholder="<?php _e( 'Investment points', MCI ); ?>" required />
				</div>
			<?php
				if( $withdraw != '' ) {
			?>
				<div class="mci-row mci-withdraw-row">
					<label><?php _e( 'Withdraw', MCI ); ?></label>
					<select name="mci_withdraw">on_term_end
						<option value="monthly"><?php _e( 'Monthly', MCI ); ?></option>
						<option value="on_term_end"><?php _e( 'On Term Ends', MCI ); ?></option>
					</select>
				</div>
			<?php } ?>
				<input type="submit" value="<?php _e( 'Invest', MCI ); ?>" />
			</div>
		</form>
		<?php
	}

	/**
     * Add admin scripts
     */
	public function mci_admin_scripts() {
		wp_enqueue_style( 'mci-admin-css', MCI_ASSETS_URL . 'css/mci-admin.css', [], self::VERSION, null );
		wp_enqueue_script( 'mci-Chart-js', MCI_ASSETS_URL . 'js/Chart.min.js', ['jquery'], self::VERSION, true );
		wp_enqueue_script( 'mci-admin-js', MCI_ASSETS_URL . 'js/mci-admin.js', ['jquery'], self::VERSION, true );
		$users = get_users();
		$users_per_req =  ceil( count( $users ) / 300 ); 

		wp_localize_script( 'mci-admin-js', 'MciAdmin', array(
			'ajaxURL' 		=> admin_url( 'admin-ajax.php' ),
			'ajaxReqs' 		=> $users_per_req,
			'wcCurrency' 	=> get_woocommerce_currency(),
			'affCurrency' 	=> affwp_get_currency(),
			'chartData'		=> $this->mci_get_chart_data( null ),
			'isWithdrawalPost' => isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wallet_withdrawal' ? true : false
		) );
	}

	/**
     * Add theme scripts
     */
	public function mci_theme_scripts() {
		wp_enqueue_style( 'mci-theme-css', MCI_ASSETS_URL . 'css/mci-theme.css', [], self::VERSION, null );
		wp_enqueue_script( 'mci-Chart-js', MCI_ASSETS_URL . 'js/Chart.min.js', ['jquery'], self::VERSION, true );
		wp_enqueue_script( 'mci-theme-js', MCI_ASSETS_URL . 'js/mci-theme.js', ['jquery'], self::VERSION, true );

	}

    /**
     * Save investment product meta
     */
    public function mci_save_investment_product_type( $post_id, $post, $update ) {
    	if( $post->post_type != 'product' ) return;

    	if( isset( $_POST['product-type'] ) && $_POST['product-type'] == 'mci_investment' ) {
    		update_post_meta( $post_id, 'mci_investment_product', 'true' );
    		update_post_meta( $post_id, '_stock_status', 'instock' );
    		update_post_meta( $post_id, '_sold_individually', 'yes' );
    		update_post_meta( $post_id, '_regular_price', 0 );
    	} else {
    		delete_post_meta( $post_id, 'mci_investment_product' );
    	}

    	if( isset( $_POST['mci_min_amount_priv'] ) ) {
    		update_post_meta( $post_id, 'mci_min_amount_priv', $_POST['mci_min_amount_priv'] );
    	}
    	if( isset( $_POST['mci_min_amount_ref'] ) ) {
    		update_post_meta( $post_id, 'mci_min_amount_ref', $_POST['mci_min_amount_ref'] );
    	}
    	if( isset( $_POST['mci_min_amount'] ) ) {
    		update_post_meta( $post_id, 'mci_min_amount', $_POST['mci_min_amount'] );
    	}
    	if( isset( $_POST['mci_max_amount'] ) ) {
    		update_post_meta( $post_id, 'mci_max_amount', $_POST['mci_max_amount'] );
    	}
    	if( isset( $_POST['mci_term'] ) ) {
    		update_post_meta( $post_id, 'mci_term', $_POST['mci_term'] );
    	}
    	if( isset( $_POST['mci_term_type'] ) ) {
    		update_post_meta( $post_id, 'mci_term_type', $_POST['mci_term_type'] );
    	}
    	if( isset( $_POST['mci_show_withdraw'] ) ) {
    		update_post_meta( $post_id, 'mci_show_withdraw', $_POST['mci_show_withdraw'] );
    	} elseif( ! isset( $_POST['mci_show_withdraw'] ) ) {
    		delete_post_meta( $post_id, 'mci_show_withdraw' );
    	}
    	if( isset( $_POST['mci_base_interest'] ) ) {
    		update_post_meta( $post_id, 'mci_base_interest', $_POST['mci_base_interest'] );
    	}
    	if( isset( $_POST['mci_interest_type'] ) ) {
    		update_post_meta( $post_id, 'mci_interest_type', $_POST['mci_interest_type'] );
    	}
    	if( isset( $_POST['mci_withdraw_type'] ) ) {
    		update_post_meta( $post_id, 'mci_withdraw_type', $_POST['mci_withdraw_type'] );
    	}
    	if( isset( $_POST['mci_exlude_shipping'] ) ) {
    		update_post_meta( $post_id, 'mci_exlude_shipping', $_POST['mci_exlude_shipping'] );
    	} else {
    		delete_post_meta( $post_id, 'mci_exlude_shipping' );
    	}
    }

    /**
     * Investment tab options
     */
    public function mci_investment_options_tab_data() {

    	if( isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'product' && get_post_meta( $_GET['post'], 'mci_investment_product', true ) ) { ?>

    		<script type="text/javascript">
    			var typeSelector = document.querySelector( 'select#product-type' );

    			if( typeSelector !== undefined && typeSelector !== null ) {
    				var opts = document.querySelectorAll( 'select#product-type option' );
    				
    				opts.forEach( ( elem, index ) => {
    					
    					if( elem.value == 'mci_investment' ) {
    						elem.setAttribute( 'selected', 'selected' );	
    					}
    					
    				} );
    			}
    		</script>
    <?php
    	}

    	$min_priv = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_min_amount_priv', true ) ? get_post_meta( $_GET['post'], 'mci_min_amount_priv', true ) : '';
    	$min_priv_ref = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_min_amount_ref', true ) ? get_post_meta( $_GET['post'], 'mci_min_amount_ref', true ) : '';
    	$min = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_min_amount', true ) ? get_post_meta( $_GET['post'], 'mci_min_amount', true ) : '';
		$max = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_max_amount', true ) ? get_post_meta( $_GET['post'], 'mci_max_amount', true ) : '';
		$term = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_term', true ) ? get_post_meta( $_GET['post'], 'mci_term', true ) : '';
		$type = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_term_type', true ) ? get_post_meta( $_GET['post'], 'mci_term_type', true ) : '';
		$withdraw = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_show_withdraw', true ) ? 'checked="checked"' : '';
		$interest_amount = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_base_interest', true ) ? get_post_meta( $_GET['post'], 'mci_base_interest', true ) : '';
		$withdraw_type = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_withdraw_type', true ) ? get_post_meta( $_GET['post'], 'mci_withdraw_type', true ) : '';
		$interest_type = isset( $_GET['post'] ) && get_post_meta( $_GET['post'], 'mci_interest_type', true ) ? get_post_meta( $_GET['post'], 'mci_interest_type', true ) : '';
    	$excluded = get_post_meta( $_GET['post'], 'mci_exlude_shipping', true ) ? 'checked' : '';
    	
    ?>
    	<div class="mci-investment-options panel woocommerce_options_panel wc-metaboxes-wrapper hidden" id="mci-investment-options">
    		<p class="form-field">
				<label><?php _e( 'Exclude Shipping', MCI ); ?></label>
				<input type="checkbox" name="mci_exlude_shipping" <?php echo $excluded; ?> />
				<?php _e( 'Exclude Shipping from this product', MCI ); ?>
			</p>
    		<p class="form-field">
				<label><?php _e( 'Minimum Amount(Privilege)', MCI ); ?></label>
				<input type="text" class="short wc_input_price" style="" name="mci_min_amount_priv" id="_regular_price" value="<?php echo $min_priv; ?>" placeholder="Minimum investment amount with privilege">
				<span><?php _e( ' on', MCI ); ?></span>
				<input type="text" class="short wc_input_price mci_min_amount_ref" style="" name="mci_min_amount_ref" id="_regular_price" value="<?php echo $min_priv_ref; ?>" placeholder="Minimum investment amount with privilege">
				<?php _e( ' referrals.', MCI ); ?>
			</p>
			<p class="form-field">
				<label><?php _e( 'Minimum Amount', MCI ); ?></label>
				<input type="text" class="short wc_input_price" style="" name="mci_min_amount" id="_regular_price" value="<?php echo $min; ?>" placeholder="Minimum investment amount">
			</p>
			<p class="form-field">
				<label><?php _e( 'Maximum Amount', MCI ); ?></label>
				<input type="text" class="short wc_input_price" style="" name="mci_max_amount" id="_regular_price" value="<?php echo $max; ?>" placeholder="Maximum investment amount"> 
			</p>
			<p class="form-field">
				<label><?php _e( 'Term', MCI ); ?></label>
				<select name="mci_term">
					<option value="1" <?php echo $term == 1 ? 'selected="selected"' : ''; ?>>1</option>
					<option value="2" <?php echo $term == 2 ? 'selected="selected"' : ''; ?>>2</option>
					<option value="3" <?php echo $term == 3 ? 'selected="selected"' : ''; ?>>3</option>
					<option value="4" <?php echo $term == 4 ? 'selected="selected"' : ''; ?>>4</option>
					<option value="5" <?php echo $term == 5 ? 'selected="selected"' : ''; ?>>5</option>
					<option value="6" <?php echo $term == 6 ? 'selected="selected"' : ''; ?>>6</option>
					<option value="7" <?php echo $term == 7 ? 'selected="selected"' : ''; ?>>7</option>
					<option value="8" <?php echo $term == 8 ? 'selected="selected"' : ''; ?>>8</option>
					<option value="9" <?php echo $term == 9 ? 'selected="selected"' : ''; ?>>9</option>
					<option value="10" <?php echo $term == 10 ? 'selected="selected"' : ''; ?>>10</option>
					<option value="11" <?php echo $term == 11 ? 'selected="selected"' : ''; ?>>11</option>
					<option value="12" <?php echo $term == 12 ? 'selected="selected"' : ''; ?>>12</option>
				</select>
				<select name="mci_term_type">
					<option value="monthly" <?php echo $type == 'monthly' ? 'selected="selected"' : ''; ?>><?php _e( 'Month(s)', MCI ); ?></option>
					<option value="yearly" <?php echo $type == 'yearly' ? 'selected="selected"' : ''; ?>><?php _e( 'Year(s)', MCI ); ?></option>
				</select>
			</p>
			<p class="form-field">
				<label><?php _e( 'Withdraw Type', MCI ); ?></label>
				<select name="mci_withdraw_type">
					<option value="monthly" <?php echo $withdraw_type == 'monthly' ? 'selected' : ''; ?>><?php _e( 'Monthly', MCI ); ?></option>
					<option value="on_term_end" <?php echo $withdraw_type == 'on_term_end' ? 'selected' : ''; ?>><?php _e( 'On Term Ends', MCI ); ?></option>
				</select>
			</p>
			<p class="form-field">
				<label><?php _e( 'Withdraw Option', MCI ); ?></label>
				<input type="checkbox" name="mci_show_withdraw" <?php echo $withdraw; ?> />
				<span><?php _e( 'Show Withdraw option to investor.', MCI ); ?></span>
			</p>	
			<p class="form-field">
				<label><?php _e( 'Interest', MCI ); ?></label>
				<input type="text" class="short wc_input_price" name="mci_base_interest" placeholder="<?php _e( 'Base Interest', MCI ); ?>" value="<?php echo $interest_amount; ?>" />
				<select name="mci_interest_type">
					<option value="base" <?php echo $interest_type == 'base' ? 'selected' : ''; ?>><?php _e( 'Base Interest', MCI ); ?></option>
					<option value="compound" <?php echo $interest_type == 'compound' ? 'selected' : ''; ?>><?php _e( 'Compound Interest', MCI ); ?></option>
				</select>
			</p>	
    	</div>
    <?php
    }

    /**
     * Create tab for investment options
     */
    public function mci_create_investment_options_tab( $tabs ) {

    	$tabs['mci_investment_opts'] = array(
			'label'    => __( 'Investment Options', MCI ),
			'target'   => 'mci-investment-options',
			'class'    => array( 'hide_if_simple', 'hide_if_variable', 'hide_if_virtual', 'hide_if_grouped', 'hide_if_external', 'inventory_options' ),
			'priority' => 10,
		);

    	return $tabs;
    }

    /**
     * Add investment type product option
     */
    public function mci_create_investment_type ( $types ) {

    	$types['mci_investment'] = __( 'Point investment', MCI );

    	return $types;
    }

	/**
	 * Investment profit schedule cron
	 */
    public function mci_create_investment_cron() {
    	if( ! wp_next_scheduled ( 'mci_award_investment_profit') ) {
    		wp_schedule_event( time(), 'daily', 'mci_award_investment_profit' );	
    	}
    }

}

/**
 * Display admin notifications if dependency not found.
 */
function mci_ready() {

    if( ! is_admin() ) {
        return;
    }

    if( ! class_exists( 'myCRED_Core' ) || ! class_exists( 'WooCommerce' ) || ! class_exists( 'Affiliate_WP' ) ) {
        deactivate_plugins ( plugin_basename ( __FILE__ ), true );
        $class = 'notice is-dismissible error';
        $message = __( 'myCred Investments add-on requires myCred, WooCommerce and AffiliateWP plugins to be activated.', 'MCI' );
        printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }
}

/**
 * @return bool
 */
function MCI() {
    if ( ! class_exists( 'myCRED_Core' ) || ! class_exists( 'WooCommerce' ) || ! class_exists( 'Affiliate_WP' ) ) {
        add_action( 'admin_notices', 'mci_ready' );
        return false;
    }

    return MCI::instance();
}
add_action( 'plugins_loaded', 'MCI' );

register_activation_hook( __FILE__, [ 'MCI', 'mci_create_investment_cron' ] );