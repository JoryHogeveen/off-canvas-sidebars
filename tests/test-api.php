<?php
/**
 * Off-Canvas Sidebars - Unit tests for plugin API
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

class OCS_API_UnitTest extends WP_UnitTestCase {

	/**
	 * Tests the following functions (through the API):
	 * @see shortcode_ocs_trigger()
	 * @see the_ocs_control_trigger()
	 * @see OCS_Off_Canvas_Sidebars_Frontend::do_control_trigger()
	 * @see off_canvas_sidebars_parse_attr_string()
	 */
	function test_shortcode_ocs_trigger() {

		// Create two sidebars to start te tests.
		OCS_UnitTest_Factory::load();
		OCS_UnitTest_Factory::$settings->set_settings(
			array(
				'sidebars' => array(
					'left' => array(),
					'right' => array(),
				)
			)
		);

		$tests = array(

			// Text only.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left">test</button>',
			),

			// Icon only.
			array(
				'shortcode' => '[ocs_trigger id="left" icon="dashicons dashicons-yes"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left"><span class="icon dashicons dashicons-yes"></span></button>',
			),

			// Text + Icon.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" icon="dashicons dashicons-yes"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left"><span class="icon dashicons dashicons-yes"></span><span class="label">test</span></button>',
			),

			// Text + Icon after.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" icon="dashicons dashicons-yes" icon_location="after"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left"><span class="label">test</span><span class="icon dashicons dashicons-yes"></span></button>',
			),

			// Nested + Different action.
			array(
				'shortcode' => '[ocs_trigger id="right" action="open" element="button"]test[/ocs_trigger]',
				'compare' => '<button class="ocs-trigger ocs-open ocs-open-right">test</button>',
			),

			// Custom classes.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" class="foo bar"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left foo bar">test</button>',
			),

			// Custom classes (alias).
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" classes="foo bar"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left foo bar">test</button>',
			),

			// Other (non-singleton) elements.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" element="span"]',
				'compare' => '<span class="ocs-trigger ocs-toggle ocs-toggle-left">test</span>',
			),

			/**
			 * (Custom attributes order goes before regular order but class is always first).
			 */

			// Custom attribute.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="foo:bar"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left" foo="bar">test</button>',
			),

			// Custom attributes.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="foo:bar;multi:multiple words test"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left" foo="bar" multi="multiple words test">test</button>',
			),

			// Custom empty attribute (alias).
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attributes="foo:bar;empty"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left" foo="bar" empty="">test</button>',
			),

			/**
			 * Singleton elements.
			 */

			// Image (using custom attribute, with faulty end `; `) + text as alt.
			array(
				'shortcode' => '[ocs_trigger id="left" element="img" attr="src:http://your.domain/image.jpg; " text="test"]',
				'compare' => '<img class="ocs-trigger ocs-toggle ocs-toggle-left" src="http://your.domain/image.jpg" alt="test" />',
			),

			// Image as nested shortcode (using div as the element). It will ignore the `text` parameter.
			array(
				'shortcode' => '[ocs_trigger id="left" element="div" text="test"]<img src="http://your.domain/image.jpg" />[/ocs_trigger]',
				'compare' => '<div class="ocs-trigger ocs-toggle ocs-toggle-left"><img src="http://your.domain/image.jpg" /></div>',
			),

			/**
			 * Link elements XFN.
			 */

			array(
				'shortcode' => '[ocs_trigger id="left" text="test" element="a"]',
				'compare' => '<a class="ocs-trigger ocs-toggle ocs-toggle-left" rel="nofollow">test</a>',
			),

			array(
				'shortcode' => '[ocs_trigger id="left" text="test" element="a" attr="rel: me"]',
				'compare' => '<a class="ocs-trigger ocs-toggle ocs-toggle-left" rel="me">test</a>',
			),

			/**
			 * URL attribute sanitization (javascript: scheme should be removed).
			 */

			// Valid HTTP URL is kept.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" element="a" attr="href:https://your.domain/page"]',
				'compare' => '<a class="ocs-trigger ocs-toggle ocs-toggle-left" href="https://your.domain/page" rel="nofollow">test</a>',
			),

			// Encoded javascript: URL is removed.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" element="a" attr="href:javascript:alert%28document.domain%29"]',
				'compare' => '<a class="ocs-trigger ocs-toggle ocs-toggle-left" rel="nofollow">test</a>',
			),

			// Mixed case javascript: URL is removed.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" element="a" attr="href:JavaScript:alert%28document.domain%29"]',
				'compare' => '<a class="ocs-trigger ocs-toggle ocs-toggle-left" rel="nofollow">test</a>',
			),

			// Non-URL attributes (exempt) may legitimately contain a colon value.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="style:color:red"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left" style="color:red">test</button>',
			),

			// data-* attributes are exempt from protocol validation.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="data-config:mode:dark"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left" data-config="mode:dark">test</button>',
			),

			// Unknown attribute with a scheme-like value fails closed (value dropped).
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="foo:bar:baz"]',
				'compare' => '<button class="ocs-trigger ocs-toggle ocs-toggle-left">test</button>',
			),

		);

		foreach ( $tests as $test ) {
			$this->assertEquals( $test['compare'], do_shortcode( $test['shortcode'] ) );
		}

	}

	/**
	 * Tests that the allowed URL protocols can be extended per attribute via filter.
	 * @see off_canvas_sidebars_parse_attr_string()
	 */
	function test_shortcode_ocs_allowed_url_protocols_filter() {

		OCS_UnitTest_Factory::load();
		OCS_UnitTest_Factory::$settings->set_settings(
			array(
				'sidebars' => array(
					'left' => array(),
				)
			)
		);

		$filter = function( $protocols, $name, $value ) {
			if ( 'src' === $name ) {
				$protocols[] = 'custom';
			}
			return $protocols;
		};
		add_filter( 'ocs_allowed_url_protocols', $filter, 10, 3 );

		// The custom protocol is accepted for src because of the filter.
		$this->assertEquals(
			'<img class="ocs-trigger ocs-toggle ocs-toggle-left" src="custom:image.png" alt="" />',
			do_shortcode( '[ocs_trigger id="left" element="img" attr="src:custom:image.png"]' )
		);

		// javascript: is still rejected, even for src.
		$this->assertEquals(
			'<img class="ocs-trigger ocs-toggle ocs-toggle-left" alt="" />',
			do_shortcode( '[ocs_trigger id="left" element="img" attr="src:javascript:alert%28document.domain%29"]' )
		);

		remove_filter( 'ocs_allowed_url_protocols', $filter );
	}
}
