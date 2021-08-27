<?php
/**
 * Handles membership checks when using Restrict Content Pro
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
class Restrict_Content_Pro {

	/**
	 * Add filters
	 *
	 * @since 1.0.0
	 */
	public static function filters() {
		add_filter( 'tribe_template_context', [self::class, 'remove_tickets_from_context'], 100, 4 );
		add_filter( "tribe_template_html:tickets/v2/tickets/item/quantity", [ self::class, 'ticket_quantity_template'], 100, 4 );
	}

	/**
	 * Check if user can view tickets
	 *
	 * @since 1.0.0
	 */
	public static function can_view( $product_id ) {
		return rcp_user_can_view_woocommerce_product( get_current_user_id(), $product_id );
	}

	/**
	 * Check if user can purchase tickets
	 *
	 * @since 1.0.0
	 */
	public static function can_purchase( $product_id ) {
		return rcp_user_can_purchase_woocommerce_product( get_current_user_id(), $product_id );
	}

	/**
	 * Maybe remove tickets from context
	 *
	 * @since 1.0.0
	 */
	public static function remove_tickets_from_context( $context, $file, $name, $obj ) {
		$current_template = implode( "/", $name );
		$templates_to_check = [
			'v2/tickets',
		];

		if ( ! in_array( $current_template, $templates_to_check ) ) {
			return $context;
		}

		foreach( $context['tickets'] as $index => $ticket ) {

			if( ! self::can_view( $ticket->ID )  ) {
				$on_sale_index = array_search( $ticket->ID, array_column( $context['tickets_on_sale'], 'ID' ) );
				unset( $context['tickets'][$index] );
				unset( $context['tickets_on_sale'][$on_sale_index] );
			}
		}

		if ( empty( $context['tickets_on_sale'] ) ) {
			$context['has_tickets_on_sale'] = FALSE;
		}

		return $context;
	}

	/**
	 * If user can't purchase tickets, replace quantity fields
	 *
	 * @since 1.0.0
	 */
	public static function ticket_quantity_template( $html, $file, $name, $obj ) {

		$ticket = $obj->get( 'ticket' );

		if ( ! self::can_purchase( $ticket->ID ) ) {
			return '<div class="tribe-common-h4 tribe-tickets__tickets-item-quantity" style="font-size: 12px;">Members <br/>only!</div>';
		}

		return $html;
	}

}
