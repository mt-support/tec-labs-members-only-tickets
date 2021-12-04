<?php

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Integration_Handler
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */
class Integration_Handler extends \tad_DI52_ServiceProvider {
	/**
	 * Which classes we will load for order statuses by default.
	 *
	 * @since 1.0.0
	 *
	 * @var string[]
	 */
	protected $default_integrations = [
		Paid_Memberships_Pro::class,
		Restrict_Content_Pro::class,
		WooCommerce_Memberships::class,
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
	 * Sets up all the Status instances for the Classes registered in $default_statuses.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		foreach ( $this->default_integrations as $integration_class ) {

			// Spawn the new instance.
			$integration = new $integration_class( $this->container );

			// Register as a singleton for internal ease of use.
			$this->container->singleton( $integration_class, $integration );

			// Register the service provider.
			$this->container->register( $integration );

			// Collect this particular status instance in this class.
			$this->register_integration( $integration );
		}

		$this->container->singleton( static::class, $this );
	}

	/**
	 * Register an integration.
	 *
	 * @since 1.0.0
	 *
	 * @param Integration_Interface $integration Which status we are registering.
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
	 * Fetches the first status registered with a given slug.
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