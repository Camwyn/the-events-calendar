<?php
/**
 * Handles registering all Assets for the Events V2 Views
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Plugin;

/**
 * Register
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
class Assets extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.2
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-calendar-views-v2',
			'views/tribe-events-v2.css',
			[ 'tribe-common-style', 'tribe-tooltipster-css' ], // @todo: check if we're including tooltips only in month view.
			'wp_enqueue_scripts',
			[ 'priority' => 10 ]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-manager',
			'views/manager.js',
			[ 'jquery', 'tribe-common', 'tribe-query-string', 'underscore' ],
			null // prevent it from loading
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-scripts',
			'views/scripts.js',
			[ 'jquery', 'tribe-common', 'tribe-tooltipster' ], // @todo: check if we're including tooltips only in month view.
			'wp_enqueue_scripts',
			[ 'priority' => 10 ]
		);
	}
}