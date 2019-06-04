<?php
/**
 * Plugin Name: Paid Member Subscriptions - Stripe Payment Gateway
 * Plugin URI: http://www.cozmoslabs.com/
 * Description: Accept credit and debit card payments through Stripe
 * Version: 1.2.4
 * Author: Cozmoslabs, Mihai Iova
 * Author URI: http://www.cozmoslabs.com/
 * Text Domain: paid-member-subscriptions
 * License: GPL2
 *
 * == Copyright ==
 * Copyright 2015 Cozmoslabs (www.cozmoslabs.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) ) return;

Class PMS_Stripe {

    /**
     * Constructor
     *
     */
    public function __construct() {

        define( 'PMS_STRIPE_VERSION', '1.2.4' );
        define( 'PMS_STRIPE_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
        define( 'PMS_STRIPE_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

        $this->load_dependencies();
        $this->init();

    }

    private function init() {

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_end_scripts' ) );

    }


    /**
     * Load needed files
     *
     */
    private function load_dependencies() {

        // Stripe Library
        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'libs/stripe/init.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'libs/stripe/init.php';

        // Admin page
        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/admin/functions-admin-pages.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/admin/functions-admin-pages.php';

        // Gateway class and gateway functions
        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/functions.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/functions.php';

        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/functions-actions.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/functions-actions.php';

        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/functions-filters.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/functions-filters.php';

        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/class-payment-gateway-stripe-legacy.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/class-payment-gateway-stripe-legacy.php';

        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/class-payment-gateway-stripe.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'includes/class-payment-gateway-stripe.php';

        //Compatibility files with PB
        if( file_exists( PMS_STRIPE_PLUGIN_DIR_PATH . 'extend/functions-pb-redirect.php' ) )
            include PMS_STRIPE_PLUGIN_DIR_PATH . 'extend/functions-pb-redirect.php';

    }

    /**
     * Enqueue front-end scripts and styles
     *
     */
    public function enqueue_front_end_scripts() {

        wp_enqueue_script( 'pms-stripe-js', 'https://js.stripe.com/v2/', array( 'jquery' ) );

        wp_enqueue_style( 'pms-stripe-style', PMS_STRIPE_PLUGIN_DIR_URL . 'assets/css/pms-stripe.css', array(), PMS_STRIPE_VERSION );
        wp_enqueue_script( 'pms-stripe-script', PMS_STRIPE_PLUGIN_DIR_URL . 'assets/js/front-end.js', array('jquery'), PMS_STRIPE_VERSION );

    }

}

// Let's get this party started
new PMS_Stripe;


if( class_exists( 'pms_PluginUpdateChecker' ) ) {
    $slug = 'stripe';
    $localSerial = get_option( $slug . '_serial_number');
    $pms_stripe_update = new pms_PluginUpdateChecker('http://updatemetadata.cozmoslabs.com/?localSerialNumber=' . $localSerial . '&uniqueproduct=CLPMSSTP', __FILE__, $slug );
}
