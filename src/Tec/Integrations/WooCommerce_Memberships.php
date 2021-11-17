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
class WooCommerce_Memberships extends \tad_DI52_ServiceProvider implements Integration_Interface {

	use Common;

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
		// Get active plugins
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		return in_array( 'woocommerce-memberships/woocommerce-memberships.php', $active_plugins, true );
	}

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		if ( ! $this->is_active() ) {
			return;
		}

		$this->add_filters();
		$this->add_actions();
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
