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
	 * Adds the filters required by the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function filters() {
		add_filter( 'tribe_template_context', [ $this, 'filter_ticket_visibility'], 100, 4 );
	}

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
		$user_is_member = self::is_member( $required_membership_level_name );

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
