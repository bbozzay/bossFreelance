<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_Elementor_Widgets {
    private static $_instance = null;

	/**
	 * Register plugin action hooks and filters
	 */
	public function __construct() {
        // Add category
        add_action( 'elementor/elements/categories_registered', array( $this, 'add_category' ) );

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
	}

    /**
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @return PMS_Elementor_Widgets An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

	/**
	 * Include Widgets files
	 */
	private function include_widgets_files() {
		require_once( __DIR__ . '/widgets/class-widget-account.php' );
		require_once( __DIR__ . '/widgets/class-widget-login.php' );
		require_once( __DIR__ . '/widgets/class-widget-recover-password.php' );
		require_once( __DIR__ . '/widgets/class-widget-register.php' );
	}

	/**
	 * Register Widgets
	 */
	public function register_widgets() {
		$this->include_widgets_files();

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Account_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Login_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Recover_Password_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Register_Widget() );
	}

    public function add_category( $elements_manager ) {
        $elements_manager->add_category(
            'paid-member-subscriptions',
            array(
                'title' => __( 'Paid Member Subscriptions Shortcodes', 'paid-member-subscriptions' ),
                'icon'  => 'fa fa-plug',
            )
        );
    }
}

// Instantiate Plugin Class
PMS_Elementor_Widgets::instance();
