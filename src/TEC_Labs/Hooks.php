<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * ```php
 *  remove_filter( 'some_filter', [ tribe( TEC_Labs\Membersonlytickets\Hooks::class ), 'some_filtering_method' ] );
 *  remove_filter( 'some_filter', [ tribe( 'extension.members_only_tickets.hooks' ), 'some_filtering_method' ] );
 * ```
 *
 * To remove an action:
 * ```php
 *  remove_action( 'some_action', [ tribe( TEC_Labs\Membersonlytickets\Hooks::class ), 'some_method' ] );
 *  remove_action( 'some_action', [ tribe( 'extension.members_only_tickets.hooks' ), 'some_method' ] );
 * ```
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets;
 */

namespace TEC_Labs\Membersonlytickets;

use Tribe__Main as Common;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Hooks.
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets
 */
class Hooks extends Service_Provider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.members_only_tickets.hooks', $this );
		$this->add_actions();
	}

	/**
	 * Adds the actions required by the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function add_actions() {
		add_action( 'tribe_load_text_domains', [ $this, 'load_text_domains' ] );
		add_filter( 'tribe_template_path_list', [ $this, 'template_path' ], 15, 2 );
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
	 * Setting up the template location for the extension.
	 *
	 * @since 1.0.0
	 *
	 * @param                  $folders
	 * @param \Tribe__Template $template
	 *
	 * @return mixed
	 */
	public function template_path( $folders, \Tribe__Template $template ) {
		$path = (array) rtrim( tribe( Plugin::class )->plugin_path, '/' );
		$folder = [ 'src/views' ];

		$folders[ Plugin::SLUG ] = [
			'id'        => Plugin::SLUG,
			'namespace' => Plugin::SLUG,
			'priority'  => 11,
			'path'      => implode( DIRECTORY_SEPARATOR, array_merge( $path, $folder ) )
		];

		return $folders;
	}
}
