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

		$container_width = empty( $width ) ? get_post_meta( $id, '_power_bi_width', true ) : $width;
		$container_height = empty( $height ) ? get_post_meta( $id, '_power_bi_height', true ) : $height;

		$powerbi_js = $this->powerbi_js( $id );

		ob_start();
		echo '<div id="powerbi-embedded-'. $id .'" style="height: ' . $container_height . '; width: ' . $container_width . ';"></div>';
		echo $powerbi_js;
		return ob_get_clean();
    }

	public function powerbi_js( $id ) {
		$power_bi_credentials = get_option('power_bi_credentials');

        if( isset( $power_bi_credentials['access_token'] ) ) {
            $access_token = $power_bi_credentials['access_token'];
        } else {
			return;
		}

		// Common metas
		$token_type     = 'Aad';
		$api_url        = "https://app.powerbi.com/";
		$embed_type 	= get_post_meta( $id, '_power_bi_embed_type', true );
		$dashboard_id 	= get_post_meta( $id, '_power_bi_dashboard_id', true );
		$group_id 	    = get_post_meta( $id, '_power_bi_group_id', true );
		$report_id 		= get_post_meta( $id, '_power_bi_report_id', true );
		$dataset_id 	= get_post_meta( $id, '_power_bi_dataset_id', true );

		$filter_pane 	 = get_post_meta( $id, '_power_bi_filter_pane', true );
		$page_navigation = get_post_meta( $id, '_power_bi_page_navigation', true );
		$background 	 = get_post_meta( $id, '_power_bi_background', true );
		$language 	     = get_post_meta( $id, '_power_bi_language', true );
		$format_local 	 = get_post_meta( $id, '_power_bi_format_local', true );

		if( 'dashboard' === $embed_type ) {
			$embed_url = $api_url . "dashboardEmbed?dashboardId=" . $dashboard_id . "&groupId=" . $group_id;
		}

		if( 'report' === $embed_type ) {
			$report_mode = get_post_meta( $id, '_power_bi_report_mode', true );
			$page_name 	 = get_post_meta( $id, '_power_bi_page_name', true );

			if ( 'create' === $report_mode ) {
				$embed_url = $api_url . "reportEmbed?groupId=" . $group_id;
			} else {
				$embed_url = $api_url . "reportEmbed?reportId=" . $report_id . "&groupId=" . $group_id;
			}
		}

		if( 'qna' === $embed_type ) {
			$qna_mode       = get_post_meta( $id, '_power_bi_qna_mode', true );
			$input_question = get_post_meta( $id, '_power_bi_input_question', true );

			if ( 'show_a_predefined' === $qna_mode ) {
				$qna_mode = 'ResultOnly';
			} else {
				$qna_mode = 'Interactive';
			}

			$embed_url = $api_url . "qnaEmbed?groupId=" . $group_id;
		}

		if( 'visual' === $embed_type ) {
			$page_name 	 = get_post_meta( $id, '_power_bi_page_name', true );
			$visual_name = get_post_meta( $id, '_power_bi_visual_name', true );
			$embed_url   = $api_url . "reportEmbed?reportId=" . $report_id .  "&groupId=" . $group_id;
		}

		if( 'tile' === $embed_type ) {
			$tile_id   = get_post_meta( $id, '_power_bi_tile_id', true );
			$embed_url = $api_url . "embed?dashboardId=" . $dashboard_id . "&tileId=" . $tile_id . "&groupId=" . $group_id;
		}

		ob_start();
		?>
		<script type="text/javascript">
			
			(function( $ ) {

				"use strict";
				$(document).ready(function() {
					var models = window['powerbi-client'].models;	
					//This is where we get the auth token
					var restURL = "<?php echo get_rest_url('','wp/v2/powerbi/getToken'); ?>";
					var tmpdata = jQuery.get({
						url: restURL,
						type: 'post',
						//Needs to be parameterized and corrected, if
						data: "grant_type=client_credentials&client_id=<CLIENT_ID>&client_secret=<CLIENT_SECRET>&resource=https://analysis.windows.net/powerbi/api",
						dataType:'jso application/x-www-form-urlencodedn',
						async:false,
						success: function(data) {
							var access_token = data.token
						}
					});

					var access_token = tmpdata.responseJSON.token;
					access_token = access_token.replace(/"/g,"");
					
					
					 //console.log('New Access Token:  ' + access_token );
					// sessionStorage.setItem('access_token', 'access_token' );
					sessionStorage.setItem('access_token', access_token );
					// console.log(sessionStorage.getItem('access_token'));
				
					var embedConfiguration = {
						//these may need to change, I used type=Report / tokenType= EMBED
						type: '<?php echo $embed_type; ?>',
						embedUrl: '<?php echo $embed_url; ?>',
						tokenType: models.TokenType.<?php echo $token_type; ?>,
						accessToken: access_token,
						settings: {
							filterPaneEnabled: <?php echo ($filter_pane ? 'true': 'false'); ?>,
							navContentPaneEnabled: <?php echo ($page_navigation ? 'true': 'false'); ?>,
                            <?php if( isset( $background ) ): ?>
							<?php if ( !empty( $background ) ) : ?>
								background: <?php echo $background; ?>,
							<?php endif; ?>
                            <?php else : ?>
                                background: models.BackgroundType.Transparent,
                            <?php endif; ?>

							localeSettings: {
								language: '<?php echo $language; ?>',
								formatLocale: '<?php echo $format_local; ?>'
							}
						},

						<?php if ('dashboard' === $embed_type) : ?>
						dashboardId: '<?php echo $dashboard_id; ?>',
						<?php endif; ?>

						<?php if ('report' === $embed_type) : ?>
						id: '<?php echo $report_id; ?>',
						pageName: '<?php echo $page_name; ?>',
						<?php endif; ?>

						<?php if ('qna' === $embed_type) : ?>
						viewMode: models.QnaMode['<?php echo $qna_mode; ?>'],
						datasetIds: ['<?php echo $dataset_id; ?>'],
						question: '<?php echo $input_question; ?>',
						<?php endif; ?>

						<?php if ('visual' === $embed_type) : ?>
						pageName: '<?php echo $page_name; ?>',
						visualName: '<?php echo $visual_name; ?>',
						id: '<?php echo $report_id; ?>',
						<?php endif; ?>

						<?php if ('tile' === $embed_type) : ?>
						id: '<?php echo $tile_id; ?>',
						dashboardId: '<?php echo $dashboard_id; ?>',
						<?php endif; ?>

						<?php if ( 'edit' === $report_mode && 'report' === $embed_type ) : ?>
						viewMode: models.ViewMode.Edit,
						permissions: models.Permissions.All,
						<?php endif; ?>

						<?php if ( 'create' === $report_mode && 'report' === $embed_type ) : ?>
						datasetId: '<?php echo $dataset_id; ?>',
						permissions: models.Permissions.All,
						<?php endif; ?>
					};

					// ****
					// apply filters before report load
					// ****
					
					// get query string and convert to powerbi filter
					var urlParams = new URLSearchParams(window.location.search);

					// if filters value exists parse the encoded string to JSON and set as filter
					if ( urlParams.has('filters') ) {
						var urlFilters = JSON.parse(urlParams.get("filters"));
						var filters = urlFilters;
						
						embedConfiguration.filters = filters;
					}

					// ****
					// apply slicers before report load
					// ****
					if ( urlParams.has("slicers") ) {
						var urlSlicers = JSON.parse(urlParams.get("slicers"));
						embedConfiguration.slicers = urlSlicers;
					}
					
					var $container = $('#powerbi-embedded-<?php echo $id; ?>');

					<?php if ( 'create' === $report_mode && 'report' === $embed_type ) : ?>
						var report = powerbi.createReport($container.get(0), embedConfiguration);
					<?php else: ?>
						var report = powerbi.embed($container.get(0), embedConfiguration);

						// set timeOut to refresh token
						report.on("loaded", function(event) {
							function test(report) {
								setTimeout(function() {
									updateToken().then(function(data)  {
										console.log("Resetting token: " + report.getAccessToken());
										// console.log(data);
										report.setAccessToken(data)
											.then(function(resp) {
												console.log("New token: " + report.getAccessToken());
												sessionStorage.setItem('access_token', report.getAccessToken() );
											})
											.catch(function(error) {console.log(error)} );
										
										test(report);
									}).catch(function(error) { console.log(error)});

								}, 1000*60*10);
							}

							test(report);

						});

						function updateToken() {
							
							var restURL = "<?php echo get_rest_url('','wp/v2/powerbi/getToken'); ?>";
							return new Promise(function(resolve, reject) {
								$.ajax({
									url : restURL,
									method : "GET",
								}).done(function(response) {
									resolve(response);
								}).fail(function(error) {
									console.log("Error: " + error);
									reject(error);
								});
							});
						}
							// var xhttp = new XMLHttpRequest();
							// xhttp.onreadystatechange = function() {
							// 	if (this.readyState == 4 && this.status == 200) {
							// 		// console.log("Resetting token: " + report.getAccessToken());
							// 		// report.setAccessToken(this.responseText);
							// 		// console.log("New token: " + report.getAccessToken());
							// 		return this.responseText;
							// 	}
							// };
							// setTimeout(() => {
							// 	xhttp.open("GET", restURL, true);
							// 	xhttp.setRequestHeader("Content-type", "application/json");
							// 	xhttp.send();
							// 	updateToken();
							// }, 1000*60*55);
					
					<?php endif; ?>
				})
				
			})(jQuery);
		</script>
		<?php
		return ob_get_clean();
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
