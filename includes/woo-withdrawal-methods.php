<?php

/**
 * American Express gateway for withdrawal 
 * from WooCommerce Wallet 
 *
 * @author subrata
 */
class American_Express {

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->id = 'mci-ax';
        $this->method_title = __( 'American Express', 'woo-wallet-withdrawal' );
    }

    public function get_method_id() {
    	return $this->id;
    }

    public function get_method_title() {
    	return $this->method_title;
    }

    public function is_enable_auto_withdrawal() {
        return false;
    }

    public function process_payment($withdrawal) {
        return parent::process_payment($withdrawal);
    }

    public static function is_available() {
        return true;
    }

    public function gateway_charge() {
        $charge = array(
            'amount' => 0,
            'type' => 'percent'
        );
        if ('on' === woo_wallet()->settings_api->get_option('_is_enable_gateway_charge', '_wallet_settings_withdrawal', 'off')) {
            $type = woo_wallet()->settings_api->get_option('_withdrawal_gateway_charge_type', '_wallet_settings_withdrawal', 'percent');
            $charge['amount'] = woo_wallet()->settings_api->get_option('_charge_' . $this->get_method_id(), '_wallet_settings_withdrawal', 0);
            if ('percent' === $type) {
                $charge['type'] = 'percent';
            } else {
                $charge['type'] = 'fixed';
            }
        }
        return $charge;
    }
}

/**
 * Bitcoin gateway for withdrawal 
 * from WooCommerce Wallet 
 *
 * @author subrata
 */
class Bitcoin {

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->id = 'mci-bt';
        $this->method_title = __( 'Bitcoin', 'woo-wallet-withdrawal' );
    }

    public function get_method_id() {
        return $this->id;
    }

    public function get_method_title() {
        return $this->method_title;
    }

    public function is_enable_auto_withdrawal() {
        return false;
    }

    public function process_payment($withdrawal) {
        return parent::process_payment($withdrawal);
    }

    public static function is_available() {
        return true;
    }

    public function gateway_charge() {
        $charge = array(
            'amount' => 0,
            'type' => 'percent'
        );
        if ('on' === woo_wallet()->settings_api->get_option('_is_enable_gateway_charge', '_wallet_settings_withdrawal', 'off')) {
            $type = woo_wallet()->settings_api->get_option('_withdrawal_gateway_charge_type', '_wallet_settings_withdrawal', 'percent');
            $charge['amount'] = woo_wallet()->settings_api->get_option('_charge_' . $this->get_method_id(), '_wallet_settings_withdrawal', 0);
            if ('percent' === $type) {
                $charge['type'] = 'percent';
            } else {
                $charge['type'] = 'fixed';
            }
        }
        return $charge;
    }

}