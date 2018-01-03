<?php
/**
 * Off-Canvas Sidebars - Class Tab_Importexport
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab import/export
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5
 * @version 0.5
 */
final class OCS_Off_Canvas_Sidebars_Tab_Importexport extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Tab_Importexport
	 * @since  0.3
	 */
	protected static $_instance = null;

	/**
	 * @since   0.1
	 * @since   0.3  Private constructor.
	 * @since   0.5  Protected constructor. Refactor into separate tab classes and methods.
	 * @access  private
	 */
	protected function __construct() {
		$this->tab = 'ocs-importexport';
		$this->name = esc_attr__( 'Import/Export', OCS_DOMAIN );
		parent::__construct();
		$this->maybe_importexport_settings();
	}

	/**
	 * Initialize this tab.
	 * @since  1.5
	 */
	public function init() {
		add_filter( 'ocs_page_form_do_submit', '__return_false' );
		add_filter( 'ocs_page_form_do_settings_fields', '__return_false' );
		add_filter( 'ocs_page_form_do_sections', '__return_false' );
		add_action( 'ocs_page_form', array( $this, 'tab_content' ) );
		add_filter( 'ocs_page_form_action', array( $this, 'ocs_page_form_action' ) );
	}

	/**
	 * Register settings.
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_settings() {
		//parent::register_settings();

		do_action( 'off_canvas_sidebar_settings_importexport' );
	}

	/**
	 * @since   0.5
	 * @return  string
	 */
	public function ocs_page_form_action() {
		return 'themes.php?page=' . off_canvas_sidebars()->get_plugin_key() . '&tab=' . $this->tab;
	}

	/**
	 * Tab content.
	 * @since   0.5
	 */
	public function tab_content() {
		$export_link = add_query_arg( 'action', 'export' );
		$plugin_key = off_canvas_sidebars()->get_plugin_key();
		?>
		<h3><?php esc_html_e( 'Import/Export Settings', OCS_DOMAIN ); ?></h3>

		<p>
			<a class="submit button" href="<?php echo $export_link; ?>">
				<?php esc_attr_e( 'Export Settings', OCS_DOMAIN ); ?>
			</a>
		</p>

		<p>
			<input type="hidden" name="<?php echo $plugin_key; ?>-import" id="<?php echo $plugin_key; ?>-import" value="true" />
			<?php submit_button( esc_attr__( 'Import Settings', OCS_DOMAIN ), 'button', $plugin_key . '-submit', false ); ?>
			<input type="file" name="<?php echo $plugin_key; ?>-import-file" id="<?php echo $plugin_key; ?>-import-file" />
		</p>
		<?php
	}

	/**
	 * Import/Export handler.
	 * @since   0.1
	 */
	public function maybe_importexport_settings() {
		static $done;
		if ( $done ) {
			return;
		}
		$done = true;

		$plugin_key = off_canvas_sidebars()->get_plugin_key();

		// @codingStandardsIgnoreLine
		$get = $_GET; $post = $_POST;

		/**
		 * Check if it is the correct page.
		 * Capability filter documented in $this->load_plugin_data().
		 */
		if ( ! current_user_can( $this->capability ) ||
		     ! isset( $get['page'] ) || $plugin_key !== $get['page'] ||
		     $this->tab !== $this->tab ) {
			return;
		}

		if ( isset( $get['ocs_import_result'] ) ) {

			$result_class = '';
			$ocs_import_result = '';

			switch ( $get['ocs_import_result'] ) {
				case 1:
					$result_class = 'updated';
					$ocs_import_result = esc_attr__( 'Settings Imported', OCS_DOMAIN );
					break;
				case 2:
					$result_class = 'error';
					$ocs_import_result = esc_attr__( 'Invalid Settings File', OCS_DOMAIN );
					break;
				case 3:
					$result_class = 'error';
					$ocs_import_result = esc_attr__( 'No Settings File Selected', OCS_DOMAIN );
					break;
			}

			if ( ! empty( $ocs_import_result ) ) {
				echo '<div class="' . $result_class . '"><p>' . esc_html( $ocs_import_result ) . '</p></div>';
			}

			return;
		}

		// Export settings.
		if ( ! empty( $get['action'] ) && 'export' === $get['action'] ) {
			header( "Content-Disposition: attachment; filename=" . $plugin_key . ".txt" );
			header( 'Content-Type: text/plain; charset=utf-8' );
			$settings = off_canvas_sidebars()->get_settings();

			echo "[START=OCS SETTINGS]\n";
			foreach ( $settings as $id => $text )
				echo "$id\t" . wp_json_encode( $text ) . "\n";
			echo "[STOP=OCS SETTINGS]";
			die();
		}

		// Import settings.
		if ( ! empty( $post[ $plugin_key . '-import' ] ) && ! empty( $_FILES[ $plugin_key . '-import-file' ] ) ) {

			if ( $_FILES[ $plugin_key . '-import-file' ]['tmp_name'] ) {

				// @codingStandardsIgnoreLine
				$import = explode( "\n", file_get_contents( $_FILES[ $plugin_key . '-import-file' ]['tmp_name'] ) );
				if ( "[START=OCS SETTINGS]" === array_shift( $import ) && "[STOP=OCS SETTINGS]" === array_pop( $import ) ) {

					$settings = array();
					foreach ( $import as $import_option ) {
						list( $key, $value ) = explode( "\t", $import_option );
						$settings[ $key ] = json_decode( $value, true );
					}

					// Get the current settings.
					$org_settings = off_canvas_sidebars()->get_settings();

					// Validate and store the new settings.
					OCS_Off_Canvas_Sidebars_Settings::get_instance()->set_settings( $settings );
					$settings = off_canvas_sidebars()->get_settings();

					// Combine the new settings with the original settings.
					$settings = array_merge( $org_settings, $settings );

					// Update database.
					OCS_Off_Canvas_Sidebars_Settings::get_instance()->update_settings( $settings );

					$ocs_import_result = 1;
				} else {
					$ocs_import_result = 2;
				}
			} else {
				$ocs_import_result = 3;
			}

			wp_redirect( admin_url( '/themes.php?page=' . $plugin_key . '&tab=' . $this->tab . '&ocs_import_result=' . esc_attr( $ocs_import_result ) ) );
			die();
		} // End if().

	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Tab_Importexport
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
