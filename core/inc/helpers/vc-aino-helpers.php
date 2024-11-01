<?php

class Vc_Aino_Helpers {

    /** Refers to a single instance of this class. */
    private static $instance = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return  Foo A single instance of this class.
     */
    public static function get_instance() {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get the chosen shipping methods from the WooCommerce session
     *
     * @return array
     */
    public function get_chosen_methods() {
        return WC()->session->get('chosen_shipping_methods');
    }

    /**
     * Get the chosen shipping methods from the WooCommerce session
     *
     * @return array
     */
    public function get_chosen_method() {
        return $this->get_chosen_methods()[0];
    }

    /**
     * Get the chosen shipping methods from the WooCommerce session
     *
     * @return array
     */
    public function get_chosen_method_instance() {
        return $this->get_shipping_method($this->get_chosen_method());
    }

    /**
     * Splits the method id to array containing the WooCommerce id and the method instance number
     *
     * @return array
     */
    public function get_method_array($method) {
        return explode(':', $method);
    }

    /**
     * Get the id of the passed method
     *
     * @return string
     */
    public function get_method_id($method_array) {
        return $method_array[0];
    }

    /**
     * Get the instance of the passed method
     *
     * @return string
     */
    public function get_method_instance_number($method_array) {
        return $method_array[1];
    }

    /**
     * Get the instance number of the passed method
     *
     * @return string
     */
    public function get_method_instance($instance_number) {
        return WC_Shipping_Zones::get_shipping_method($instance_number);
    }

    /**
     * Get the instance of the passed method
     *
     * @return string
     */
    public function get_shipping_method($method_woo_id) {
        $method_arr = $this->get_method_array($method_woo_id);
        $method_instance_number = $this->get_method_instance_number($method_arr);

        return $this->get_method_instance($method_instance_number);
    }

    /**
     * Get the data saved in the aio_data cookie
     *
     * @return string
     */
    public function get_aio_data() {
        return isset($_COOKIE['aio_data']) ? json_decode(stripslashes(filter_input(INPUT_COOKIE, 'aio_data')), true) : array();
    }

    /**
     * Get the data saved in the aio_data cookie
     *
     * @return string
     */
    public function get_aio_delivery_data() {
        return isset($_COOKIE['aio_data']) ? json_decode(stripslashes(filter_input(INPUT_COOKIE, 'aio_data')), true)['deliveryDetails'] : null;
    }

    /**
     * Get the data saved in the aio_data cookie
     *
     * @return string
     */
    public function get_default_options() {
        $aio_data = $this->get_aio_data();
        
        if(!empty($aio_data['shippingId'])){
            return $aio_data;
        } else {
            $method_instance = $this->get_chosen_method_instance();
            return array('shippingId' => $method_instance->vc_aino_type);
        }
    }

    /**
     * get_cost function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function get_cost_for_type($type, $method_id, $method_instance) {
        global $woocommerce;

        $rates = get_option($method_id . '_rates[' . $method_instance . ']');

        if (isset($woocommerce->cart->cart_contents_total)) {
            $weight = $woocommerce->cart->get_cart_contents_weight();
            $total = $woocommerce->cart->subtotal;

            if (!empty($rates)) {
                if (!empty($rates[$type])) {
                    $rates_val = $rates[$type];
                } else {
                    $rates_val = $rates;
                }

                foreach ($rates_val as $rate) {
                    if ($this->check_rate($rate)) {
                        if ($weight >= $rate['weight_min'] && $weight < $rate['weight_max'] && $total >= $rate['total_from'] && $total < $rate['total_to']) {
                            return $rate['cost'];
                        }
                    }
                }
            }
        }

        return null;
    }

    public function check_rate($rate) {
        if (!isset($rate['weight_min']) || !isset($rate['weight_max']) || !isset($rate['total_from']) || !isset($rate['total_to'])
        ) {
            return false;
        }

        return true;
    }

    public function get_new_tax($cost) {
        $tax = new WC_Tax();
        $country = '';

        if (!empty($_GET['co'])) {
            $country = filter_input(INPUT_GET, 'co');
        } else if (!empty($_POST['co'])) {
            $country = filter_input(INPUT_POST, 'co');
        }

        $tax_rates = $tax->find_rates(array('country' => $country));

        return WC_Tax::calc_tax($cost, $tax_rates, false);
    }

    /**
     * get_cost function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function get_method_price($method_definition, $package) {

        return $method_definition->get_cost($package);
    }

}

$vc_aino_heplers = Vc_Aino_Helpers::get_instance();
