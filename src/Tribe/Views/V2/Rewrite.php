<?php
/**
 * Modifies and updates rewrite rules for Views V2.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Rewrite
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Rewrite {

	/**
	 * Filters The Events Calendar rewrite rules to fix and update them, if required.
	 *
	 * @since TBD
	 *
	 * @param array  $bases  An array of rewrite bases that have been generated.
	 * @param string $method The method that's being used to generate the bases; defaults to `regex`.
	 *
	 * @return array<string,array> The filtered rewrite rules, updated or modified if required.
	 */
	public function filter_raw_i18n_slugs( array $bases, $method ) {
		if ( $method !== 'regex' ) {
			return $bases;
		}

		$bases = $this->add_url_encoded_slugs( $bases );

		return $bases;
	}

	/**
	 * Adds the URL encoded version of the slugs to the rewrite rules to ensure rewrites will keep working
	 * in localized installations.
	 *
	 * This method wil "fill-in" wrongly formatted or encoded bases too and order bases so that the `Tribe__Rewrite`
	 * URL resolving methods will, preferably, resolve to the "pretty" (non URL-encoded) and human readable version.
	 *
	 * @since TBD
	 *
	 * @param array<string,array> $bases The raw bases, as generated by The Events Calendar rewrite handler.
	 *
	 * @return array<string,array> The rules, updated to include the URL encoded version of the slugs.
	 */
	protected function add_url_encoded_slugs( $bases ) {
		array_walk( $bases, function ( array &$base_group ) {
			foreach ( $base_group as $value ) {
				$is_encoded = $this->is_encoded( $value );

				if ( $is_encoded ) {
					$encoded = strtolower( $value );
					$decoded = urldecode( $value );
				} else {
					$encoded = strtolower( urlencode( $value ) );
					$decoded = $value;
				}

				if ( $encoded === $decoded ) {
					continue;
				}

				// Some function expect, or provide, uppercase encoding chars, some don't. Cope w/ both.
				$base_group[] = $decoded;
				$base_group[] = strtoupper( $encoded );
				$base_group[] = $encoded;
			}

			// Remove duplicates and put the non-encoded strings first.
			$base_group = array_unique( $base_group );
			usort( $base_group, [ $this, 'sort_by_encoding' ] );
		} );

		return $bases;
	}

	/**
	 * Detects, in a very specific manner, if the string is urlencoded or not.
	 *
	 * Refrain from moving this into a general-purpose function: this detections system makes a number of assumptions
	 * thare are just wrong in other contexts.
	 *
	 * @since TBD
	 *
	 * @param strin $string The string to check for encoding.
	 *
	 * @return bool Whether the strins is encoded or not.
	 */
	protected function is_encoded( $string ) {
		// We assume no localized slug will contain the `%` char as a legit translation.
		return false !== strpos( $string, '%' );
	}

	/**
	 * Sorts a set of English, localize, encoded and not encoded slugs trying to put English and "pretty" first.
	 *
	 * URL encoded versions will be moved down the set, English will be put first, then "pretty" localized versions.
	 *
	 * @since TBD
	 *
	 * @param string $a The first localized slug to check.
	 * @param        $b The second localized slug to check.
	 *
	 * @return int The check result, `0` if the positions should not change, `-1` or `1` to move `$a` before `$b` or
	 *             viceversa.
	 */
	protected function sort_by_encoding( $a, $b ) {
		$a_is_encoded = $this->is_encoded( $a );
		$b_is_encoded = $this->is_encoded( $b );

		if ( $a_is_encoded === $b_is_encoded ) {
			return 0;
		}

		return $a_is_encoded - $b_is_encoded;
	}
}
