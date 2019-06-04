<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a log entry when an admin is manually adding a payment.
 */
add_action( 'pms_payment_insert', 'pms_log_manually_added_payments', 20, 2 );
function pms_log_manually_added_payments( $payment_id, $data ) {
    if ( !is_admin() || ( isset( $data['type'] ) && $data['type'] != 'manual_payment' ) )
        return;

    $payment = pms_get_payment( $payment_id );

    if ( empty( $payment->id ) )
        return;

    $user = get_userdata( get_current_user_id() );

    $payment->log_data( 'payment_added', array( 'user' => $user->user_login ) );
}

/**
 * Add a log entry when the status of a payment changes.
 */
add_action( 'pms_payment_update', 'pms_log_payment_status_changes', 20, 3 );
function pms_log_payment_status_changes( $payment_id, $data, $old_data ) {
    if ( !isset( $data['status'] ) || ( isset( $data['status' ] ) && $data['status'] == $old_data['status'] ) )
        return;

    $payment = pms_get_payment( $payment_id );

    if ( empty( $payment->id ) )
        return;

    unset( $old_data['logs'] );

    $payment->log_data( 'status_changed', array( 'user' => get_current_user_id(), 'new_data' => $data, 'old_data' => $old_data ) );
}
