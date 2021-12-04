<?php

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class WooCommerce_Memberships.
 *
 * Handles membership checks when using WooCommerce Memberships.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */
class WooCommerce_Memberships extends \tad_DI52_ServiceProvider implements Integration_Interface {

	use Integration_Traits;

	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'woocommerce_memberships';
	}

	/**
	 * @inheritDoc
	 */
	public function is_active() {
		return class_exists( 'WC_Memberships_Loader' );
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
		add_filter( 'tribe_get_event_meta', [ $this, 'filter_cost' ], 100, 4 );
	}

	/**
	 * @inheritDoc
	 */
	public function can_view( $product_id ) {
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
	 * @inheritDoc
	 */
	public function can_purchase( $product_id ) {
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
