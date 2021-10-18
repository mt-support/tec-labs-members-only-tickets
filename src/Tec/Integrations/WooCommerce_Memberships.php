<?php
/**
 * Handles membership checks when using WooCommerce Memberships.
 *
 * @since   1.0.0
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class WooCommerce_Memberships.
 */
class WooCommerce_Memberships extends \tad_DI52_ServiceProvider {

	use Common;

	/**
	 * The integration slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $ID = 'woocommerce_memberships';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		$this->container->singleton( "extension.members_only_tickets.{$this->ID}", $this );
		$this->actions();
	}

	/**
	 * Adds the actions and filters required by the integration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function actions() {
		add_filter( 'tribe_template_context', [ $this, 'remove_tickets_from_context' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/quantity', [ $this, 'ticket_quantity_template' ], 100, 4 );
		add_filter( 'tribe_get_event_meta', [ $this, 'filter_cost' ], 100, 4 );
	}

	/**
	 * Check if user can view a ticket.
	 *
	 * @since 1.0.0
	 * @param int $product_id
	 * @return bool
	 */
	protected function can_view( $product_id ) {
		if ( ! \wc_memberships_is_product_viewing_restricted( $product_id ) ) {
			return true;
		}

		$user_id = \get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		if ( ! \wc_memberships_user_can( $user_id, 'view', [ 'product' => $product_id ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if user can purchase a ticket.
	 *
	 * @since 1.0.0
	 * @param int $product_id
	 * @return bool
	 */
	protected function can_purchase( $product_id ) {
		if ( ! \wc_memberships_is_product_purchasing_restricted( $product_id ) ) {
			return true;
		}

		$user_id = \get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		if ( ! \wc_memberships_user_can( $user_id, 'purchase', [ 'product' => $product_id ] ) ) {
			return false;
		}

		return true;
	}
}
