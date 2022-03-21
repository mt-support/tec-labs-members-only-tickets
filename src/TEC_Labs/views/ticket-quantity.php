<?php
/**
 * Alternate ticket controls for tickets that can be viewed, but not purchased.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/members-only-tickets/ticket-quantity.php
 *
 * @since 1.0.0
 */
?>
<div class="tribe-common-h4 tribe-tickets__tickets-item-quantity" style="opacity: 0.5;">
	<button
		class="tribe-tickets__tickets-item-quantity-remove"
		type="button"
		style="pointer-events:none;"
		disabled
	>
		-
	</button>

	<div class="tribe-tickets__tickets-item-quantity-number">
		<input
			id="tribe-tickets__tickets-item-quantity-number"
			class="tribe-common-h3 tribe-common-h4--min-medium tribe-tickets__tickets-item-quantity-number-input"
			type="number"
			value="0"
			autocomplete="off"
			disabled
		>
	</div>

	<button
		class="tribe-tickets__tickets-item-quantity-add"
		type="button"
		style="pointer-events:none;"
		disabled
	>
		+
	</button>
</div>
