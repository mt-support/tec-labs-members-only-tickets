<?php
/**
 * Integrations
 *
 * Load functionality for any membership plugins that are supported
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;


/**
 * Class Hooks.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets;
 */
class Integrations extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.members_only_tickets.integrations', $this );
		$this->load_integrations();
	}

	/**
	 * Load the integrations
	 *
	 * @since 1.0.0
	 */
	protected function load_integrations() {

		// Restrict Content Pro
		if ( class_exists( 'Restrict_Content_Pro' ) ) {
			Restrict_Content_Pro::filters();
		}

		// Paid Memberships Pro
		if ( defined( 'PMPRO_VERSION' ) ) {
			Paid_Memberships_Pro::filters();
		}
	}

}
