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
class Paid_Memberships_Pro extends Integration_Abstract implements Integration_Interface {
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
		// The category added to members only products in WooCommerce.
		$viewable_product_category = $this->get_option( "{$this->get_id()}_product_category_view" );

		// Is this a member ticket?
		if ( ! has_term( $viewable_product_category, 'product_cat', $product_id ) ) {
			return true;
		}

		// If not logged in, we don't know if they are a member.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// The required membership level.
		$membership_level = $this->get_option( "{$this->get_id()}_required_membership_level_view" );

		// Does the user have the required membership level?
		return pmpro_hasMembershipLevel( $membership_level );
	}

	/**
	 * @inheritDoc
	 */
	public function can_purchase( $product_id ) {
		// The category added to members only products in WooCommerce.
		$purchasable_product_category = $this->get_option( "{$this->get_id()}_product_category_purchase" );

		// Is this a member ticket?
		if ( ! has_term( $purchasable_product_category, 'product_cat', $product_id ) ) {
			return true;
		}

		// If not logged in, we don't know if they are a member.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// The required membership level.
		$membership_level = $this->get_option( "{$this->get_id()}_required_membership_level_purchase" );

		// Does the user have the required membership level?
		return pmpro_hasMembershipLevel( $membership_level );
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
			"{$this->get_id()}_members_settings_intro"   => [
				'type' => 'html',
				'html' => sprintf(
					'<h3>%s</h3><p>%s</p>',
					esc_html__( 'Membership', 'et-members-only-tickets' ),
					esc_html__( 'Settings for Paid Memberships Pro.', 'et-members-only-tickets' )
				)
			],
			"{$this->get_id()}_members_can_view_title"   => [
				'type' => 'html',
				'html' => sprintf(
					'<h4>%s</h4>',
					esc_html__( 'Only members can view', 'et-members-only-tickets' )
				)
			],
			"{$this->get_id()}_required_membership_level_view" => [
				'type'            => 'text',
				'label'           => esc_html__( 'Membership level', 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( 'The membership level needed for a user to be able to view members only tickets.', 'et-members-only-tickets'),
				'validation_type' => 'html'
			],
			"{$this->get_id()}_product_category_view" => [
				'type'            => 'text',
				'label'           => esc_html__( 'Product category - viewable', 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( 'WooCommerce product category that designates a ticket to be visable to members only.', 'et-members-only-tickets' ),
				'validation_type' => 'html'
			],
			"{$this->get_id()}_members_can_purchase_title"   => [
				'type' => 'html',
				'html' => sprintf(
					'<h4>%s</h4>',
					esc_html__( 'Only members can purchase', 'et-members-only-tickets' )
				)
			],
			"{$this->get_id()}_required_membership_level_purchase" => [
				'type'            => 'text',
				'label'           => esc_html__( 'Membership level', 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( 'The membership level needed for a user to be able to purchase members only tickets.', 'et-members-only-tickets' ),
				'validation_type' => 'html'
			],
			"{$this->get_id()}_product_category_purchase" => [
				'type'            => 'text',
				'label'           => esc_html__( 'Product category - purchasable', 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( 'WooCommerce product category that designates a ticket to be purchasable by members only.', 'et-members-only-tickets' ),
				'validation_type' => 'html'
			],
			"{$this->get_id()}_members_only_message" => [
				'type'            => 'textarea',
				'label'           => esc_html__( 'Message for non-members.', 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( 'Non-members will see this text in place of the ticket description.', 'et-members-only-tickets' ),
				'default' 		  => esc_html__( 'This ticket is for members only.', 'et-members-only-tickets' ),
				'validation_type' => 'html'
			]
		];

		return $settings;
	}
}
