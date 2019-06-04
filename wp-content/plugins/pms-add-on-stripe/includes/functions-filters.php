<?php

    // Exit if accessed directly
    if( ! defined( 'ABSPATH' ) ) exit;

    // Return if PMS is not active
    if( ! defined( 'PMS_VERSION' ) ) return;

    /**
     * Add Stripe to the payment gateways array
     *
     * @param array $payment_gateways
     *
     */
    function pms_payment_gateways_stripe( $payment_gateways = array() ) {

        $payment_gateways['stripe'] = array(
            'display_name_user'  => __( 'Credit / Debit Card', 'paid-member-subscriptions' ),
            'display_name_admin' => 'Stripe',
            'class_name'         => 'PMS_Payment_Gateway_Stripe'
        );

        return $payment_gateways;

    }
    add_filter( 'pms_payment_gateways', 'pms_payment_gateways_stripe' );


    /*
     * Add payment types for Stripe
     */
    function pms_payment_types_stripe( $types ) {

        $types['stripe_card_one_time']              = __( 'Card - One Time', 'paid-member-subscriptions' );
        $types['stripe_card_subscription_payment']  = __( 'Subscription Recurring Payment', 'paid-member-subscriptions' );

        return $types;

    }
    add_filter( 'pms_payment_types', 'pms_payment_types_stripe' );


    /**
     * Add data-type="credit_card" attribute to the pay_gate hidden and radio input for Stripe
     *
     */
    function pms_payment_gateway_input_data_type_stripe( $value, $payment_gateway ) {

        if( $payment_gateway == 'stripe' ) {
            $value = str_replace( '/>', 'data-type="credit_card" />', $value );
        }

        return $value;

    }
    add_filter( 'pms_output_payment_gateway_input_radio', 'pms_payment_gateway_input_data_type_stripe', 10, 2 );
    add_filter( 'pms_output_payment_gateway_input_hidden', 'pms_payment_gateway_input_data_type_stripe', 10, 2 );


    /**
     * Hooks to 'pms_confirm_cancel_subscription' from PMS to change the default value provided
     * Makes an api call to Stripe to cancel the subscription, if is successful returns true,
     * but if not returns an array with 'error'
     *
     * @param bool $confirmation
     * @param int $user_id
     * @param int $subscription_plan_id
     *
     * @return mixed    - bool true if successful, array if not
     *
     */
    function pms_stripe_confirm_cancel_subscription( $confirmation, $user_id, $subscription_plan_id ) {

        // Get payment_profile_id
        $payment_profile_id = pms_member_get_payment_profile_id( $user_id, $subscription_plan_id );

        // Continue only if the profile id is a PayPal one
        if( !pms_is_stripe_payment_profile_id($payment_profile_id) )
            return $confirmation;

        // Instantiate the payment gateway with data
        $payment_data = array(
            'user_data' => array(
                'user_id'       => $user_id,
                'subscription'  => pms_get_subscription_plan( $subscription_plan_id )
            )
        );

        $stripe_gate = pms_get_payment_gateway( 'stripe', $payment_data );

        // Cancel the subscription and return the value
        $confirmation = $stripe_gate->cancel_subscription( $payment_profile_id );

        if( !$confirmation )
            $confirmation = array( 'error' => __( 'Something went wrong.', 'paid-member-subscriptions' ) );

        return $confirmation;

    }
    add_filter( 'pms_confirm_cancel_subscription', 'pms_stripe_confirm_cancel_subscription', 10, 3 );


    /**
     * Function that outputs the automatic renewal option in the front-end for the user/customer to see
     * This function was deprecated due to moving the functionality to the core of Paid Member Subscriptions
     *
     * @deprecated 1.2.0
     *
     */
    if( ! function_exists( 'pms_ppsrp_renewal_option' ) && ! function_exists( 'pms_renewal_option_field' ) ) {

        function pms_renewal_option_field( $output, $include, $exclude_id_group, $member, $pms_settings ) {

            // Get all subscription plans
            if( empty( $include ) )
                $subscription_plans = pms_get_subscription_plans();
            else {
                if( !is_object( $include[0] ) )
                    $subscription_plans = pms_get_subscription_plans( true, $include );
                else
                    $subscription_plans = $include;
            }

            // Calculate the amount for all subscription plans
            $amount = 0;
            foreach( $subscription_plans as $subscription_plan ) {
                $amount += $subscription_plan->price;
            }

            if( ! $member && isset( $pms_settings['payments']['recurring'] ) && $pms_settings['payments']['recurring'] == 1 && $amount != 0 ) {
                $output .= '<div class="pms-subscription-plan-auto-renew">';
                $output .= '<label><input name="pms_recurring" type="checkbox" value="1" ' . ( isset( $_REQUEST['pms_recurring'] ) ? 'checked="checked"' : '' ) . ' />' . apply_filters( 'pms_auto_renew_label', __( 'Automatically renew subscription', 'pmstxt' ) ) . '</label>';
                $output .= '</div>';

            }

            return $output;

        }
        //add_filter( 'pms_output_subscription_plans', 'pms_renewal_option_field', 20, 5 );

    }


    /**
     * Function that adds the recurring info to the payment data
     * This function was deprecated due to moving the functionality to the core of Paid Member Subscriptions
     *
     * @deprecated 1.2.0
     *
     */
    if( ! function_exists( 'pms_recurring_register_payment_data' ) ) {

        function pms_recurring_register_payment_data( $payment_data, $payments_settings ) {

            // Unlimited plans cannot be recurring
            if( $payment_data['user_data']['subscription']->duration == 0 )
                return $payment_data;

            if( (isset( $_POST['pms_recurring'] ) && $_POST['pms_recurring'] == 1) || ( isset( $payments_settings['recurring'] ) && $payments_settings['recurring'] == 2 ) ) {
                $payment_data['recurring'] = 1;
            } else {
                $payment_data['recurring'] = 0;
            }

            return $payment_data;

        }
        //add_filter( 'pms_register_payment_data', 'pms_recurring_register_payment_data', 10, 2 );
    }