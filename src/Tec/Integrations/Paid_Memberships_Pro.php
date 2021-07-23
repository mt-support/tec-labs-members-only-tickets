<?php
/**
 * Handles membership checks when using Paid Memberships Pro
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations;
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Hooks.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations;
 */
class Paid_Memberships_Pro {

	/**
	 * Check if user can access tickets
	 *
	 * @since 1.0.0
	 */
	public static function is_member( $membership_level ) {

		// Check that we have access to PMP functions
		if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
			return true;
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return pmpro_hasMembershipLevel( $membership_level );
	}
}
