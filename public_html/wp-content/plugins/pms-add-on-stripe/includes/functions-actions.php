<?php

    // Exit if accessed directly
    if( ! defined( 'ABSPATH' ) ) exit;

    // Return if PMS is not active
    if( ! defined( 'PMS_VERSION' ) ) return;

    /**
     * Display a warning to the administrators if the API credentials are missing in the
     * register page
     *
     */
    function pms_stripe_api_credentials_admin_warning() {

        if( !current_user_can( 'manage_options' ) )
            return;

        $are_active = array_intersect( array( 'stripe' ), pms_get_active_payment_gateways() );

        if( pms_get_stripe_api_credentials() == false && !empty( $are_active ) ) {

            echo '<div class="pms-warning-message-wrapper">';
                echo '<p>' . sprintf( __( 'Your Stripe API settings are missing. In order to make payments you will need to add your API credentials %1$s here %2$s.', 'paid-member-subscriptions' ), '<a href="' . admin_url( 'admin.php?page=pms-settings-page&nav_tab=payments#pms-settings-payment-gateways' ) .'" target="_blank">', '</a>' ) . '</p>';
                echo '<p><em>' . __( 'This message is visible only by Administrators.', 'paid-member-subscriptions' ) . '</em></p>';
            echo '</div>';

        }

    }
    add_action( 'pms_register_form_top', 'pms_stripe_api_credentials_admin_warning' );
    add_action( 'pms_new_subscription_form_top', 'pms_stripe_api_credentials_admin_warning' );
    add_action( 'pms_upgrade_subscription_form_top', 'pms_stripe_api_credentials_admin_warning' );
    add_action( 'pms_renew_subscription_form_top', 'pms_stripe_api_credentials_admin_warning' );
    add_action( 'pms_retry_payment_form_top', 'pms_stripe_api_credentials_admin_warning' );


    /**
     * Cancel Stripe subscription before the user upgrades the subscription
     *
     */
    function pms_stripe_cancel_subscription_before_upgrade( $member_subscription_id, $payment_data ) {

        $user_id = $payment_data['user_id'];

        // Get payment_profile_id
        $payment_profile_id = pms_member_get_payment_profile_id( $user_id, $member_subscription_id );

        // Continue only if the profile id is a PayPal one
        if( !pms_is_stripe_payment_profile_id($payment_profile_id) )
            return;

        // Instantiate the payment gateway with data
        $payment_data = array(
            'user_data' => array(
                'user_id'       => $user_id,
                'subscription'  => pms_get_subscription_plan( $member_subscription_id )
            )
        );

        $stripe_gate = pms_get_payment_gateway( 'stripe', $payment_data );

        // Cancel the subscription and return the value
        $confirmation = $stripe_gate->cancel_subscription( $payment_profile_id );

    }
    add_action( 'pms_stripe_before_upgrade_subscription', 'pms_stripe_cancel_subscription_before_upgrade', 10, 2 );