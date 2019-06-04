<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Function that return the IP address of the user. Checks for IPs (in order) in: 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
 *
 * @return string
 *
 */
function pms_get_user_ip_address() {

    $ip_address = '';

    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true) {
            foreach ( array_map('trim', explode( ',', $_SERVER[$key]) ) as $ip ) {
                if ( filter_var($ip, FILTER_VALIDATE_IP) !== false ) {
                    return $ip;
                }
            }
        }
    }

    return $ip_address;

}


/**
 * Detect if the current user has concurrent sessions ( multiple logins at the same time )
 *
 * @return bool
 */
function pms_user_has_concurrent_sessions(){

    return ( is_user_logged_in() && count( wp_get_all_sessions() ) > 1 );

}


/**
 * Get the user's current session
 *
 * @return array
 */
function pms_get_current_session(){

    $sessions = WP_Session_Tokens::get_instance( get_current_user_id() );

    return $sessions->get( wp_get_session_token() );

}


/**
 * Allow only one session per user (disable concurrent logins)
 *
 * A newer session will have priority over an old one.
 * If the current user's session has been taken over by a newer session, we will destroy their session automatically and they will have to login again.
 * This will make it annoying for members to share their login credentials.
 *
 */
function pms_disable_concurrent_logins(){

    if ( !pms_user_has_concurrent_sessions() )

        return;

    $user_id = pms_get_current_user_id();

    $newest_session = max( wp_list_pluck( wp_get_all_sessions(), 'login') );

    $session = pms_get_current_session();

    if ( $session['login'] === $newest_session ) {

        // remove other sessions and keep this one
        wp_destroy_other_sessions();

        /**
         * Fires after a user's non-current sessions are destroyed
         *
         * @param int $user_id ID of the affected user
         */
        do_action( 'pms_destroy_other_sessions', $user_id );

    }

}

$pms_settings = get_option( 'pms_general_settings' );

if ( isset( $pms_settings['prevent_account_sharing'] ) && !empty( $pms_settings['prevent_account_sharing'] ) )
    add_action( 'init', 'pms_disable_concurrent_logins' );


/**
 * Redirect users from default WordPress login, register and lost password forms to the corresponding front-end pages created with our plugin.
 * This is done only if these pages are set under Settings -> General -> Membership Pages
 *
 */
function pms_redirect_default_wp_pages(){

    global $pagenow;

    $settings = get_option( 'pms_general_settings' );

    $login_page = ( isset( $settings['login_page'] ) && $settings['login_page'] != -1) ? get_permalink($settings['login_page']) : false;

    $register_page = ( isset( $settings['register_page'] ) && $settings['register_page'] != -1) ? get_permalink($settings['register_page']) : false;

    $lost_password_page =  ( isset( $settings['lost_password_page'] ) && $settings['lost_password_page'] != -1) ? get_permalink($settings['lost_password_page']) : false;

    if( ($pagenow == "wp-login.php") && !isset($_GET['action']) && $login_page ) {

        wp_redirect($login_page);
        exit;
    }

    else if ( ($pagenow == "wp-login.php") && ( isset( $_GET['action'] ) && ( $_GET['action'] == 'register' ) ) && $register_page ) {
        wp_redirect($register_page);
        exit;
    }

    else if ( ($pagenow == "wp-login.php") && ( isset( $_GET['action'] ) && ( $_GET['action'] == 'lostpassword' ) ) && $lost_password_page ) {
        wp_redirect($lost_password_page);
        exit;
    }

}

// make sure "Redirect Default WordPress Pages" option is checked
$pms_settings = get_option( 'pms_general_settings' );

if ( isset( $pms_settings['redirect_default_wp'] ) && !empty( $pms_settings['redirect_default_wp'] ) )
    add_action( 'init', 'pms_redirect_default_wp_pages' );