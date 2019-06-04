<?php
/**
 * HTML Output for the PMS Settings page -> Invoices tab
 */

$invoice_number = get_option( 'pms_inv_invoice_number', '1' );

?>

<div id="pms-settings-invoices" class="pms-tab <?php echo ( $active_tab == 'invoices' ? 'tab-active' : '' ); ?>">

    <?php do_action( 'pms-settings-page_tab_invoices_before_content', $options ); ?>

    <div id="invoice-details">

        <h3><?php echo __( 'Invoice Details', 'paid-member-subscriptions' ); ?></h3>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-company-details"><?php echo __( 'Company Details', 'paid-member-subscriptions' ) ?></label>
            <?php wp_editor( ( isset($options['invoices']['company_details']) ? wp_kses_post($options['invoices']['company_details']) : '' ), 'invoices-company-details', array( 'textarea_name' => 'pms_settings[invoices][company_details]', 'editor_height' => 150 ) ); ?>
            <p class="description"> <?php echo __('Enter your company details as you would like them to appear on the invoice. ( Company Name, Address, Country, etc.) <br/> <strong>Note: Company details are required to create invoices.</strong>','paid-member-subscriptions','paid-member-subscriptions') ?></p>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-notes"><?php echo __( 'Invoice Notes', 'paid-member-subscriptions' ) ?></label>
            <?php wp_editor( ( isset($options['invoices']['notes']) ? wp_kses_post($options['invoices']['notes']) : __( 'Thank you for your business!' ,'paid-member-subscriptions') ), 'invoices-notes', array( 'textarea_name' => 'pms_settings[invoices][notes]', 'editor_height' => 150 ) ); ?>
            <p class="description"> <?php echo __('These notes will appear at the bottom of each invoice.','paid-member-subscriptions') ?></p>
        </div>

        <?php do_action( 'pms-settings-page_invoice_details_after_content', $options ); ?>

    </div>

    <div id="invoice-settings">

        <h3><?php echo __( 'Invoice Settings', 'paid-member-subscriptions' ); ?></h3>
        <p class="description"> <?php echo __('For invoice title and format you can use the following tags: <code>{{number}}</code>, <code>{{MM}}</code>, <code>{{YYYY}}</code>','paid-member-subscriptions') ?></p>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-title"><?php echo __( 'Invoice Title', 'paid-member-subscriptions' ) ?></label>
            <input type="text" id="invoice-title" class="widefat" name="pms_settings[invoices][title]" value="<?php echo ( isset($options['invoices']['title']) ? esc_attr( $options['invoices']['title'] ) : __('Invoice','paid-member-subscriptions') ) ?>">
            <p class="description"> <?php echo __('Depending on your country fiscal regulations you can change it to things like: Tax Invoice etc.','paid-member-subscriptions') ?></p>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-format"><?php echo __( 'Format', 'paid-member-subscriptions' ) ?></label>
            <input type="text" id="invoices-format" class="widefat" name="pms_settings[invoices][format]" value="<?php echo ( isset($options['invoices']['format']) ? esc_attr( $options['invoices']['format'] ) : '{{number}}' ) ?>">
            <p class="description"> <?php echo __('<strong>Note</strong>: {{number}} is required.','paid-member-subscriptions') ?></p>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-reset-invoice-counter"><?php echo __( 'Reset Invoice Counter', 'paid-member-subscriptions' ) ?></label>
            <p class="description"><input type="checkbox" id="invoices-reset-invoice-counter" name="pms_settings[invoices][reset_invoice_counter]" value="1" <?php echo ( isset( $options['invoices']['reset_invoice_counter'] ) ? checked($options['invoices']['reset_invoice_counter'], '1', false) : '' ); ?> /><?php echo __( 'Check this if you want to reset the invoice counter.', 'paid-member-subscriptions' ); ?></p>
        </div>
        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-next-invoice-number"><?php echo __( 'Next Invoice Number', 'paid-member-subscriptions' ) ?></label>
            <input type="number" id="invoices-next-invoice-number" class="widefat" name="pms_inv_invoice_number" min="1" readonly disabled value="<?php echo $invoice_number; ?>">
            <p class="description"> <?php echo __('Enter the next invoice number. Default value is 1 and increments every time an invoice is issued. Existing invoices will not be changed.','paid-member-subscriptions') ?></p>
        </div>
        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="invoices-reset-yearly"><?php echo __( 'Reset Yearly', 'paid-member-subscriptions' ) ?></label>
            <p class="description"><input type="checkbox" id="invoices-reset-yearly" name="pms_settings[invoices][reset_yearly]" value="1" <?php echo ( isset( $options['invoices']['reset_yearly'] ) ? checked($options['invoices']['reset_yearly'], '1', false) : '' ); ?> /><?php echo __( 'Automatically reset invoice numbers on new year\'s day. Resets invoice number to 1.', 'paid-member-subscriptions' ); ?></p>
        </div>

        <?php do_action( 'pms-settings-page_invoice_settings_after_content', $options ); ?>

    </div>

</div>