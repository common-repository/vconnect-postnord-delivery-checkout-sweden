<?php

class VC_Aino_License {

    /*--------------------------------------------*
     * Attributes
     *--------------------------------------------*/

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

        add_action('admin_notices', array($this, 'license_admin_notice'), 5000);
    }

    /**
     * Adds a notice if the plugin is not registered with the vConnect license system
     *
     * @access public
     * @return void
     */
    public function license_admin_notice() {
        if (get_option('vc_aino_license_status') != 1) {
            $class = 'error';
            $license_info = get_option('vc_aino_license_info');
            $message = !empty($license_info) ? $license_info : 'You need to register in order to use PostNord All In One Module';
            ?>
            <div class="<?php echo $class; ?>">
                <p><?php _e($message, 'my-text-domain'); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Process license function
     *
     * @access public
     * @param string $license_email
     * @param string $license_key
     * @param string $api_key
     * @return void
     */
    public function process_license($license_email, $license_key, $api_key) {

        $license_status = get_option('vc_aino_license_status');
        $license_info = get_option('vc_aino_license_info');

        if ($license_status != 1) {
            if (strlen($license_key) == '20') {
                $license_system_path = "api.vconnect.dk/v1/licenses/activate";
                $url = $license_system_path . "?license_key=" . $license_key . "&consumerId=" . $api_key . "&email=" . $license_email . "&ip=" . $_SERVER["REMOTE_ADDR"] . "&domain=" . $_SERVER['HTTP_HOST'];

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $data = json_decode(curl_exec($curl));
                curl_close($curl);

                list($license_status, $license_info) = $data ? $this->process_license_data($data) : array(0, 'System Error');
            } else {
                $license_status = 0;
                $license_info = 'Invalid license key';
            }

            update_option('vc_aino_license_status', $license_status);
            update_option('vc_aino_license_info', $license_info);
        }
    }

    /**
     * Process license data
     *
     * @access private
     * @param object $data
     * @return array
     */
    private function process_license_data($data) {

        if (isset($data->error)) {
            if ($data->error == '2000' || $data->error == '4003' || $data->error == '4001') {
                $license_status = 1;
                $license_info = 'Your license is valid';
            } else {
                $license_status = 0;
                $license_info = $data->description;
            }
        } else {
            $license_status = 1;
            $license_info = 'Your license is valid';
        }

        return array($license_status, $license_info);
    }

}


$vc_aino_license = VC_Aino_License::get_instance();