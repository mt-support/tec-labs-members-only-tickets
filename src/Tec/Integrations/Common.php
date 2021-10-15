<?php
/**
 * Base methods for all integrations.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Common methods for all integrations.
 *
 * @since  1.0.0
 * @access public
 */
trait Common {

	/**
	 * Add filters
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function filters() {
		add_filter( 'tribe_template_context', [ $this, 'remove_tickets_from_context' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/quantity', [ $this, 'ticket_quantity_template' ], 100, 4 );
		add_filter( 'tribe_get_event_meta', [ $this, 'filter_cost' ], 100, 4 );
	}

	/**
	 * Maybe remove tickets from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 * @param string $file
	 * @param array $name
	 * @param object $obj
	 *
	 * @return array
	 */
	public function remove_tickets_from_context( $context, $file, $name, $obj ) {
		if (  'v2/tickets' !== implode( "/", $name ) ) {
			return $context;
		}

		foreach( $context['tickets'] as $index => $ticket ) {
			if ( ! $this->can_view( $ticket->ID ) ) {
				$on_sale_index = array_search( $ticket->ID, array_column( $context['tickets_on_sale'], 'ID' ) );
				unset( $context['tickets'][ $index ] );
				unset( $context['tickets_on_sale'][ $on_sale_index ] );
			}
		}

		if ( empty( $context['tickets_on_sale'] ) ) {
			$context['has_tickets_on_sale'] = FALSE;
		}

		return $context;
	}

	/**
	 * If user can't purchase tickets, replace quantity fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html
	 * @param string $file
	 * @param array $name
	 * @param object $obj
	 *
	 * @return string
	 */
	public function ticket_quantity_template( $html, $file, $name, $obj ) {
		$ticket = $obj->get( 'ticket' );

		if ( ! $this->can_purchase( $ticket->ID ) ) {
			return '<div class="tribe-common-h4 tribe-tickets__tickets-item-quantity" style="font-size: 12px;">Members <br/>only!</div>';
		}

		return $html;
	}

	/**
	 * Filter hidden member tickets from showing up in cost range.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $costs - List of ticket costs.
	 * @param int    $post_id - Target Event's ID.
	 * @param string $meta - Meta key name.
	 * @param bool   $single - Determines if the requested meta should be a single item or an array of items.
	 *
	 * @return array The list of ticket costs with hidden tickets excluded possibly.
	 */
	public function filter_cost( $costs, $post_id, $meta, $single ) {

		if ( '_EventCost' != $meta || $single || empty( $costs )  ) {
			return $costs;
		}

		// Get the tickets
		$tickets = tribe( 'tickets-plus.commerce.woo' )->get_tickets( $post_id );

		// Check tickets to see if we should show the cost
		foreach ( $tickets as $ticket ) {

			if ( ! $this->can_view( $ticket->ID ) ) {
				// Try to find the ticket price in the list of costs.
				$key = array_search( $ticket->price, $costs );

				// Remove the value from the list of costs if we found it.
				if ( false !== $key ) {
					unset( $costs[ $key ] );
				}
				continue;
			}
		}

		return $costs;
	}
}