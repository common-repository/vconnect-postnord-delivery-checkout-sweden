<?php

if (!class_exists('WC_VC_EU_Dpdclassic')) {

    class WC_VC_EU_Dpdclassic extends Vc_Aino_Shipping_Method {

        // Id for your shipping method. Should be unique.
        public $vc_aino_id = 'vconnect_postnord_eu_dpdclassic';
        // Title shown in admin
        public $vc_aino_type = 'euDelivery';
        // Title shown in admin
        protected $vc_aino_method_title = 'Europeisk leverans';
        // Description shown in admin
        protected $vc_aino_method_description = 'Description of your shipping method';
        // This can be added as an setting but for this example its forced.
        protected $vc_aino_title = 'International shipping - EU';

    }

}