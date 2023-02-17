<?php
/**
 * Handles displays and hooks for the Power BI oauth.
 *
 * @package Power_Bi
 */
class Power_Bi_Shortcodes {

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
			$instance->setup_shortcodes();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_shortcodes() {
		add_shortcode( 'powerbi', array( $this, 'power_bi_html' ) );
		add_shortcode( 'powerbi_resource', array( $this, 'power_bi_resource_html' ) );
	}

    public function power_bi_html( $atts ) {
        extract( shortcode_atts( array(
			'id' => '',
			'width' => '',
			'height' => '',
        ), $atts ) );

		if ( empty( $id ) ) {
			return;
		}
		$options = get_power_bi_plugin_settings();

		$container_width = empty( $width ) ? get_post_meta( $id, '_power_bi_width', true ) : $width;
		$container_height = empty( $height ) ? get_post_meta( $id, '_power_bi_height', true ) : $height;
		
		$html = '';
		if ( get_post_meta( $id, '_power_bi_display_breakpoint_notice', true ) ) {
			$notice = get_post_meta( $id, '_power_bi_display_breakpoint_notice_message', true );
			$notice = (!empty( $notice )) ? $notice : $options['powerbi_mobile_breakpoint_notice_display_message'];
			$html .= '<style>.powerbi-embed-notice {display:none} @media screen and (max-width:'. $options['powerbi_mobile_breakpoint'].'px) {.powerbi-embed-notice {display:block;text-align:center;}}</style>';
			$html .= '<div id="powerbi-embedded-notice-'. $id .'" class="powerbi-embed-notice" style="padding:20px;">'.$notice.'</div>';
		}
		
		$html .= '<div id="powerbi-embedded-'. $id .'" class="powerbi-embed" style="height: ' . $container_height . '; width: ' . $container_width . ';" data-postid="' . $id . '"></div>';

		return $html;
    }

	public function power_bi_resource_html( $atts, $c ) {
        extract( shortcode_atts( array(
			'state' => 'Succeeded',
        ), $atts ) );

		if ( empty( $c ) ) {
			return;
		}

		$powerbi_resource = Power_Bi_Schedule_Resources::get_instance();
		$resource_capacity_state = $powerbi_resource->check_resource_capacity_state();

		if ( ! empty( $resource_capacity_state ) ) {
			if ( $state == $resource_capacity_state ) {
				return do_shortcode( $c );
			}
		}
    }
}

Power_Bi_Shortcodes::get_instance();
