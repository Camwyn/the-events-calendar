<?php
/**
 * Class that handles interfacing with core Site Health.
 *
 * @since   6.1.0
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\Site_Health\Info_Section_Abstract;
use TEC\Common\Site_Health\Factory;
use Tribe\Events\Views\V2\Manager as Manager;
use Tribe__Template as Template;

/**
 * Class Site_Health
 *
 * @since   6.1.0
 * @package TEC\Events\Site_Health
 */
class Info_Section extends Info_Section_Abstract {
	/**
	 * Slug for the section.
	 *
	 * @since 6.1.0
	 *
	 * @var string $slug
	 */
	protected static string $slug = 'the-events-calendar';

	/**
	 * Label for the section.
	 *
	 * @since 6.1.0
	 *
	 * @var string $label
	 */
	protected string $label;

	/**
	 * If we should show the count of fields in the site health info page.
	 *
	 * @since 6.1.0
	 *
	 * @var bool $show_count
	 */
	protected bool $show_count = false;

	/**
	 * If this section is private.
	 *
	 * @since 6.1.0
	 *
	 * @var bool $is_private
	 */
	protected bool $is_private = false;

	/**
	 * Description for the section.
	 *
	 * @since 6.1.0
	 *
	 * @var string $description
	 */
	protected string $description;

	/**
	 * Sets up the section and internally add the fields.
	 *
	 * @since 6.1.0
	 */
	public function __construct() {
		$this->label       = esc_html__( 'The Events Calendar', 'the-events-calendar' );
		$this->description = esc_html__( 'This section contains information on The Events Calendar Plugin.', 'the-events-calendar' );
		$this->add_fields();
	}

	/**
	 * Generates and adds our fields to the section.
	 *
	 * @since 6.1.0
	 *
	 * @param array $info The debug information to be added to the core information page.
	 *
	 * @return array The debug information to be added to the core information page.
	 */
	public function add_fields(): void {
		$plural_events_label = tribe_get_event_label_plural_lowercase();

		$this->add_field(
			Factory::generate_post_status_count_field(
				'event_counts',
				\Tribe__Events__Main::POSTTYPE,
				10
			)
		);

		$this->add_field(
			Factory::generate_post_status_count_field(
				'published_organizers',
				\Tribe__Events__Organizer::POSTTYPE,
				20
			)
		);

		$this->add_field(
			Factory::generate_post_status_count_field(
				'published_venues',
				\Tribe__Events__Venue::POSTTYPE,
				30
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'event_block_editor',
				sprintf(
					esc_html__( 'Block Editor enabled for %1$s', 'the-events-calendar' ),
					$plural_events_label
				),
				tec_bool_to_string( tribe_get_option( 'toggle_blocks_editor', false ) ),
				40
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'include_events_in_loop',
				sprintf(
					esc_html__( 'Include %1$s in main blog loop', 'the-events-calendar' ),
					$plural_events_label
				),
				tec_bool_to_string( tribe_get_option( 'showEventsInMainLoop', false ) ),
				50
			)
		);

		$view_manager     = tribe( Manager::class );
		$active_views = array_map(
			static function( $view ) use ( $view_manager ) {
				return $view_manager->get_view_label_by_class( $view );
			},
			$view_manager->get_publicly_visible_views( true )
		);

		$this->add_field(
			Factory::generate_generic_field(
				'enabled_views',
				esc_html__( 'Views', 'the-events-calendar' ),
				array_values( $active_views ),
				60
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'default_view',
				esc_html__( 'Default view', 'the-events-calendar' ),
				$view_manager->get_default_view_slug(),
				70
			)
		);

		$import_query = new \WP_Query(
			[
				'post_type' => 'tribe_events',
				'meta_key' => '_EventOrigin',
				'meta_value' => 'event-aggregator'
			]
		);

		$this->add_field(
			Factory::generate_generic_field(
				'imported_events',
				sprintf(
					esc_html__( 'Total imported %1$s', 'the-events-calendar' ),
					$plural_events_label
				),
				$import_query->found_posts,
				80
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'front_page',
				esc_html__( 'Front page calendar', 'the-events-calendar' ),
				tec_bool_to_string( '-10' === get_option( 'page_on_front' ) ),
				90
			)
		);

		$this->add_field(
			Factory::generate_generic_field(
				'previous_versions',
				esc_html__( 'Previous TEC versions', 'the-events-calendar' ),
				array_filter( (array) tribe_get_option( 'previous_ecp_versions', [] ) ),
				100
			)
		);

		$template = new Template();
		$overrides = array_filter( wp_list_pluck( $template->get_template_override_paths( 'tribe/events' ), 'path' ) );

		$this->add_field(
			Factory::generate_generic_field(
				'template_overrides',
				esc_html__( 'Parent theme template overrides', 'the-events-calendar' ),
				is_dir( $overrides['parent-theme'] ),
				110
			)
		);
		$this->add_field(
			Factory::generate_generic_field(
				'template_overrides',
				esc_html__( 'Child theme template overrides', 'the-events-calendar' ),
				is_dir( $overrides['child-theme'] ),
				110
			)
		);

		$this->add_support_info();
	}


	/**
	 * Adds support info to Site Health
	 *
	 * @since TBD
	 */
	public function add_support_info() {
		$support     = \Tribe__Support::getInstance();
		$system_info = $support->getSupportStats();
		// Skip these here for now as they are redundant with the core Site Health data.
		// Note; these are case-sensitive!
		$ignore = [
			'Home URL',
			'MU Plugins',
			'Multisite',
			'Network Plugins',
			'Permalink Structure',
			'SAPI',
			'PHP version',
			'PHP',
			'Plugins',
			'Server',
			'Settings',
			'Site Language',
			'Site URL',
			'Theme',
			'tribeEnableViews',
			'WordPress version',
			'WP Timezone',
		];

		foreach( $system_info as $key => $value ) {
			if ( in_array( $key, $ignore ) ) {
				continue;
			}

			if ( $key === 'Week Starts On' ) {
				$value = date('l', strtotime( "Sunday +{$value} days" ) );
			}

			if ( tribe_is_truthy( $value ) ) {
				$value = 'true';
			} elseif ( tribe_is_falsy( $value ) ) {
				$value = 'false';
			} elseif ( $value === '' ) {
				$value = 'default';
			}

			$this->add_field(
				Factory::generate_generic_field(
					sanitize_title_with_dashes( $key ),
					esc_html( $key ),
					$value,
					500
				)
			);
		}
	}
}
