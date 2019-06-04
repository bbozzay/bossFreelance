<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) )
    return;


/**
 * Add tab for Invoices under PMS Settings page
 *
 * @param array $pms_tabs The PMS Settings tabs
 *
 * @return array
 *
 */
function pms_inv_add_invoices_tab( $pms_tabs ) {

    $pms_tabs['invoices'] = __( 'Invoices', 'paid-member-subscriptions' );

    return $pms_tabs;
}
add_filter( 'pms-settings-page_tabs', 'pms_inv_add_invoices_tab' );


/**
 * Add content for Invoices tab
 *
 * @param array $options The PMS settings options
 *
 */
function pms_inv_add_invoices_tab_content( $options ) {

    // Get active tab
    $active_tab = ( isset( $_REQUEST['nav_tab'] ) ? trim( $_REQUEST['nav_tab'] ) : 'general' );

    // Output Invoices tab content
    include_once( 'views/view-settings-tab-invoices.php' );

}
add_action( 'pms-settings-page_after_tabs', 'pms_inv_add_invoices_tab_content' );


/**
 * Sanitize PMS Invoices settings
 *
 * @param array $options The PMS settings options
 *
 * @return array
 *
 */
function pms_inv_sanitize_settings( $options ) {

    // Invoice Details
    if( empty( $options['invoices']['company_details'] ) ) {
        add_settings_error('general', 'invoices_company_details', __('Company Details are required in order to create invoices.', 'paid-member-subscriptions'), 'error');
    }

    if( isset( $options['invoices']['company_details'] ) ) {
        $options['invoices']['company_details'] = wp_kses_post( $options['invoices']['company_details'] );
    }

    if( isset( $options['invoices']['notes'] ) ) {
        $options['invoices']['notes'] =  wp_kses_post( $options['invoices']['notes'] );
    }

    // Invoice Settings
    if( isset( $options['invoices']['title'] ) ) {

        if ( !empty( $options['invoices']['title'] ) ) {
            $options['invoices']['title'] = sanitize_text_field( $options['invoices']['title'] );
        } else {
            $options['invoices']['title'] = __( 'Invoice', 'paid-member-subscriptions' );
        }

    }

    if( isset( $options['invoices']['format'] ) ) {

        // {{number}} tag is required in invoice format
        if ( strpos( $options['invoices']['format'], '{{number}}' ) === false ) {
            add_settings_error('general', 'invoices_format', __('The {{number}} tag is required under Format.', 'paid-member-subscriptions'), 'error');
            $options['invoices']['format'] = '{{number}}';
        } else {
            $options['invoices']['format'] = sanitize_text_field($options['invoices']['format']);
        }

    }

    // Remove Reset Invoice Counter
    if( isset( $options['invoices']['reset_invoice_counter'] ) ) {
        unset( $options['invoices']['reset_invoice_counter'] );
    }

    if( isset( $options['invoices']['next_invoice_number'] ) ) {
        $options['invoices']['next_invoice_number'] =  (int)$options['invoices']['next_invoice_number'];
    }

    if( isset( $options['invoices']['reset_yearly'] ) ) {
        $options['invoices']['reset_yearly'] =  (int)$options['invoices']['reset_yearly'];
    }

    return $options;

}
add_filter( 'pms_sanitize_settings', 'pms_inv_sanitize_settings' );