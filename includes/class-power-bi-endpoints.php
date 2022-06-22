<?php
if (!defined('ABSPATH')) exit();
/**
 * class-theme-endpoints.php
 * 
 * add all theme functions
 */

class Power_Bi_Endpoints{

    /**
	 * Returns the instance.
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
			$instance->setup_actions();
		}

		return $instance;
	}

    /**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {
		$routes = ['add_get_report_data_endpoint','register_get_powerbi_access_token'];
        foreach($routes as $route){
            add_action('rest_api_init', [$this,$route]);
        }
	}

    //construct
    private function __construct(){}

    /**
	 * Get Report Data to make api call.
	 *
	 * @since  1.1.5
	 * @access public
	 * @return mixed
	 */
    public function get_report_data($data){
        if(!isset($data['post_id'])) return false;
        $power_bi_credentials = get_option('power_bi_credentials');
        $response = [];
        if(!isset( $power_bi_credentials['access_token'])) return false;
        $response['access_token'] = $power_bi_credentials['access_token'];
        $post_id = sanitize_text_field($data['post_id']);
		// Common metas
		$response['token_type']      = 'Aad';
		$response['api_url']         = "https://app.powerbi.com/";
		$response['embed_type'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_embed_type', true ));
		$response['dashboard_id'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_dashboard_id', true ));
		$response['group_id'] 	     = esc_attr(get_post_meta( $post_id, '_power_bi_group_id', true ));
		$response['report_id'] 		 = esc_attr(get_post_meta( $post_id, '_power_bi_report_id', true ));
		$response['dataset_id'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_dataset_id', true ));
		$response['filter_pane'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_filter_pane', true ));
		$response['page_navigation'] = esc_attr(get_post_meta( $post_id, '_power_bi_page_navigation', true ));
		$response['background'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_background', true ));
		$response['language'] 	     = esc_attr(get_post_meta( $post_id, '_power_bi_language', true ));
		$response['format_local'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_format_local', true ));
        $response['page_navigation'] = esc_attr(get_post_meta( $post_id, '_power_bi_page_navigation', true ));
        $response['mobile_width'] = esc_attr(get_post_meta( $post_id, '_power_bi_mobile_width', true ));
        $response['mobile_height'] = esc_attr(get_post_meta( $post_id, '_power_bi_mobile_height', true ));

		if( 'dashboard' === $response['embed_type'] ) {
			$response['embed_url'] = $response['api_url'] . "dashboardEmbed?dashboardId=" . $response['dashboard_id'] . "&groupId=" . $response['group_id'];
		}

		if( 'report' === $response['embed_type'] ) {
			$response['report_mode'] = esc_attr(get_post_meta( $post_id, '_power_bi_report_mode', true ));
			$response['page_name'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_page_name', true ));

			if ( 'create' === $response['report_mode'] ) {
				$response['embed_url'] = $response['api_url'] . "reportEmbed?groupId=" . $response['group_id'];
			} else {
				$response['embed_url'] = $response['api_url'] . "reportEmbed?reportId=" . $response['report_id'] . "&groupId=" . $response['group_id'];
			}
		}

		if( 'qna' === $response['embed_type'] ) {
			$response['qna_mode']       = esc_attr(get_post_meta( $post_id, '_power_bi_qna_mode', true ));
			$response['input_question'] = esc_attr(get_post_meta( $post_id, '_power_bi_input_question', true ));

			if ( 'show_a_predefined' === $response['qna_mode'] ) {
				$response['qna_mode'] = 'ResultOnly';
			} else {
				$response['qna_mode'] = 'Interactive';
			}

			$response['embed_url'] = $response['api_url'] . "qnaEmbed?groupId=" . $response['group_id'];
		}

		if( 'visual' === $response['embed_type'] ) {
			$response['page_name'] 	 = esc_attr(get_post_meta( $post_id, '_power_bi_page_name', true ));
			$response['visual_name'] = esc_attr(get_post_meta( $post_id, '_power_bi_visual_name', true ));
			$response['embed_url']   = $response['api_url'] . "reportEmbed?reportId=" . $response['report_id'] .  "&groupId=" . $response['group_id'];
		}

		if( 'tile' === $response['embed_type'] ) {
			$response['tile_id']   = esc_attr(get_post_meta( $post_id, '_power_bi_tile_id', true ));
			$response['embed_url'] = $response['api_url'] . "embed?dashboardId=" . $response['dashboard_id'] . "&tileId=" . $response['tile_id'] . "&groupId=" . $response['group_id'];
		}
        nocache_headers();
        return new WP_REST_Response(!(empty($response)) ? $response : false);
    }

    public function add_get_report_data_endpoint(){
        register_rest_route('powerbi/v1', 'getReportData', [
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => [$this, 'get_report_data'],
            'permission_callback' => function(){
                return true;
            }
        ]);
    }

    public function get_powerbi_access_token() {
        $oauth = Power_Bi_Oauth::get_instance();
        $returnObject = $oauth->get_token();
        nocache_headers();
        return new WP_REST_Response($returnObject['access_token']);
    }

    /**
     * Register a REST route to get a new access token
     * @since 2.0.0
     * @access private
     * @return void
     */
    public function register_get_powerbi_access_token() {
        register_rest_route( 'powerbi/v1', '/getToken', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => array ($this, 'get_powerbi_access_token'),
            'permission_callback' => function(){
                return true;
            }
        ));
    }
    
}

Power_Bi_Endpoints::get_instance();