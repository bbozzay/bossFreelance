<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Return if PMS is not active
if( ! defined( 'PMS_VERSION' ) )
    return;

Class PMS_PDF_Invoice extends TCPDF {

	/**
	 * Header
	 *
	 * Overwrites the default header of the parent class
	 *
	 */
	public function Header() {

	}

	/**
	 * Footer
	 *
	 * Outputs the footer notes message configured in the Settings on all invoices
	 *
	 */
	public function Footer() {

		$settings = get_option( 'pms_settings', array() );

		if( ! empty( $settings['invoices']['notes'] ) ) {

			$this->SetY( -15 );
			$this->SetFontSize( 10 );
			$this->writeHTMLCell( $this->getPageWidth() - 16 , 15, '', '', wpautop( $settings['invoices']['notes'] ) );

		}

	}

}