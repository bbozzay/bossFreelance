<?php

    // Exit if accessed directly
    if( ! defined( 'ABSPATH' ) ) exit;

    // Return if PMS is not active
    if( ! defined( 'PMS_VERSION' ) ) return;


    /**
     * Function that adds the HTML for Stripe in the payments tab from the Settings page
     *
     * @param array $options    - The saved option settings
     *
     */
    function pms_add_settings_content_stripe( $options ) {

        // Stripe API fields
        $fields = array(
            'test_api_publishable_key' => array(
                'label' => __( 'Test Publishable Key', 'paid-member-subscriptions' )
            ),
            'test_api_secret_key' => array(
                'label' => __( 'Test Secret Key', 'paid-member-subscriptions' )
            ),
            'api_publishable_key' => array(
                'label' => __( 'Live Publishable Key', 'paid-member-subscriptions' )
            ),
            'api_secret_key' => array(
                'label' => __( 'Live Secret Key', 'paid-member-subscriptions' )
            )
        );

        echo '<div class="pms-payment-gateway-wrapper">';

            echo '<h4 class="pms-payment-gateway-title">' . __( 'Stripe', 'paid-member-subscriptions' ) . '</h4>';

            foreach( $fields as $field_slug => $field_options ) {
                echo '<div class="pms-form-field-wrapper">';

                echo '<label class="pms-form-field-label" for="stripe-' . str_replace( '_', '-', $field_slug ) . '">' . $field_options['label'] . '</label>';
                echo '<input id="stripe-' . str_replace( '_', '-', $field_slug ) . '" type="text" name="pms_settings[payments][gateways][stripe][' . $field_slug . ']" value="' . ( isset( $options['payments']['gateways']['stripe'][$field_slug] ) ? $options['payments']['gateways']['stripe'][$field_slug] : '' ) . '" class="widefat" />';

                if( isset( $field_options['desc'] ) )
                    echo '<p class="description">' . $field_options['desc'] . '</p>';

                echo '</div>';
            }

            do_action( 'pms_settings_page_payment_gateway_stripe_extra_fields', $options );

        echo '</div>';


    }
    add_action( 'pms-settings-page_payment_gateways_content', 'pms_add_settings_content_stripe' );


    /**
     * Adds extra fields for the member's subscription in the add new / edit subscription screen
     *
     * @param int    $subscription_id      - the id of the current subscription's edit screen. 0 for add new screen.
     * @param string $gateway_slug
     * @param array  $gateway_details
     *
     */
    function pms_stripe_add_payment_gateway_admin_subscription_fields( $subscription_id = 0, $gateway_slug = '', $gateway_details = array() ) {

        if( empty( $gateway_slug ) || empty( $gateway_details ) )
            return;

        if( ! function_exists( 'pms_get_member_subscription_meta' ) )
            return;

        if( $gateway_slug != 'stripe' )
            return;

        // Set card id value
        $stripe_customer_id = ( ! empty( $subscription_id ) ? pms_get_member_subscription_meta( $subscription_id, '_stripe_customer_id', true ) : '' );
        $stripe_customer_id = ( ! empty( $_POST['_stripe_customer_id'] ) ? $_POST['_stripe_customer_id'] : $stripe_customer_id );

        // Set card id value
        $stripe_card_id = ( ! empty( $subscription_id ) ? pms_get_member_subscription_meta( $subscription_id, '_stripe_card_id', true ) : '' );
        $stripe_card_id = ( ! empty( $_POST['_stripe_card_id'] ) ? $_POST['_stripe_card_id'] : $stripe_card_id );

        // Stripe Customer ID
        echo '<div class="pms-meta-box-field-wrapper">';

            echo '<label for="pms-subscription-stripe-customer-id" class="pms-meta-box-field-label">' . __( 'Stripe Customer ID', 'paid-member-subscriptions' ) . '</label>';
            echo '<input id="pms-subscription-stripe-customer-id" type="text" name="_stripe_customer_id" class="pms-subscription-field" value="' . esc_attr( $stripe_customer_id ) . '" />';

        echo '</div>';

        // Stripe Card ID
        echo '<div class="pms-meta-box-field-wrapper">';

            echo '<label for="pms-subscription-stripe-card-id" class="pms-meta-box-field-label">' . __( 'Stripe Card ID', 'paid-member-subscriptions' ) . '</label>';
            echo '<input id="pms-subscription-stripe-card-id" type="text" name="_stripe_card_id" class="pms-subscription-field" value="' . esc_attr( $stripe_card_id ) . '" />';

        echo '</div>';

    }
    add_action( 'pms_view_add_new_edit_subscription_payment_gateway_extra', 'pms_stripe_add_payment_gateway_admin_subscription_fields', 10, 3 );


    /**
     * Checks to see if data from the extra subscription fields is valid
     *
     * @param array $admin_notices
     *
     * @return array
     *
     */
    function pms_stripe_validate_subscription_data_admin_fields( $admin_notices = array() ) {

        // Validate the customer id
        if( ! empty( $_POST['_stripe_customer_id'] ) ) {

            if( false === strpos( $_POST['_stripe_customer_id'], 'cus_' ) )
                $admin_notices[] = array( 'error' => __( 'The provided Stripe Customer ID is not valid.', 'paid-member-subscriptions' ) );

        }

        // Validate the card id
        if( ! empty( $_POST['_stripe_card_id'] ) ) {

            if( false === strpos( $_POST['_stripe_card_id'], 'card_' ) )
                $admin_notices[] = array( 'error' => __( 'The provided Stripe Card ID is not valid.', 'paid-member-subscriptions' ) );

        }

        return $admin_notices;

    }
    add_filter( 'pms_submenu_page_members_validate_subscription_data', 'pms_stripe_validate_subscription_data_admin_fields' );


    /**
     * Saves the values for the payment gateway subscription extra fields
     *
     * @param int $subscription_id
     *
     */
    function pms_stripe_save_payment_gateway_admin_subscription_fields( $subscription_id = 0 ) {

        if( ! function_exists( 'pms_update_member_subscription_meta' ) )
            return;

        if( $subscription_id == 0 )
            return;

        if( ! is_admin() )
            return;

        if( ! current_user_can( 'manage_options' ) )
            return;

        if( empty( $_POST['payment_gateway'] ) || $_POST['payment_gateway'] !== 'stripe' )
            return;

        // Update the customer id
        if( isset( $_POST['_stripe_customer_id'] ) )
            pms_update_member_subscription_meta( $subscription_id, '_stripe_customer_id', sanitize_text_field( $_POST['_stripe_customer_id'] ) );

        // Update the card id
        if( isset( $_POST['_stripe_card_id'] ) )
            pms_update_member_subscription_meta( $subscription_id, '_stripe_card_id', sanitize_text_field( $_POST['_stripe_card_id'] ) );

    }
    add_action( 'pms_member_subscription_inserted', 'pms_stripe_save_payment_gateway_admin_subscription_fields' );
    add_action( 'pms_member_subscription_updated', 'pms_stripe_save_payment_gateway_admin_subscription_fields' );