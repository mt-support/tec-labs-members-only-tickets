<?php
/**
 * Handles membership checks when using Restrict Content Pro.
 *
 * @since   1.0.0
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Restrict_Content_Pro.
 */
class Restrict_Content_Pro extends \tad_DI52_ServiceProvider implements Integration_Interface {

	use Common;

	public static function get_id() {
		return 'restrict_content_pro';
	}

	public function is_active() {
		// Get active plugins
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		return in_array( 'restrict-content-pro/restrict-content-pro.php', $active_plugins, true );
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
	public function add_actions() {
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
		return rcp_user_can_view_woocommerce_product( get_current_user_id(), $product_id );
	}

	/**
	 * @inheritDoc
	 */
	public function can_purchase( $product_id ) {
		return rcp_user_can_purchase_woocommerce_product( get_current_user_id(), $product_id );
	}
}
