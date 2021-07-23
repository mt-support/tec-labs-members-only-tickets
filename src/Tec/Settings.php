<?php

namespace Tribe\Extensions\Membersonlytickets;

use Tribe__Settings_Manager;

/**
 * Do the Settings.
 *
 * TODO: Delete file if not using settings
 */
class Settings {

	/**
	 * The Settings Helper class.
	 *
	 * @var Settings_Helper
	 */
	protected $settings_helper;

	/**
	 * The prefix for our settings keys.
	 *
	 * @see get_options_prefix() Use this method to get this property's value.
	 *
	 * @var string
	 */
	private $options_prefix = '';

	/**
	 * Settings constructor.
	 *
	 * TODO: Update this entire class for your needs, or remove the entire `src` directory this file is in and do not load it in the main plugin file.
	 *
	 * @param string $options_prefix Recommended: the plugin text domain, with hyphens converted to underscores.
	 */
	public function __construct( $options_prefix ) {
		$this->settings_helper = new Settings_Helper();

		$this->set_options_prefix( $options_prefix );

		// Remove settings specific to Google Maps
		add_action( 'admin_init', [ $this, 'remove_settings' ] );

		// Add settings specific to OSM
		add_action( 'admin_init', [ $this, 'add_settings' ] );
	}

	/**
	 * Allow access to set the Settings Helper property.
	 *
	 * @see get_settings_helper()
	 *
	 * @param Settings_Helper $helper
	 *
	 * @return Settings_Helper
	 */
	public function set_settings_helper( Settings_Helper $helper ) {
		$this->settings_helper = $helper;

		return $this->get_settings_helper();
	}

	/**
	 * Allow access to get the Settings Helper property.
	 *
	 * @see set_settings_helper()
	 */
	public function get_settings_helper() {
		return $this->settings_helper;
	}

	/**
	 * Set the options prefix to be used for this extension's settings.
	 *
	 * Recommended: the plugin text domain, with hyphens converted to underscores.
	 * Is forced to end with a single underscore. All double-underscores are converted to single.
	 *
	 * @see get_options_prefix()
	 *
	 * @param string $options_prefix
	 */
	private function set_options_prefix( $options_prefix = '' ) {
		if ( empty( $opts_prefix ) ) {
			$opts_prefix = str_replace( '-', '_', 'et-members-only-tickets' ); // The text domain.
		}

		$opts_prefix = $opts_prefix . '_';

		$this->options_prefix = str_replace( '__', '_', $opts_prefix );
	}

	/**
	 * Get this extension's options prefix.
	 *
	 * @see set_options_prefix()
	 *
	 * @return string
	 */
	public function get_options_prefix() {
		return $this->options_prefix;
	}

	/**
	 * Given an option key, get this extension's option value.
	 *
	 * This automatically prepends this extension's option prefix so you can just do `$this->get_option( 'a_setting' )`.
	 *
	 * @see tribe_get_option()
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return mixed
	 */
	public function get_option( $key = '', $default = '' ) {
		$key = $this->sanitize_option_key( $key );

		return tribe_get_option( $key, $default );
	}

	/**
	 * Get an option key after ensuring it is appropriately prefixed.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function sanitize_option_key( $key = '' ) {
		$prefix = $this->get_options_prefix();

		if ( 0 === strpos( $key, $prefix ) ) {
			$prefix = '';
		}

		return $prefix . $key;
	}

	/**
	 * Get an array of all of this extension's options without array keys having the redundant prefix.
	 *
	 * @return array
	 */
	public function get_all_options() {
		$raw_options = $this->get_all_raw_options();

		$result = [];

		$prefix = $this->get_options_prefix();

		foreach ( $raw_options as $key => $value ) {
			$abbr_key            = str_replace( $prefix, '', $key );
			$result[ $abbr_key ] = $value;
		}

		return $result;
	}

	/**
	 * Get an array of all of this extension's raw options (i.e. the ones starting with its prefix).
	 *
	 * @return array
	 */
	public function get_all_raw_options() {
		$tribe_options = Tribe__Settings_Manager::get_options();

		if ( ! is_array( $tribe_options ) ) {
			return [];
		}

		$result = [];

		foreach ( $tribe_options as $key => $value ) {
			if ( 0 === strpos( $key, $this->get_options_prefix() ) ) {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Given an option key, delete this extension's option value.
	 *
	 * This automatically prepends this extension's option prefix so you can just do `$this->delete_option( 'a_setting' )`.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function delete_option( $key = '' ) {
		$key = $this->sanitize_option_key( $key );

		$options = Tribe__Settings_Manager::get_options();

		unset( $options[ $key ] );

		return Tribe__Settings_Manager::set_options( $options );
	}

	/**
	 * Here is an example of removing settings from Events > Settings > General tab > "Map Settings" section
	 * that are specific to Google Maps.
	 *
	 * TODO: Remove this method and the corresponding hook in `__construct()` if you don't want to remove any settings.
	 */
	public function remove_settings() {
		// // Remove "Enable Google Maps" checkbox
		// $this->settings_helper->remove_field( 'embedGoogleMaps', 'general' );

		// // Remove "Map view search distance limit" (default of 25)
		// $this->settings_helper->remove_field( 'geoloc_default_geofence', 'general' );

		// // Remove "Google Maps default zoom level" (0-21, default of 10)
		// $this->settings_helper->remove_field( 'embedGoogleMapsZoom', 'general' );
	}

	/**
	 * Adds a new section of fields to Events > Settings > General tab, appearing after the "Map Settings" section
	 * and before the "Miscellaneous Settings" section.
	 *
	 * TODO: Move the setting to where you want and update this docblock. If you like it here, just delete this TODO.
	 */
	public function add_settings() {
		$fields = [
			'members_only_settings_intro'   => [
				'type' => 'html',
				'html' => $this->get_example_intro_text(),
			],
			'product_category' => [
				'type'            => 'text',
				'label'           => esc_html__( "Members only category", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "WooCommerce product category that designates a ticket to only be available to members.", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'required_membership_level' => [
				'type'            => 'text',
				'label'           => esc_html__( "Required Membership Level", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "The membership level needed for a user to be able to purchase members only tickets", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'required_user_role' => [
				'type'            => 'text',
				'label'           => esc_html__( "Required User Role", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "The role a user needs to be able to purchase members only tickets", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
			'required_user_permissions' => [
				'type'            => 'text',
				'label'           => esc_html__( "Required User Permission", 'et-members-only-tickets' ),
				'tooltip'         => esc_html__( "The permission a user needs to be able to purchase members only tickets", 'et-members-only-tickets'),
				'validation_type' => 'html',
			],
		];

		$this->settings_helper->add_fields(
			$this->prefix_settings_field_keys( $fields ),
			'event-tickets',
			'tribeEventsMiscellaneousTitle',
			true
		);
	}

	/**
	 * Add the options prefix to each of the array keys.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function prefix_settings_field_keys( array $fields ) {
		$prefixed_fields = array_combine(
			array_map(
				function ( $key ) {
					return $this->get_options_prefix() . $key;
				}, array_keys( $fields )
			),
			$fields
		);

		return (array) $prefixed_fields;
	}

	/**
	 * Here is an example of getting some HTML for the Settings Header.
	 *
	 * TODO: Delete this method if you do not need a heading for your settings. Also remove the corresponding element in the the $fields array in the `add_settings()` method above.
	 *
	 * @return string
	 */
	private function get_example_intro_text() {
		$result = '<h3>' . esc_html_x( 'Members Only Tickets', 'Settings header', 'et-members-only-tickets' ) . '</h3>';
		$result .= '<p>';
		$result .= esc_html_x( 'Limit access to certain tickets to logged in users with a given membership level or permissions', 'Setting section description', 'et-members-only-tickets' );
		$result .= '</p>';

		return $result;
	}

}
