<?php

namespace TEC_Labs\Membersonlytickets\Integrations;

/**
 * Class Paid_Memberships_Pro.
 *
 * Handles membership checks when using Paid Memberships Pro.
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets\Integrations
 */
class Paid_Memberships_Pro extends \tad_DI52_ServiceProvider implements Integration_Interface {

	use Integration_Traits;

	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'paid_memberships_pro';
	}

	/**
	 * @inheritDoc
	 */
	public function is_active() {
		return defined( 'PMPRO_VERSION' );
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
	public function add_filters() {
		add_filter( 'tribe_template_context', [ $this, 'remove_tickets_from_context' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/quantity', [ $this, 'ticket_quantity_template' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/content/description', [ $this, 'ticket_description_template' ], 100, 4 );
		add_filter( 'tribe_get_event_meta', [ $this, 'filter_cost' ], 100, 4 );
		add_filter( 'extension.members_only_tickets.settings', [ $this, 'settings' ] );
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
		// If not a "members only" ticket, don't interfere.
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
		$settings[ $this->get_id() ] = [
			'members_settings_intro'   => [
				'type' => 'html',
				'html' => sprintf(
					'<h3>%s</h3><p>%s</p>',
					esc_html__( 'Membership', 'et-members-only-tickets' ),
					esc_html__( 'Settings for Paid Memberships Pro.', 'et-members-only-tickets' )
				)
			],
			'product_category' => [
				'type'            => 'text',
				'label'           => esc_html__( "Product category", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "WooCommerce product category that designates a ticket to be members only.", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'required_membership_level' => [
				'type'            => 'text',
				'label'           => esc_html__( "Membership level", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "The membership level needed for a user to be able to purchase members only tickets.", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'hide_member_tickets' => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( "Hide members only tickets.", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "When enabled, only members will see tickets with the members product category.", 'et-members-only-tickets'),
				'validation_type' => 'boolean',
			],
			'members_only_message' => [
				'type'            => 'textarea',
				'label'           => esc_html__( "Message for non-members.", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "Non-members will see this text as the ticket description.", 'et-members-only-tickets'),
				'default' 		  => esc_html__( "This ticket is for members only.", 'et-members-only-tickets' ),
				'validation_type' => 'html',
			]
		];

		return $settings;
	}
}
