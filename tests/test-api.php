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
			 * (Custom attributes order goes before regular order.
			 */

			// Custom attribute.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="foo:bar"]',
				'compare' => '<button foo="bar" class="ocs-trigger ocs-toggle ocs-toggle-left">test</button>',
			),

			// Custom attributes.
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attr="foo:bar;multi:multiple words test"]',
				'compare' => '<button foo="bar" multi="multiple words test" class="ocs-trigger ocs-toggle ocs-toggle-left">test</button>',
			),

			// Custom empty attribute (alias).
			array(
				'shortcode' => '[ocs_trigger id="left" text="test" attributes="foo:bar;empty"]',
				'compare' => '<button foo="bar" empty="" class="ocs-trigger ocs-toggle ocs-toggle-left">test</button>',
			),

			/**
			 * Singleton elements.
			 */

			// Image (using custom attribute, with faulty end `; `) + text as alt.
			array(
				'shortcode' => '[ocs_trigger id="left" element="img" attr="src:http://your.domain/image.jpg; " text="test"]',
				'compare' => '<img src="http://your.domain/image.jpg" alt="test" class="ocs-trigger ocs-toggle ocs-toggle-left" />',
			),

			// Image as nested shortcode (using div as the element). It will ignore the `text` parameter.
			array(
				'shortcode' => '[ocs_trigger id="left" element="div" text="test"]<img src="http://your.domain/image.jpg" />[/ocs_trigger]',
				'compare' => '<div class="ocs-trigger ocs-toggle ocs-toggle-left"><img src="http://your.domain/image.jpg" /></div>',
			),

		);

		foreach ( $tests as $test ) {
			$this->assertEquals( $test['compare'], do_shortcode( $test['shortcode'] ) );
		}

	}
}
