<?php
/**
 * Main class.
 *
 * @package Power_Bi
 */

if ( ! class_exists( 'Power_Bi' ) ) {

	/**
	 * Handles core plugin hooks and action setup.
	 */
	class Power_Bi {

		/**
		 * Returns the instance.
		 */
		public static function get_instance() {

			static $instance = null;

			if ( is_null( $instance ) ) {
				$instance = new self();
				$instance->includes();
				$instance->setup_actions();
			}

			return $instance;
		}

		/**
		 * Constructor method.
		 */
		private function __construct() {
			if ( defined( 'POWER_BI_VERSION' ) ) {
				$this->version = POWER_BI_VERSION;
			} else {
				$this->version = '1.0.0';
			}

			$this->plugin_name = 'power-bi';
		}

		/**
		 * Loads include and admin files for the plugin.
		 */
		private function includes() {
			// Includes.
			include_once POWER_BI_PLUGIN_DIR . '/lib/cmb2/init.php';
			include_once POWER_BI_PLUGIN_DIR . '/lib/cmb2-conditionals/cmb2-conditionals.php';

			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-post-types.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-settings.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-oauth.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-shortcodes.php';

			include_once POWER_BI_PLUGIN_DIR . '/includes/functions-power-bi-settings.php';
		}

		/**
		 * Sets up initial actions.
		 *
		 * @since  1.0.0
		 * @access private
		 * @return void
		 */
		private function setup_actions() {
			// Register scripts, styles, and fonts.
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );

			add_filter( 'the_content', array( $this, 'insert_shortcode' ) );
		}

		/**
		 * Filter content and insert shortcode.
		 */
		public function insert_shortcode( $content ) {

			if ( is_singular( 'powerbi' ) ) {
				return do_shortcode( '[powerbi id="' . get_the_ID() . '"]' );
			} else {
				return $content;
			}

		}

		/**
		 * Enqueue scripts.
		 */
		public function scripts() {
			wp_enqueue_script( $this->plugin_name . '-main', POWER_BI_PLUGIN_URL . '/assets/js/powerbi.min.js', array( 'jquery' ), '1232018', false );
		}

		/**
		 * Enqueue styles.
		 */
		public function styles() {
		}
	}
}
