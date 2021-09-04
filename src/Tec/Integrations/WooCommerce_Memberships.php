<?php
/**
 * Handles membership checks when using WooCommerce Memberships.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class WooCommerce_Memberships.
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */
class WooCommerce_Memberships extends \tad_DI52_ServiceProvider {

	/**
	 * The integration slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $ID = 'woocommerce_memberships';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( "extension.members_only_tickets.{$this->ID}", $this );
		$this->filters();
	}

	/**
	 * Add filters.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function filters() {
		add_filter( 'tribe_template_context', [ $this, 'remove_tickets_from_context' ], 100, 4 );
		add_filter( 'tribe_template_html:tickets/v2/tickets/item/quantity', [ $this, 'ticket_quantity_template' ], 100, 4 );
	}

	/**
	 * Check if user can view a ticket.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	protected function can_view( $product_id ) {

		if ( ! \wc_memberships_is_product_viewing_restricted( $product_id ) ) {
			return true;
		}

		$user_id = \get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		if ( ! \wc_memberships_user_can( $user_id, 'view', [ 'product' => $product_id ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if user can purchase a ticket.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	protected function can_purchase( $product_id ) {

		if ( ! \wc_memberships_is_product_purchasing_restricted( $product_id ) ) {
			return true;
		}

		$user_id = \get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		if ( ! \wc_memberships_user_can( $user_id, 'purchase', [ 'product' => $product_id ] ) ) {
			return false;
		}

		return true;
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
		if ( 'v2/tickets' !== implode( "/", $name ) ) {
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
}
