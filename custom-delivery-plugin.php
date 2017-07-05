<?php
/*
Plugin Name: Custom delivery plugin
Description: Custom delivery plugin for Woocommerce .
Version: 1.0
Author: Nikita Muhtarov
License: A "Slug" license name e.g. GPL2
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
     function delivery_validate_order( $posted )   {
        $packages = WC()->shipping->get_packages();
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    
        if( is_array( $chosen_methods ) && in_array( 'delivery_shipping_method', $chosen_methods ) ) {
            foreach ( $packages as $i => $package ) {
                if ( $chosen_methods[ $i ] != "delivery_shipping_method" ) {
                    continue;
                }
                
                $weightLimit = 100;
                $weight = 0;
                foreach ( $package['contents'] as $item_id => $values )
                {
                    $_product = $values['data'];
                    $weight = $weight + $_product->get_weight() * $values['quantity'];
                }
                $weight = wc_get_weight( $weight, 'kg' );
                if( $weight > $weightLimit ) {
                    $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'tutsplus' ), $weight, $weightLimit, $TutsPlus_Shipping_Method->title );
                    $messageType = "error";
                    if( ! wc_has_notice( $message, $messageType ) ) {
                        wc_add_notice( $message, $messageType );
                    }
                }
            }
        }
    }
    add_action( 'woocommerce_review_order_before_cart_contents', 'delivery_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'delivery_validate_order' , 10 );
    // Создайте функцию для размещения своего класса
    function delivery_shipping_method_init() {
        require_once dirname(__FILE__) . '/WC_Delivery_Shipping_Method.php';
    }
    add_action( 'woocommerce_shipping_init', 'delivery_shipping_method_init' );
    function add_delivery_shipping_method( $methods ) {
        $methods['delivery_shipping_method'] = 'WC_Delivery_Shipping_Method';
        return $methods;
    }
    add_filter( 'woocommerce_shipping_methods', 'add_delivery_shipping_method' );

} else {
	function activation(){
        deactivate_plugins('custom-shipping-plugin/custom-delivery-plugin.php');
        wp_die('Error wordpress');
    }
    function maybe_display_admin_notices () {
        echo '<div class="error fade"><p>Error woocommerce</p></div>' . "\n";
    }
    register_activation_hook( __FILE__, 'activation' );
    add_action( 'admin_notices', 'maybe_display_admin_notices'  );
      
}