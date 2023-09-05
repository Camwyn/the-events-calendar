<?php
/**
 * Class that handles interfacing Settings debug info with core Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\Site_Health\Info_Section_Abstract;
use TEC\Common\Site_Health\Factory;
use Tribe__Support as Support;
use Tribe__Date_Utils as Dates;

/**
 * Class Site_Health
 *
 * @since   TBD
 * @package TEC\Events\Site_Health
 */
class Settings_Section extends Info_Section_Abstract {
	/**
	 * Slug for the section.
	 *
	 * @since TBD
	 *
	 * @var string $slug
	 */
	protected static string $slug = 'the-events-calendar-settings';

	/**
	 * Label for the section.
	 *
	 * @since TBD
	 *
	 * @var string $label
	 */
	protected string $label;

	/**
	 * If we should show the count of fields in the site health info page.
	 *
	 * @since TBD
	 *
	 * @var bool $show_count
	 */
	protected bool $show_count = false;

	/**
	 * If this section is private.
	 *
	 * @since TBD
	 *
	 * @var bool $is_private
	 */
	protected bool $is_private = false;

	/**
	 * Description for the section.
	 *
	 * @since TBD
	 *
	 * @var string $description
	 */
	protected string $description;

	/**
	 * Sets up the section and internally add the fields.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->label       = esc_html__( 'The Events Calendar: Settings Overview', 'the-events-calendar' );
		$this->description = esc_html__( 'This section contains information on your Events Calendar Settings.', 'the-events-calendar' );
		$this->add_fields();
	}

	/**
	 * Generates and adds our fields to the section.
	 * This section ONLY CONTAINS TEC Settings!
	 *
	 * @since TBD
	 *
	 * @param array $info The debug information to be added to the core information page.
	 *
	 * @return array The debug information to be added to the core information page.
	 */
	public function add_fields(): void {
		$support     = Support::getInstance();
		$system_info = $support->getSupportStats();
		$settings    = $system_info['Settings'];
		$ignore = [
			'google_maps_js_api_key' => true,
			'custom-fields' => true,
			'custom-fields-max-index' => true,
		];

		foreach( $settings as $key => $value ) {
			// Put custom fields with ECP.
			if ( isset( $ignore[ $key ] )  ) {
				continue;
			}

			$value = self::normalize_and_sanitize( $value, $key );

			$this->add_field(
				Factory::generate_generic_field(
					sanitize_title_with_dashes( $key ),
					esc_html( $key ),
					$value,
					501
				)
			);
		}

		$this->add_field(
			Factory::generate_generic_field(
				sanitize_title_with_dashes( 'custom-fields-max-index' ),
				esc_html( 'custom-fields-max-index' ),
				$settings['custom-fields-max-index'],
				501
			)
		);



		foreach( $settings['custom-fields'] as $key => $value ) {
			$name = $value['name'];
			unset( $value['name'] );
			$this->add_field(
				Factory::generate_generic_field(
					sanitize_title_with_dashes( $name ),
					esc_html( 'Custom Field: ' . $name ),
					$value,
					501
				)
			);
		}
	}

	public static function normalize_and_sanitize( $value, $key = null ) {
		if ( is_array( $value ) ) {
			if ( count( $value ) === 1) {
				return self::normalize_and_sanitize( array_pop( $value ) );
			}

			$value = array_map(
				function( $value ) {
					return self::normalize_and_sanitize( $value );
				},
				$value );

			return $value;
		}

		if ( is_null( $value ) ) {
			return 'default';
		}

		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( is_string( $value ) && ! empty( $value ) ) {
			return trim( $value );
		}

		if ( tribe_is_truthy( $value ) ) {
			$value = 'true';
		} elseif ( tribe_is_falsy( $value ) ) {
			$value = 'false';
		} elseif ( $value === '' ) {
			$value = 'default';
		}

		return esc_html( $value );
	}
}
