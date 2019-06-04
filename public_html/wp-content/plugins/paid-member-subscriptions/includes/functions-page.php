<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the ID or URL of a page associated with PMS actions.
 *
 * @param  string $page Can be: login, account, register and lost-password
 * @param  bool   $url  Return URL or ID
 * @return mixed        ID or URL of the page.
 */
function pms_get_page( $page, $url = false ) {
    $settings = get_option( 'pms_general_settings' );

    //replace "-" with "_" and append "_page" at the end to match the keys from our settings
    $page = str_replace( '-', '_', $page ) . '_page';

    if ( !isset( $settings[$page] ) || $settings[$page] == '-1' )
        return false;
    else {
        if ( $url )
            return get_permalink( $settings[$page] );
        else
            return $settings[$page];
    }
}

/**
 * Given a subscription plan ID, generate an URL to retry the payment
 *
 * @since  1.8.5
 * @param  int     $plan_id  Subscription Plan ID
 * @return string            Retry Payment URL
 */
function pms_get_retry_payment_url( $plan_id ) {

    if ( !( $account_page = pms_get_page( 'account', true ) ) )
        return false;

    $url = wp_nonce_url( add_query_arg( array( 'pms-action' => 'retry_payment_subscription', 'subscription_plan' => $plan_id ), $account_page ), 'pms_retry_payment_subscription', 'pmstkn' );

    return apply_filters( 'pms_get_retry_payment_url', esc_url( $url ) );

}

/**
 * Get the URL of an account page tab, processed based on current permalinks settings.
 *
 * @param  string   $tab       Tab slug.
 * @param  string   $permalink Page permalink.
 * @return string              Processed permalink with the correct tab added.
 */
function pms_account_get_tab_url( $tab, $permalink ) {

    if ( get_option( 'permalink_structure' ) && pms_get_page( 'account' ) !== false && !apply_filters( 'pms_account_rewrite_tab_urls', false ) ) {
        if ( strstr( $permalink, '?' ) ) {
            $query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
            $permalink    = current( explode( '?', $permalink ) );
        } else
            $query_string = '';

        $url = trailingslashit( $permalink ) . trailingslashit( $tab );

        $url .= $query_string;
    } else
        $url = add_query_arg( 'tab', $tab );

    return apply_filters( 'pms_account_get_tab_url', $url, $tab );

}

/**
 * Add query vars.
 *
 * @param   array  $vars
 * @return  array
 */
function pms_query_vars( $vars ) {
    $vars[] = 'tab';

    return $vars;
}
add_filter( 'query_vars', 'pms_query_vars', 10 );

//Account page needs the rewrite so we have pretty permalinks for tabs
if ( pms_get_page( 'account' ) ) {
    add_action( 'init', 'pms_rewrite_rule', 10 );
    add_action( 'wp_loaded', 'pms_flush_rewrite_rules' );
}

/**
 * Add new rewrite rule.
 *
 * @return void
 */
function pms_rewrite_rule() {

    if ( apply_filters( 'pms_account_rewrite_tab_urls', false ) )
        return;

    $page = pms_get_page( 'account' );
    $page_slug = get_post_field( 'post_name', $page );

    add_rewrite_rule( '^'.$page_slug.'/([^/]*)/page/?([0-9]{1,})/?$','index.php?page_id='.$page.'&tab=$matches[1]&paged=$matches[2]', 'top' );
    add_rewrite_rule( '(.?.+?)/'.$page_slug.'(/(.*))/page/?([0-9]{1,})/?$','index.php?page_id='.$page.'&tab=$matches[3]&paged=$matches[4]', 'top' );
    add_rewrite_rule( '^'.$page_slug.'/([^/]*)/?','index.php?page_id='.$page.'&tab=$matches[1]', 'top' );
    add_rewrite_rule( '(.?.+?)/'.$page_slug.'(/(.*))?/?$','index.php?page_id='.$page.'&tab=$matches[3]', 'top' );

}

/**
 * Flush rewrite rules if our rule is not set.
 *
 * @return void
 */
function pms_flush_rewrite_rules() {

    if ( apply_filters( 'pms_account_rewrite_tab_urls', false ) )
        return;

	$rules     = get_option( 'rewrite_rules' );
    $page_slug = get_post_field( 'post_name', pms_get_page( 'account' ) );

	if ( !isset( $rules['(.?.+?)/'.$page_slug.'(/(.*))?/?$'] ) || !isset( $rules['^'.$page_slug.'/([^/]*)/?'] ) ) {
		global $wp_rewrite;

		$wp_rewrite->flush_rules();
	}
}
