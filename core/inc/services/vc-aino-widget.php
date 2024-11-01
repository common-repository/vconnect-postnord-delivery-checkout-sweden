<?php

class Vc_Aino_Widget {

    private $colorCode = 'B4E1D8';
    private $language = AINO_WIDGET_LANG;
    private $theme = 'Inline';
    public $translations = array();

    private $helpers = null;

    /** Refers to a single instance of this class. */
    private static $instance = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return  Foo A single instance of this class.
     */
    public static function get_instance() {

        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    /**
     * Constructor
     *
     * @access private
     * @return void
     */
    private function __construct() {
        global $vc_aino_heplers;
        $this->helpers = $vc_aino_heplers;

        $this->translations = $this->get_widget_translations();
        $this->description = get_option('vc_aino_widget_description');
    }

    /**
     * Collects information about the active AIO shipping methods in a format used by the widget
     *
     * @access public
     * @return array
     */
    public function get_widget() {
        $response = array();

        $country_post = filter_input(INPUT_POST, 'co');
        WC()->customer->set_props(array(
            'shipping_country' => !empty($country_post) ? $country_post : null,
        ));
        WC()->customer->save();

        $shipping_methods = array();

        foreach (WC()->cart->get_shipping_packages() as $package) {
            $zone = WC_Shipping_Zones::get_zone_matching_package($package);

            foreach ($zone->get_shipping_methods() as $method) {
                if (!empty($method->is_vc_aino) && $method->enabled == 'yes') {
                    $shipping_methods[] = $this->process_shipping_method($method, $package);
                }
            }
        }

        $response['defaultOptions'] = $this->helpers->get_default_options();

        $response['shipping'] = $shipping_methods;

        $response['colorCode'] = $this->colorCode;
        $response['language'] = $this->language;
        $response['description'] = $this->description;
        $response['theme'] = $this->theme;

        return $response;
    }

    /**
     * Collects information about the active AIO shipping methods in a format used by the widget
     *
     * @access public
     * @param object(WC_Shipping_Method) $method_definition
     * @param array $package
     * @return object(stdClass)
     */
    private function process_shipping_method($method_definition, $package) {
        global $vc_aino_heplers;

        $method = new \stdClass();
        $method->title = $method_definition->title;
        $method->type = $method_definition->vc_aino_type;

        if(is_null($price = $this->helpers->get_method_price($method_definition, $package))){
            return false;
        }
        
        $taxes = get_option('woocommerce_calc_taxes')=='yes' && $method_definition->tax_status=='taxable' ? array_sum($this->helpers->get_new_tax($price, array_keys(WC()->cart->shipping_taxes))) : 0;
        $method->price = number_format($price+$taxes, '2', '.', ' ');
        
        $options = $method_definition->get_options();

        if(!empty($options['deliveryDetails'])){
            $method->deliveryDetails = $options['deliveryDetails'];

            $instance_options = get_option($method_definition->id . '_rates[' . $method_definition->instance_id . ']');

            $option_labels = $instance_options['option_labels'];
            $enableWeather = !empty($instance_options['enableWeather']) ? $instance_options['enableWeather'] : false;
            $disable_options = !empty($instance_options['disable_options']) ? $instance_options['disable_options'] : false;

            if(!empty($method->deliveryDetails['type'])){
                foreach($method->deliveryDetails['type'] as $key => $type){
                    if(is_null($price = $this->helpers->get_cost_for_type($type['id'], $method_definition->id, $method_definition->instance_id))){
                        unset($method->deliveryDetails['type'][$key]);
                        continue;
                    }
                    $taxes = get_option('woocommerce_calc_taxes')=='yes' && $method_definition->tax_status=='taxable' ? array_sum($this->helpers->get_new_tax($price, array_keys(WC()->cart->shipping_taxes))) : 0;
                    $cost = number_format($price+$taxes, '2', '.', ' ');

                    $method->deliveryDetails['type'][$key]['addedPrice'] = number_format($cost, '2', '.', ' ');
                    $method->deliveryDetails['type'][$key]['name'] = $option_labels[$type['id']];
                    if(!empty($enableWeather) && !empty($enableWeather[$type['id']])){
                        $method->deliveryDetails['type'][$key]['enableWeather'] = true;
                    }
                    if(!empty($disable_options)){
                        $method->deliveryDetails['type'][$key]['location'] = array();
                    }
                }

                if(!count($method->deliveryDetails['type'])){
                    return false;
                }
            }

            $method->deliveryDetails['type'] = $this->reindex_delivery($method->deliveryDetails['type']);
        }
        $method->description = $method_definition->instance_settings['description'];
        $method->description2 = $method_definition->instance_settings['description2'];
        $method->carrierServiceCode = '';

        return $method;
    }

    /**
     * Loops over and puts incrementing numbers to the delivery types of the method (as needed by the js widget)
     *
     * @access public
     * @param array $types
     * @return array
     */
    public function reindex_delivery($types){
        $reindexed = array();

        if(!empty($types)){
            foreach($types as $type){
                $reindexed[] = $type;
            }
        }
        return $reindexed;
    }

    /**
     * Processes and returns structures method options
     *
     * @access public
     * @param array $option_definition
     * @return array
     */
    function process_method_option($option_definition) {
        $result = array();

        if (!empty($option_definition['options'])) {
            foreach ($option_definition['options'] as $option_definition) {
                if (empty($option_definition['type'])) {
                    $result[] = array(
                        "name" => !empty($option_definition['key']) ? $option_definition['key'] : $option_definition['name'],
                        "hasSubitems" => false,
                        'subitems' => array()
                    );
                } else {
                    $subitems = array();

                    foreach ($option_definition['options'] as $suboption) {
                        if (empty($suboption['attributes'])) {
                            $name = $suboption['key'];
                        } else {
                            $name = $suboption['value']['key'];
                        }

                        $subitems[] = array(
                            "name" => $name
                        );
                    }

                    $result[] = array(
                        "name" => $option_definition['key'],
                        "hasSubitems" => true,
                        'subitems' => $subitems
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Reads the translations set in the widget language file
     *
     * @access public
     * @return string
     */
    function get_widget_translations() {
        $translations_path = AINO_PLUGIN_PATH . 'core/widget/data/aio.lang.json';

        return json_decode(file_get_contents($translations_path), true);
    }

    /**
     * Returns the translation from the widget language file
     *
     * @access public
     * @param string $translation_code
     * @return string
     */
    function _t($translation_code) {
        return !empty($this->translations[$translation_code][$this->language]) ? $this->translations[$translation_code][$this->language] : '';
    }

}

$vc_aino_widget = Vc_Aino_Widget::get_instance();