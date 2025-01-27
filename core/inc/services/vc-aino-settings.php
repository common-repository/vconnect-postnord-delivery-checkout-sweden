<?php

class Vc_Aino_Settings {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50);
        add_action('woocommerce_settings_tabs_settings_tab_demo', __CLASS__ . '::settings_tab');
        add_action('woocommerce_update_options_settings_tab_demo', __CLASS__ . '::update_settings');
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab($settings_tabs) {
        $settings_tabs['settings_tab_demo'] = __('vConnect AIO Settings', 'woocommerce-settings-tab-demo');
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        woocommerce_admin_fields(self::get_settings());
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @this->process_license()
     */
    public static function update_settings() {
        woocommerce_update_options(self::get_settings());
        global $vc_aino_license;

        $vc_aino_license->process_license(get_option('vc_aino_license_email'), get_option('vc_aino_license_key'), get_option('vc_aino_consumer_id'));
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {
        $settings = array(
            'section_title' => array(
                'name' => __('Section Title', 'woocommerce-settings-tab-demo'),
                'type' => 'title',
                'desc' => '',
                'id' => 'wc_settings_tab_demo_section_title'
            ),
            'license_email' => array(
                'title' => __('vConnect license Email', 'vc-allinone'),
                'type' => 'text',
                'id' => 'vc_aino_license_email',
                'default' => __('', 'vc-allinone'),
                'desc' => __('vConnect license Email', 'vc-allinone'),
            ),
            'license_key' => array(
                'title' => __('vConnect license Key', 'vc-allinone'),
                'type' => 'text',
                'id' => 'vc_aino_license_key',
                'default' => __('', 'vc-allinone'),
                'desc' => __('You need to make an order from <a href="https://vconnect.dk/en/" target="_new">vconnect.dk/en</a> and you will receive an email with "vConnect license Key" attached', 'vc-allinone'),
            ),
            'consumer_id' => array(
                'title' => __('PostNord API consumer ID', 'vc-allinone'),
                'type' => 'text',
                'id' => 'vc_aino_consumer_id',
                'default' => '',
                'desc' => __('You can get one from <a href="https://developer.postnord.com/login" target="_new">developer.postnord.com/login</a>', 'vc-allinone'),
            ),
            'maps_api_key' => array(
                'title' => __('Google Maps API Key', 'vc-allinone'),
                'type' => 'text',
                'id' => 'vc_aino_gmaps_key',
                'default' => '',
                'desc' =>  __('You can get one from <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_new">here</a>', 'vc-allinone'),
            ),
            'widget_description' => array(
                'title' => __('Widget Description', 'vc-allinone'),
                'type' => 'text',
                'id' => 'vc_aino_widget_description',
                'default' => 'PostNord',
                'desc_tip' => __('Widget Description', 'vc-allinone')
            ),
            'custom_styles' => array(
                'title' => __('Custom Styles', 'vc-allinone'),
                'type' => 'textarea',
                'id' => 'vc_aino_custom_styles',
                'default' => '',
                'desc' => __('You can use Ctrl+S, to save the settings without reloading the page', 'vc-allinone')
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_demo_section_end'
            ),
        );
        return apply_filters('wc_settings_tab_demo_settings', $settings);
    }

}

Vc_Aino_Settings::init();