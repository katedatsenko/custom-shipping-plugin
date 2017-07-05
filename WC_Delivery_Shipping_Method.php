<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( ! class_exists( 'WC_Delivery_Shipping_Method' ) ) {
    class WC_Delivery_Shipping_Method extends WC_Shipping_Method
    {
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id                 = 'delivery_shipping_method';
            $this->title       = __( 'Delivery Shipping Method' );
            $this->method_description = __( 'Description of delivery shipping method' ); //

            $this->init();
            $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
            $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Delivery Shipping Method' );
        }
        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init() {
            // Load the settings API
            $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
            $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
            // Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }
        /**
         * Define settings field for this shipping
         * @return void 
         */
        function init_form_fields() { 
            // We will add our settings here
                $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable'),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.'),
                          'default' => 'yes'
                          ),
             
                     'title' => array(
                        'title' => __( 'Title'),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site'),
                          'default' => __( 'Delivery')
                          ),
                     
                     );
        }
        
        /**
         * calculate_shipping function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        public function calculate_shipping( $package = array() ) {
            $country = $package["destination"]["country"];
            $state = $package["destination"]["state"];
            // We will add the cost, rate and logics in here
            global $product;
            $categories = array();
            $product_in_category = false;
            $product_id = array();
            foreach( WC()->cart->get_cart() as $cart_item ){
               $product_id[] = $cart_item["product_id"];
            }
            $product_in_category = false;
            $t_term = get_term_by('slug', 'supplements', 'product_cat');
            $category_id = array($t_term->term_id);
            foreach ($product_id as $prod) {
              $categories = get_the_terms( $prod, 'product_cat' );
                
                if ( ! empty( $categories ) ) {
                    foreach ($categories as $item) {
                        if (in_array((int)$item->term_id, $category_id)) {
                            $product_in_category = true;
                            break;
                        }  
                    }
                }
            } 

            $price = 0;
            $title = $this->title;
            $unavailable = 'Delivery is not available. Some items in your shopping cart are not available for sending to your country.';
            $freeshipping = 'Free shipping';

            global $woocommerce;
            $cart_subtotal = WC()->cart->cart_contents_total;
            switch ($country) {
                case 'CA':
                    if ($state === 'BC') {
                        
                        $rest="no";
                        $str = $package["destination"]["postcode"];
                        
                        if(strlen($str)>=3)
                        {
                            $rest = substr($str, 0, 3);
    
                        }
                        $postcode = array("V3A", "V4A", "V5A", "V6A", "V7A", 
                          "V3B", "V4B", "V5B", "V6B", "V7B", 
                          "V3C", "V4C", "V5C", "V6C", "V7C", 
                          "V3E", "V4E", "V5E", "V6E", "V7E",
                          "V3G", "V4G", "V5G", "V6G", "V7G", 
                          "V3H", "V5H", "V6H", "V7H", 
                          "V3J", "V5J", "V6J", "V7J",
                          "V3K", "V4K", "V5K", "V6K", "V7K", 
                          "V3L", "V4L", "V5L", "V6L", "V7L", 
                          "V1M", "V3M", "V4M", "V5M", "V6M", "V7M",  
                          "V3N", "V4N", "V5N", "V6N", "V7N",  
                          "V2P", "V4P", "V5P", "V6P", "V7P",  
                          "V2R", "V3R", "V4R", "V5R", "V6R", "V7R",
                          "V2S", "V3S", "V4S", "V5S", "V6S", "V7S", 
                          "V2T", "V3T", "V6T", "V7T",
                          "V2V", "V3V", "V5V", "V6V", "V7V", 
                          "V2W", "V3W", "V4W", "V5W", "V6W", "V7W", 
                          "V2X", "V3X", "V4X", "V5X", "V6X", "V7X", 
                          "V2Y", "V3Y", "V5Y", "V6Y", "V7Y", 
                          "V2Z", "V3Z", "V4Z", "V5Z", "V6Z"
                           );
                        /*$postcode = array("V2S", "V3J", "V3N", "V2P", "V3B", "V3C", "V3M", "V4C", "V1M", "V2W", "V2X", "V2V", "V3L", "V7G", " V7H", "V3Y", "V3B", "V3H", "V6P", "V6V", "V6W", "V3R", "V5K", "V7P", "V7S", "V4B", "V4B");*/
                        
                        if (in_array($rest, $postcode )) {
                            if ($cart_subtotal < 150) {
                                $price = 5;
                            }
                            else { 
                                    $price = 0;
                                    $title = $freeshipping;
                                }

                        }
                        else {
                            if ($cart_subtotal < 200) {
                                if ($product_in_category == true) {
                                  $price = 25;
                                } 
                                else {
                                  $price = 15;
                                }
                            } 
                            else  {
                                $price = 0;
                                $title = $freeshipping;
                            }
                        }
                    }
                    else {

                        if ($cart_subtotal < 300) {
                                if ($product_in_category == true) {
                                  $price = 35;
                                }
                                else {
                                  $price = 20;
                                }
                            } 
                            else  {
                                $price = 0;
                                $title = $freeshipping;
                            }
                    }
                    break;
                case 'US':
                  if ($product_in_category == true) {
                    $title = $unavailable;
                  } 
                  else {
                    $price = 25;
                  }
                    break;

                default:
                    if ($product_in_category == true) {
                    $title = $unavailable;
                  } 
                  else {
                    $price = 30;
                  }
                    break;
            }
           


            /*$rate = array(
              'id' => $this->id,
              'label' => $title,
              'cost' => $price,
              'calc_tax' => 'per_item'
            );
            // Register the rate
            $this->add_rate( $rate );*/


            $rate = array(
              'id' => $this->id,
              'label' => $title,
              'cost' => ($price * (1 + WPRSHIP_OVERALL_MARKUP_NON_SINGLE/ 100)),
              'taxes' => '',
              'calc_tax' => 'per_order'
            );
            // Register the rate
            $this->add_rate( $rate );
        }
    }
}