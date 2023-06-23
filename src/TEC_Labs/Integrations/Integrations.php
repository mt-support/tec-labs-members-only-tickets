<?php

namespace TEC_Labs\Membersonlytickets\Integrations;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Integration_Handler
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets\Integrations
 */
class Integrations extends Service_Provider {
	/**
	 * Which integration classes we will load by default.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $default_integrations = [
		Paid_Memberships_Pro::class,
		Restrict_Content_Pro::class,
		WooCommerce_Memberships::class,
		MemberPress::class
	];

	/**
	 * Stores integration instances.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $integrations = [];

	/**
	 * Sets up all the integration classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		$plugin = $this->container->make( 'extension.members_only_tickets.plugin' );

		foreach ( $this->default_integrations as $integration_class ) {
			// Instantiate the integration class.
			$integration = new $integration_class( $plugin );

			// Register as a singleton for internal ease of use.
			$this->container->singleton( $integration_class, $integration );

			// Store the integration instance.
			$this->register_integration( $integration );
		}

		$this->container->singleton( static::class, $this );
	}

	/**
	 * Register an integration.
	 *
	 * @since 1.0.0
	 *
	 * @param Integration_Interface $integration - Store the integration.
	 *
	 * @return void
	 */
	public function register_integration( Integration_Interface $integration ) {
		$this->integrations[] = $integration;
	}

	/**
	 * Gets the registered integrations.
	 *
	 * @since 1.0.0
	 *
	 * @return Integration_Interface[]
	 */
	public function get_all() {
		return $this->integrations;
	}

	/**
	 * Get a registered integration by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id
	 *
	 * @return Integration_Interface|null
	 */
	public function get_by_id( $id ) {
		foreach ( $this->get_all() as $integration ) {
			if ( $integration::get_id() === $id ) {
				return $integration;
			}
		}

		return null;
	}
}
