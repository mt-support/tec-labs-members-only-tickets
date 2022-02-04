<?php

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Base methods used in all integrations.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */
trait Integration_Traits {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		if ( ! $this->is_active() ) {
			return;
		}

		$this->add_filters();
		$this->add_actions();
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

		$login_url = wp_login_url( get_permalink() );
		$default = sprintf(
			esc_html_x(
				'%s to purchase',
				'placeholder: %s is for login link',
				'et-members-only-tickets'
			),
			'<a href="' . esc_url( $login_url ) . '">' . esc_html__( 'Log in', 'et-members-only-tickets' ) . '</a>'
		);

		$placeholder_styles = 'font-size: 14px; width: 64px; line-height: 1.4; align-self: center;';
		$placeholder_text = apply_filters( 'extension.members_only_tickets.placeholder_text',  $default, $ticket );
		$placeholder_markup = '<div class="tribe-common-h4 tribe-tickets__tickets-item-quantity" style="%s"><span>%s</span></div>';

		return sprintf( $placeholder_markup, esc_attr( $placeholder_styles ), wp_kses( $placeholder_text, 'post' ) );
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
		$tickets = tribe( 'tickets-plus.commerce.woo' )->get_tickets( $post_id );

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