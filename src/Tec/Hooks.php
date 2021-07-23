<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * ```php
 *  remove_filter( 'some_filter', [ tribe( Tribe\Extensions\Membersonlytickets\Hooks::class ), 'some_filtering_method' ] );
 *  remove_filter( 'some_filter', [ tribe( 'extension.members_only_tickets.hooks' ), 'some_filtering_method' ] );
 * ```
 *
 * To remove an action:
 * ```php
 *  remove_action( 'some_action', [ tribe( Tribe\Extensions\Membersonlytickets\Hooks::class ), 'some_method' ] );
 *  remove_action( 'some_action', [ tribe( 'extension.members_only_tickets.hooks' ), 'some_method' ] );
 * ```
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets;
 */

namespace Tribe\Extensions\Membersonlytickets;

use Tribe__Main as Common;
use Tribe\Extensions\Membersonlytickets\Integrations\Paid_Memberships_Pro as Member_Plugin;

/**
 * Class Hooks.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets;
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.members_only_tickets.hooks', $this );

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function add_actions() {
		add_action( 'tribe_load_text_domains', [ $this, 'load_text_domains' ] );
	}

	/**
	 * Adds the filters required by the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function add_filters() {
		add_filter( 'tribe_template_context', [ $this, 'filter_ticket_visibility'], 100, 4 );
	}

	/**
	 * Load text domain for localization of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_text_domains() {
		$mopath = tribe( Plugin::class )->plugin_dir . 'lang/';
		$domain = 'et-members-only-tickets';

		// This will load `wp-content/languages/plugins` files first.
		Common::instance()->load_text_domain( $domain, $mopath );
	}


	/**
	 * Check if user can access tickets
	 *
	 * @since 1.0.0
	 */
	public function filter_ticket_visibility( $context, $file, $name, $obj ) {

		// bail if not the target template
		if ( 'v2/tickets' !== implode( "/", $name ) ) {
			return $context;
		}

		// The category added to members only products in WooCommerce.
		$members_only_product_category = tribe( 'extension.members_only_tickets.plugin' )->get_option( 'product_category' );

		// The required membership level.
		$required_membership_level_name = tribe( 'extension.members_only_tickets.plugin' )->get_option( 'required_membership_level' );

		// If options not set, just carry on.
		if ( empty( $members_only_product_category ) || empty( $required_membership_level_name ) ) {
			return $context;
		}

		// Is user a member?
		$user_is_member = Member_Plugin::is_member( $required_membership_level_name );

		foreach( $context['tickets'] as $index => $ticket ) {
			if( ! has_term( $members_only_product_category, 'product_cat', $ticket->ID ) ) continue;
			if( ! $user_is_member ) {

				$on_sale_index = array_search( $ticket->ID, array_column( $context['tickets_on_sale'], 'ID' ) );

				unset( $context['tickets'][$index] );
				unset( $context['tickets_on_sale'][$on_sale_index] );
			}
		}

		return $context;
	}
}
