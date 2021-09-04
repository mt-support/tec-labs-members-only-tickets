<?php
/**
 * Handles membership checks when using Paid Memberships Pro
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Paid_Memberships_Pro.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */
class Paid_Memberships_Pro extends \tad_DI52_ServiceProvider {

	/**
	 * The integration slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $ID = 'paid_memberships_pro';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( "extension.members_only_tickets.{$this->ID}", $this );
		$this->filters();
	}

	/**
	 * Adds the filters required by the integration.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function filters() {
		add_filter( 'tribe_template_context', [ $this, 'filter_ticket_visibility' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/quantity', [ $this, 'ticket_quantity_template' ], 100, 4 );
		add_filter( 'extension.members_only_tickets.settings', [ $this, 'settings' ] );
	}

	/**
	 * Check if user can view member tickets
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function can_view() {
		if ( $this->can_purchase() ) {
			return true;
		}

		return ! tribe( 'extension.members_only_tickets.plugin' )->get_option( 'hide_member_tickets' );
	}

	/**
	 * Check if user can view tickets
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function can_purchase() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// The required membership level.
		$membership_level = tribe( 'extension.members_only_tickets.plugin' )->get_option( 'required_membership_level' );

		return pmpro_hasMembershipLevel( $membership_level );
	}

	/**
	 * Check if a ticket is members only
	 *
	 * @since 1.0.0
	 *
	 * @param int $ticket_id
	 *
	 * @return bool
	 */
	protected function is_member_ticket( $ticket_id ) {

		// The category added to members only products in WooCommerce.
		$members_only_product_category = tribe( 'extension.members_only_tickets.plugin' )->get_option( 'product_category' );

		return has_term( $members_only_product_category, 'product_cat', $ticket_id );
	}

	/**
	 * Hide tickets if they shouldn't be shown.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 * @param string $file
	 * @param array $name
	 * @param object $obj
	 *
	 * @return array
	 */
	public function filter_ticket_visibility( $context, $file, $name, $obj ) {
		if ( 'v2/tickets' !== implode( "/", $name ) ) {
			return $context;
		}

		foreach( $context['tickets'] as $index => $ticket ) {
			if( ! $this->is_member_ticket( $ticket->ID ) ) {
				continue;
			}

			if ( ! $this->can_view() ) {
				$on_sale_index = array_search( $ticket->ID, array_column( $context['tickets_on_sale'], 'ID' ) );
				unset( $context['tickets'][$index] );
				unset( $context['tickets_on_sale'][$on_sale_index] );
			}
		}

		if ( empty( $context['tickets_on_sale'] ) ) {
			$context['has_tickets_on_sale'] = FALSE;
		}

		return $context;
	}

	/**
	 * If user can't purchase tickets, replace quantity fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html
	 * @param string $file
	 * @param array $name
	 * @param object $obj
	 *
	 * @return string
	 */
	public function ticket_quantity_template( $html, $file, $name, $obj ) {
		$ticket = $obj->get( 'ticket' );

		if ( $this->is_member_ticket( $ticket->ID ) && ! $this->can_purchase() ) {
			return '<div class="tribe-common-h4 tribe-tickets__tickets-item-quantity" style="font-size: 12px;">Members <br/>only!</div>';
		}

		return $html;
	}

	/**
	 * Add any integration settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function settings( $settings ) {
		$settings[ $this->ID ] = [
			'members_settings_intro'   => [
				'type' => 'html',
				'html' => $this->get_settings_intro()
			],
			'product_category' => [
				'type'            => 'text',
				'label'           => esc_html__( "Product category", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "WooCommerce product category that designates a ticket to only be available to members.", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'required_membership_level' => [
				'type'            => 'text',
				'label'           => esc_html__( "Membership level", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "The membership level needed for a user to be able to purchase member tickets.", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'hide_member_tickets' => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( "Hide member tickets from non-members.", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "When enabled, only members will see tickets with the members product category.", 'et-members-only-tickets'),
				'validation_type' => 'boolean',
			]
		];

		return $settings;
	}

	/**
	 * Create settings intro markup.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_settings_intro() {
		$result = '<h3>' . esc_html_x( 'Membership', 'Settings header', 'et-members-only-tickets' ) . '</h3>';
		$result .= '<p>';
		$result .= esc_html_x( 'Limit access to tickets by membership level with Paid Memberships Pro.', 'Setting section description', 'et-members-only-tickets' );
		$result .= '</p>';

		return $result;
	}
}
