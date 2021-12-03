<?php

namespace Tribe\Extensions\Membersonlytickets\Integrations;

/**
 * Class Integration_Interface
 *
 * @since   TBD
 *
 * @package Tribe\Extensions\Membersonlytickets\Integrations
 */
interface Integration_Interface {
	/**
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return boolean
	 */
	public function is_active();

	/**
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function add_actions();

	/**
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function add_filters();

	/**
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public static function get_id();

	/**
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param int|string $product_id
	 *
	 * @return boolean
	 */
	public function can_view( $product_id );

	/**
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param int|string $product_id
	 *
	 * @return boolean
	 */
	public function can_purchase( $product_id );
}