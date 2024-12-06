<?php
/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package TEC_Labs\Membersonlytickets;
 */

namespace TEC_Labs\Membersonlytickets;

use TEC\Common\Contracts\Service_Provider;
use TEC_Labs\Membersonlytickets\Integrations\Integrations;

/**
 * Class Plugin
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets
 */
class Plugin extends Service_Provider {
	/**
	 * Stores the version for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.4';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SLUG = 'members-only-tickets';

	/**
	 * Stores the location of the main extension file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const FILE = TEC_LABS_MEMBERS_ONLY_TICKETS_FILE;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * @since 1.0.0
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * @since 1.0.0
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Setup the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.members_only_tickets.plugin', $this );
		$this->container->register( PUE::class );

		// If plugin dependencies are not met, bail.
		if ( ! $this->check_plugin_dependencies() ) {
			return;
		}

		// Register core plugin hooks.
		$this->container->register( Hooks::class );

		// Register the integrations.
		$this->container->register( Integrations::class );

		// Add the integration hooks.
		$this->boot_integrations();

		// Do the settings.
		$this->get_settings();
	}

	/**
	 * Set up the integration hooks.
	 *
	 * @since 1.0.0
	 */
	protected function boot_integrations() {
		$integrations = $this->container->make( Integrations::class );

		foreach ( $integrations->get_all() as $integration ) {
			$integration->boot();
		}
	}

	/**
	 * Checks whether the plugin dependency manifest is satisfied or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the plugin dependency manifest is satisfied or not.
	 */
	protected function check_plugin_dependencies() {
		$this->register_plugin_dependencies();

		return tribe_check_plugin( static::class );
	}

	/**
	 * Registers the plugin and dependency manifest among those managed by Tribe Common.
	 *
	 * @since 1.0.0
	 */
	protected function register_plugin_dependencies() {
		$plugin_register = new Plugin_Register();
		$plugin_register->register_plugin();

		$this->container->singleton( Plugin_Register::class, $plugin_register );
		$this->container->singleton( 'extension.members_only_tickets', $plugin_register );
	}

	/**
	 * Get this plugin's options prefix.
	 *
	 * Settings_Helper will append a trailing underscore before each option.
	 *
	 * @return string
	 */
	private function get_options_prefix() {
		return (string) str_replace( '-', '_', 'tribe-ext-members-only-tickets' );
	}

	/**
	 * Get Settings instance.
	 *
	 * @return Settings
	 */
	private function get_settings() {
		if ( empty( $this->settings ) ) {
			$this->settings = new Settings( $this->get_options_prefix() );
		}

		return $this->settings;
	}

	/**
	 * Get all of this extension's options.
	 *
	 * @return array
	 */
	public function get_all_options() {
		$settings = $this->get_settings();

		return $settings->get_all_options();
	}

	/**
	 * Get a specific extension option.
	 *
	 * @param        $option
	 * @param string $default
	 *
	 * @return array
	 */
	public function get_option( $option, $default = '' ) {
		$settings = $this->get_settings();

		return $settings->get_option( $option, $default );
	}

	/**
	 * Gets the template instance used for extension views.
	 *
	 * @since 1.0.0
	 *
	 * @return \Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( $this );
			$this->template->set_template_folder( 'src/views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( true );
		}

		return $this->template;
	}
}
