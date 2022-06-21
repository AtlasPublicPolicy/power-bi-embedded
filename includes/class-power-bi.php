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
				$this->version = '1.1.4';
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

            include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-endpoints.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-post-types.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-settings.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-oauth.php';
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-shortcodes.php';

			include_once POWER_BI_PLUGIN_DIR . '/includes/functions-power-bi-settings.php';
            
			// Added Schedule Resource Option
			include_once POWER_BI_PLUGIN_DIR . '/includes/class-power-bi-schedule-resources.php';
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
			add_action('wp_enqueue_scripts', array($this, 'scripts'));
			add_action('wp_enqueue_scripts', array($this, 'styles'));
			add_filter('the_content', array($this, 'displayPowerBIPostContent'));
			add_action('update_option_power_bi_settings', function( $old_value, $value ) { delete_transient('t_token'); }, 10, 2);
		}


		/**
		 * Display Report on powerbi post type single posts.
		 */
		public function displayPowerBIPostContent( $content ) {
			if ( is_singular( 'powerbi' ) ) return $content . $this->getReportHTML();
            return $content;
		}

        public function getReportHTML() {
            $post_id = get_the_ID();
            $container_width = esc_attr(get_post_meta( $post_id, '_power_bi_width', true ));
            $container_height = esc_attr(get_post_meta( $post_id, '_power_bi_height', true ));
            return '<div id="powerbi-embedded-'. $post_id .'" class="powerbi-embed" style="height: ' . $container_height . '; width: ' . $container_width . ';" data-postid="' . $post_id . '"></div>';
        }

        /**
         * Get named setting
         */
        public function getSetting($setting){
            $settings = get_option('power_bi_settings');
            if(!isset($settings[$setting])) return false;
            return $settings[$setting];
        }

		/**
		 * Enqueue scripts.
		 */
		public function scripts() {
			wp_register_script($this->plugin_name . '-main', POWER_BI_PLUGIN_URL . '/assets/js/powerbi.min.js', array('jquery'), filemtime(POWER_BI_PLUGIN_DIR . '/assets/js/powerbi.min.js'), true );
			wp_enqueue_script( $this->plugin_name . '-main');
            $args = [
                'rest_url' => get_site_url(null, 'wp-json/powerbi/v1'),
                'mobile_breakpoint' => $this->getSetting('powerbi_mobile_breakpoint'),
            ];
            wp_localize_script($this->plugin_name . '-main', 'powerBiEmbed', $args);
            wp_enqueue_script($this->plugin_name . '-report', POWER_BI_PLUGIN_URL . '/assets/js/powerbi-report.js', array(  ), filemtime(POWER_BI_PLUGIN_DIR. '/assets/js/powerbi-report.js'), true );
			//wp_enqueue_script( $this->plugin_name . '-main', POWER_BI_PLUGIN_URL . '/assets/js/powerbi.min.js', array( 'jquery' ), filemtime(POWER_BI_PLUGIN_URL . '/assets/js/powerbi.min.js'), false );
			wp_enqueue_script( 'url-search-params-polyfill', POWER_BI_PLUGIN_URL . '/assets/js/url-search-params-polyfill.js', array(  ), filemtime(POWER_BI_PLUGIN_DIR. '/assets/js/url-search-params-polyfill.js'), true );
		}

		/**
		 * Enqueue styles.
		 */
		public function styles() {
		}
	}
}
