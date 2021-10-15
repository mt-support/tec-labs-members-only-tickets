<?php
/**
 * Handles membership checks when using Restrict Content Pro.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Restrict_Content_Pro.
 */
class Restrict_Content_Pro extends \tad_DI52_ServiceProvider {

	use Common;

	/**
	 * The integration slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $ID = 'restrict_content_pro';

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
	 * Check if user can view tickets.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	protected function can_view( $product_id ) {
		return rcp_user_can_view_woocommerce_product( get_current_user_id(), $product_id );
	}

	/**
	 * Check if user can purchase tickets.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	protected function can_purchase( $product_id ) {
		return rcp_user_can_purchase_woocommerce_product( get_current_user_id(), $product_id );
	}
}
