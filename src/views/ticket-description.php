<?php
/**
 * Message that displays for tickets that can be viewed by non-members, but not purchased.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/members-only-tickets/ticket-description.php
 *
 * @since 1.0.0
 *
 * @var int $ticket_id  The WooCommerce product ID of the ticket.
 * @var string $message The text to display in the ticket description.
 */
?>
<div
	id="<?php echo esc_attr( "tribe__details__content--{$ticket_id}" ); ?>"
	class="tribe-common-b2 tribe-common-b3--min-medium tribe-tickets__tickets-item-details-content"
	style="display: block;"
>
	<?php echo wp_kses_post( $message ); ?>
</div>
