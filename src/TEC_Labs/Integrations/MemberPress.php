<?php

namespace TEC_Labs\Membersonlytickets\Integrations;

/**
 * Class Memberpress.
 *
 * Handles membership checks when using Memberpress.
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets\Integrations
 */
class MemberPress extends Integration_Abstract implements Integration_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'memberpress';
	}

	/**
	 * @inheritDoc
	 */
	public function is_active() {
		return defined( 'MEPR_PLUGIN_NAME' );
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
		// If the user can purchase, show the ticket.
		if (  ! $this->is_member_ticket( $product_id ) || $this->can_purchase( $product_id ) ) {
			return true;
		}

		// Otherwise, check the settings to determine whether to show or not.
		return ! $this->plugin->get_option( "{$this->get_id()}_hide_member_tickets" );
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

		$product = get_post( $product_id );
		$is_locked = \MeprRule::is_locked( $product );

        return ! $is_locked;
	}

	/**
	 * Check if a ticket is members only.
	 *
	 * @since 1.0.0
	 * @param int $ticket_id
	 * @return bool
	 */
	protected function is_member_ticket( $ticket_id ) {
		$product = get_post( $ticket_id );
		$rules = \MeprRule::get_rules( $product );
		return ! empty( $rules );
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
            "{$this->get_id()}_members_settings_start" => [
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap">'
			],
			"{$this->get_id()}_members_settings_intro" => [
				'type' => 'html',
				'html' => sprintf(
					'<h3>%s</h3><p>%s</p>',
					esc_html__( 'Membership', 'et-members-only-tickets' ),
					esc_html__( 'Settings for MemberPress.', 'et-members-only-tickets' )
				)
			],
			"{$this->get_id()}_hide_member_tickets" => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( "Hide members only tickets.", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "When enabled, only members will see members only tickets.", 'et-members-only-tickets'),
				'validation_type' => 'boolean',
			],
			"{$this->get_id()}_members_only_message" => [
				'type'            => 'textarea',
				'label'           => esc_html__( "Message for non-members.", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "Non-members will see this text as the ticket description.", 'et-members-only-tickets'),
				'default' 		  => esc_html__( "This ticket is for members only.", 'et-members-only-tickets' ),
				'validation_type' => 'html'
            ],
            "{$this->get_id()}_members_settings_end" => [
				'type' => 'html',
				'html' => '</div>'
			],            
		];

		return $settings;
	}
}
