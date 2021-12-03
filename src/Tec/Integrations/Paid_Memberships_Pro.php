<?php
/**
 * Handles membership checks when using Paid Memberships Pro.
 *
 * @since   1.0.0
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Paid_Memberships_Pro.
 */
class Paid_Memberships_Pro extends \tad_DI52_ServiceProvider implements Integration_Interface {

	use Common;

	public static function get_id() {
		return 'restrict_content_pro';
	}

	public function is_active() {
		// Get active plugins
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		return in_array( 'paid-memberships-pro/paid-memberships-pro.php', $active_plugins, true );
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		if ( ! $this->is_active() ) {
			return;
		}

		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * @inheritDoc
	 */
	public function add_filters() {
		add_filter( 'tribe_template_context', [ $this, 'remove_tickets_from_context' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/quantity', [ $this, 'ticket_quantity_template' ], 100, 4 );
		add_filter( 'tribe_get_event_meta', [ $this, 'filter_cost' ], 100, 4 );
		add_filter( 'extension.members_only_tickets.settings', [ $this, 'settings' ] );
	}

	/**
	 * @inheritDoc
	 */
	public function add_actions() {
		// TODO: Implement add_actions() method.
	}

	/**
	 * @inheritDoc
	 */
	public function can_view( $product_id ) {
		// If not a member ticket or if the user can purchase, show the ticket.
		if ( ! $this->is_member_ticket( $product_id ) || $this->can_purchase( $product_id ) ) {
			return true;
		}

		// Otherwise, check the settings to determine whether to show or not.
		return ! tribe( 'extension.members_only_tickets.plugin' )->get_option( 'hide_member_tickets' );
	}

	/**
	 * @inheritDoc
	 */
	public function can_purchase( $product_id ) {
		// If this isn't a "members only" ticket, don't interfere.
		if ( ! $this->is_member_ticket( $product_id ) ) {
			return true;
		}
		// If not logged in, we don't know if they are a member.
		if ( ! is_user_logged_in() ) {
			return false;
		}
		// The required membership level.
		$membership_level = tribe( 'extension.members_only_tickets.plugin' )->get_option( 'required_membership_level' );

		// Does the user have the required membership level?
		return pmpro_hasMembershipLevel( $membership_level );
	}

	/**
	 * Check if a ticket is members only.
	 *
	 * @since 1.0.0
	 * @param int $ticket_id
	 * @return bool
	 */
	protected function is_member_ticket( $ticket_id ) {
		// The category added to members only products in WooCommerce.
		$members_only_product_category = tribe( 'extension.members_only_tickets.plugin' )->get_option( 'product_category' );

		// Is this a member ticket?
		return has_term( $members_only_product_category, 'product_cat', $ticket_id );
	}

	/**
	 * Add any integration settings.
	 *
	 * @since 1.0.0
	 * @param array $settings
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
