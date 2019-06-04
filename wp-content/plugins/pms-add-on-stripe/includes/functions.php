<?php

    // Exit if accessed directly
    if( ! defined( 'ABSPATH' ) ) exit;

    // Return if PMS is not active
    if( ! defined( 'PMS_VERSION' ) ) return;

    /*
     * Return the Stripe API credentials
     *
     */
    if( !function_exists( 'pms_get_stripe_api_credentials' ) ) {

        function pms_get_stripe_api_credentials() {

            $pms_settings = get_option( 'pms_settings', array() );
            $pms_settings = ( !empty( $pms_settings['payments']['gateways']['stripe'] ) ? $pms_settings['payments']['gateways']['stripe'] : '' );

            if( empty( $pms_settings ) )
                return false;

            if( pms_is_payment_test_mode() )
                $sandbox_prefix = 'test_';
            else
                $sandbox_prefix = '';

            $api_credentials = array(
                'secret_key'      => $pms_settings[$sandbox_prefix . 'api_secret_key'],
                'publishable_key' => $pms_settings[$sandbox_prefix . 'api_publishable_key']
            );

            $api_credentials = array_map( 'trim', $api_credentials );

            if( count( array_filter($api_credentials) ) == count($api_credentials) )
                return $api_credentials;
            else
                return false;

        }

    }


    /*
     * Checks whether the value of the payment profile id matches the subscription ids
     * in Stripe
     *
     * @param string $payment_profile_id
     *
     */
    function pms_is_stripe_payment_profile_id( $payment_profile_id ) {

        if( strpos( $payment_profile_id, 'sub_' ) !== false )
            return true;
        else
            return false;

    }
