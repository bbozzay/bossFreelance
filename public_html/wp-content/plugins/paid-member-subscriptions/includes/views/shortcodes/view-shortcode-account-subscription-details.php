<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML output for the member subscription details
 *
 */

$subscriptions         = pms_get_member_subscriptions( array( 'user_id' => $user_id ) );
$subscription_statuses = pms_get_member_subscription_statuses();

foreach( $subscriptions as $subscription ) :
	if ( is_null( $subscription ) )
		continue;

	$subscription_plan = pms_get_subscription_plan( $subscription->subscription_plan_id );
	?>

	<table class="pms-account-subscription-details-table">
		<tbody>

			<?php do_action( 'pms_subscriptions_table_before_rows' ); ?>

			<!-- Subscription plan -->
			<tr>
				<td><?php echo __( 'Subscription Plan', 'paid-member-subscriptions' ); ?></td>
				<td><?php echo ( ! empty( $subscription_plan->name ) ? $subscription_plan->name : '' ); ?></td>
			</tr>

			<!-- Subscription status -->
			<tr>
				<td><?php echo __( 'Status', 'paid-member-subscriptions' ); ?></td>
				<td>
                    <?php echo ( ! empty( $subscription_statuses[$subscription->status] ) ? $subscription_statuses[$subscription->status] : '' ); ?>
                    <?php echo ( $subscription->is_trial_period() ? ' (' . __( 'Trial', 'paid-member-subscriptions' ) . ')' : '' ); ?>
                </td>
			</tr>

            <!-- Subscription start date -->
            <tr>
                <td><?php echo __( 'Start Date', 'paid-member-subscriptions' ); ?></td>
                <td><?php echo ( ! empty( $subscription->start_date ) ? ucfirst( date_i18n( get_option('date_format'), strtotime( $subscription->start_date ) ) ) : '' ); ?></td>
            </tr>

            <!-- Subscription expiration date -->
            <tr>
                <td><?php echo __( 'Expiration Date', 'paid-member-subscriptions' ); ?></td>
                <td><?php echo ( ! empty( $subscription->expiration_date ) ? ucfirst( date_i18n( get_option('date_format'), strtotime( $subscription->expiration_date ) ) ) : __( 'Unlimited', 'paid-member-subscriptions' ) ); ?></td>
            </tr>

            <!-- Subscription next payment -->
            <?php if( $subscription->is_trial_period() ): ?>
                <tr>
                    <td><?php echo __( 'Trial End Date', 'paid-member-subscriptions' ); ?></td>
                    <td><?php echo ucfirst( date_i18n( get_option('date_format'), strtotime( $subscription->trial_end ) ) ); ?></td>
                </tr>
            <?php endif; ?>

            <!-- Subscription next payment -->
            <?php if( ! empty( $subscription->billing_next_payment ) ): ?>
            <tr>
                <td><?php _e( 'Next Payment', 'paid-member-subscriptions' ); ?></td>
				<td><?php printf( _x( '%s on %s', '[amount] on [date]', 'paid-member-subscriptions' ), pms_format_price( $subscription->billing_amount, pms_get_active_currency() ), ucfirst( date_i18n( get_option('date_format'), strtotime( $subscription->billing_next_payment ) ) ) ); ?></td>
            </tr>
            <?php endif; ?>

            <!-- Subscription actions -->
            <tr>
                <td><?php _e( 'Actions', 'paid-member-subscriptions' ); ?></td>
                <td>
                    <?php

                    if( $subscription->status != 'pending' && $subscription_plan->status != 'inactive' ) {

                        // Get plan upgrades
                        $plan_upgrades = pms_get_subscription_plan_upgrades( $subscription_plan->id );

                        if( !empty( $plan_upgrades ) )
                            echo apply_filters( 'pms_output_subscription_plan_action_upgrade', '<a class="pms-account-subscription-action-link" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'upgrade_subscription', 'subscription_id' => $subscription->id, 'subscription_plan' => $subscription_plan->id ), pms_get_current_page_url( true ) ), 'pms_member_nonce', 'pmstkn' ) ) . '">' . __( 'Upgrade', 'paid-member-subscriptions' ) . '</a>', $subscription_plan, $subscription->to_array(), $member->user_id );

                        // Number of days before expiration to show the renewal action
                        $renewal_display_time = apply_filters( 'pms_output_subscription_plan_action_renewal_time', 15 );

                        if( ( ! $subscription->is_auto_renewing() && strtotime( $subscription->expiration_date ) - time() < $renewal_display_time * DAY_IN_SECONDS ) || $subscription->status == 'canceled' )
                            echo apply_filters( 'pms_output_subscription_plan_action_renewal', '<a class="pms-account-subscription-action-link" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'renew_subscription', 'subscription_id' => $subscription->id, 'subscription_plan' => $subscription_plan->id ), pms_get_current_page_url( true ) ), 'pms_member_nonce', 'pmstkn' ) ) . '">' . __( 'Renew', 'paid-member-subscriptions' ) . '</a>', $subscription_plan, $subscription->to_array(), $member->user_id );

                        if( $subscription->status == 'active' && ( ( $subscription->is_auto_renewing() && pms_is_https() ) || ! $subscription->is_auto_renewing() ) )
                            echo apply_filters( 'pms_output_subscription_plan_action_cancel', '<a class="pms-account-subscription-action-link" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'cancel_subscription', 'subscription_id' => $subscription->id  ), pms_get_current_page_url( true ) ), 'pms_member_nonce', 'pmstkn' ) ) . '">' . __( 'Cancel', 'paid-member-subscriptions' ) . '</a>', $subscription_plan, $subscription->to_array(), $member->user_id );


                    } else {

                        if( $subscription_plan->price > 0 )
                            echo apply_filters( 'pms_output_subscription_plan_pending_retry_payment', '<a class="pms-account-subscription-action-link" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'retry_payment_subscription', 'subscription_plan' => $subscription_plan->id  ) ), 'pms_member_nonce', 'pmstkn' ) ) . '">' . __( 'Retry payment', 'paid-member-subscriptions' ) . '</a>', $subscription_plan, $subscription->to_array() );

                    }

                    if( ( $subscription->is_auto_renewing() && pms_is_https() ) || ! $subscription->is_auto_renewing() )
                        echo apply_filters( 'pms_output_subscription_plan_action_abandon', '<a class="pms-account-subscription-action-link" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'pms-action' => 'abandon_subscription', 'subscription_id' => $subscription->id  ), pms_get_current_page_url( true ) ), 'pms_member_nonce', 'pmstkn' ) ) . '">' . __( 'Abandon', 'paid-member-subscriptions' ) . '</a>', $subscription_plan, $subscription->to_array(), $member->user_id );

                    ?>
                </td>
            </tr>

			<?php do_action( 'pms_subscriptions_table_after_rows' ); ?>

		</tbody>
	</table>

<?php endforeach; ?>
