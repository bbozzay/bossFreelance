<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Elementor widget for our pms-account shortcode
 */
class PMS_Elementor_Account_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'pms-account';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Account', 'paid-member-subscriptions' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories.
	 *
	 */
	public function get_categories() {
		return array( 'paid-member-subscriptions' );
	}

	/**
	 * Register widget controls
	 *
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Layout', 'paid-member-subscriptions' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_tabs',
			array(
				'label'   => __( 'Show tabs', 'paid-member-subscriptions' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'yes' => __( 'Yes', 'paid-member-subscriptions' ),
                    'no'  => __( 'No', 'paid-member-subscriptions' )
                ),
                'default' => 'yes',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render widget output in the front-end
	 *
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		echo do_shortcode( '[pms-account show_tabs='.$settings['show_tabs'].']');

	}

}
