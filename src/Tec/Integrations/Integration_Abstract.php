<?php

namespace TEC_Labs\Membersonlytickets\Integrations;

/**
 * Base methods used in all integrations.
 *
 * @since   1.0.0
 *
 * @package TEC_Labs\Membersonlytickets\Integrations
 */
class Integration_Abstract {
	/**
	 * Stores the plugin instance.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected $plugin;

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

	/**
	 * Add integration hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function boot() {
		if ( ! $this->is_active() ) {
			return;
		}

		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * Get option.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_option( $option, $default = null ) {
		return $this->plugin->get_option( $option, $default );
	}

	/**
	 * Maybe remove tickets from context.
	 *
	 * @since 1.0.0
	 * @param array  $context
	 * @param string $file
	 * @param array  $name
	 * @param object $obj
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
			$context['has_tickets_on_sale'] = false;
		}

		return $context;
	}

	/**
	 * If user can't purchase tickets, replace quantity fields.
	 *
	 * @since 1.0.0
	 * @param string $html
	 * @param string $file
	 * @param array  $name
	 * @param object $obj
	 * @return string
	 */
	public function ticket_quantity_template( $html, $file, $name, $obj ) {
		$ticket = $obj->get( 'ticket' );

		if ( $this->can_purchase( $ticket->ID ) ) {
			return $html;
		}

		return $this->plugin->get_template()->template( 'ticket_quantity', [], false );
	}

	/**
	 * If user can't purchase tickets, replace quantity fields.
	 *
	 * @since 1.0.0
	 * @param string $html
	 * @param string $file
	 * @param array  $name
	 * @param object $obj
	 * @return string
	 */
	public function ticket_description_template( $html, $file, $name, $obj ) {
		$ticket = $obj->get( 'ticket' );

		if ( $this->can_purchase( $ticket->ID ) ) {
			return $html;
		}

		$message = $this->get_option( "{$this->get_id()}_members_only_message", esc_html__( "This ticket is for members only.", 'et-members-only-tickets' ) );

		return $this->plugin->get_template()->template( 'ticket_description', ['ticket_id' => $ticket->ID, 'message' => $message], false );
	}

	/**
	 * Filter hidden member tickets from showing up in cost range.
	 *
	 * @since 1.0.0
	 * @param array  $costs
	 * @param int    $post_id
	 * @param string $meta
	 * @param bool   $single
	 * @return array
	 */
	public function filter_cost( $costs, $post_id, $meta, $single ) {
		// If not for the target meta, not single, or no costs, return early.
		if ( '_EventCost' != $meta || $single || empty( $costs )  ) {
			return $costs;
		}

		// Get the tickets
		$tickets_woo = tribe( 'tickets-plus.commerce.woo' );
		$tickets = $tickets_woo->get_tickets( $post_id );

		// Check tickets to see if we should show the cost
		foreach ( $tickets as $ticket ) {
			if ( ! $this->can_view( $ticket->ID ) ) {
				// Is this ticket in the list of costs?
				$key = array_search( $ticket->price, $costs );

				// Remove the value from the list of costs if so.
				if ( false !== $key ) {
					unset( $costs[ $key ] );
				}
				continue;
			}
		}

		return $costs;
	}
}
