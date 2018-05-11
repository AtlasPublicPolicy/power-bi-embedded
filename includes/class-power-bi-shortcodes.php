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
		$powerbi_credientials = get_option('power_bi_credientials');

        if( isset( $powerbi_credientials['access_token'] ) ) {
            $access_token = $powerbi_credientials['access_token'];
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
		$language 	     = get_post_meta( $id, '_power_bi_language', true );
		$format_local 	 = get_post_meta( $id, '_power_bi_format_local', true );

		if( 'dashboard' === $embed_type ) {
			$embed_url = $api_url . "dashboardEmbed?dashboardId=" . $dashboard_id . "&groupId=" . $group_id;
		}

		if( 'report' === $embed_type ) {
			$report_mode = get_post_meta( $id, '_power_bi_report_mode', true );

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

				var models = window['powerbi-client'].models;

				var embedConfiguration = {
					type: '<?php echo $embed_type; ?>',
					embedUrl: '<?php echo $embed_url; ?>',
					tokenType: models.TokenType.<?php echo $token_type; ?>,
					accessToken: '<?php echo $access_token; ?>',
					settings: {
						filterPaneEnabled: <?php echo ($filter_pane ? 'true': 'false'); ?>,
						navContentPaneEnabled: <?php echo ($page_navigation ? 'true': 'false'); ?>,
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

				var $container = $('#powerbi-embedded-<?php echo $id; ?>');

				<?php if ( 'create' === $report_mode && 'report' === $embed_type ) : ?>
				var report = powerbi.createReport($container.get(0), embedConfiguration);
				<?php else: ?>
				var report = powerbi.embed($container.get(0), embedConfiguration);
				<?php endif; ?>
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

		$access_token = $this->get_azure_access_token();

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://management.azure.com/subscriptions/b6e6a952-b4d5-40df-8dd5-90e826279ce7/resourceGroups/atlas_ev_hub/providers/Microsoft.PowerBIDedicated/capacities/atlasevhub?api-version=2017-10-01",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "Authorization: Bearer " . $access_token,
		    "Cache-Control: no-cache",
		    "Postman-Token: 96d97831-7bea-4cc8-8955-1a97305b1c50"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		ob_start();
		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			$response = json_decode( $response, true );
			$resource_state = $response['properties']['state'];

			if ( $state == $resource_state ) {
				echo do_shortcode( $c );
			}
		}
		return ob_get_clean();
    }

	public function get_azure_access_token() {
		return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6ImlCakwxUmNxemhpeTRmcHhJeGRacW9oTTJZayIsImtpZCI6ImlCakwxUmNxemhpeTRmcHhJeGRacW9oTTJZayJ9.eyJhdWQiOiJodHRwczovL21hbmFnZW1lbnQuYXp1cmUuY29tLyIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzFiYjQ4ZGE0LTMxNDMtNDAzMS1iZGFlLWNjYzA0MDc1MDhmZS8iLCJpYXQiOjE1MjU4NjI0NDIsIm5iZiI6MTUyNTg2MjQ0MiwiZXhwIjoxNTI1ODY2MzQyLCJhY3IiOiIxIiwiYWlvIjoiQVNRQTIvOEhBQUFBdUNic3NFQlAyY3p5WndkbjhZdTZhRVA3VjRDUHlFeFBsYnVDbk5YK3c5RT0iLCJhbXIiOlsicHdkIl0sImFwcGlkIjoiZmJmYzdhNzEtMjU2Yi00NTRhLWJmMjctMTIxNjJmNjMwZTBhIiwiYXBwaWRhY3IiOiIwIiwiZmFtaWx5X25hbWUiOiJHdWVzdCIsImdpdmVuX25hbWUiOiJBdGxhcyIsImlwYWRkciI6IjQzLjI0NS4xMjMuMTc0IiwibmFtZSI6IkF0bGFzIEd1ZXN0Iiwib2lkIjoiOWMwYzY5YWMtNTlkZi00MzYwLWI0ZTktZDdjZDFiZTgzOGMzIiwicHVpZCI6IjEwMDNCRkZEQTMxNDRCODQiLCJzY3AiOiJ1c2VyX2ltcGVyc29uYXRpb24iLCJzdWIiOiI4TmxIUWxmcGJpMEdETU9UWmxSVXlHY2U4dEl4bkhqZWhYUFR0enBaVWFVIiwidGlkIjoiMWJiNDhkYTQtMzE0My00MDMxLWJkYWUtY2NjMDQwNzUwOGZlIiwidW5pcXVlX25hbWUiOiJndWVzdEBhdGxhc3BvbGljeS5jb20iLCJ1cG4iOiJndWVzdEBhdGxhc3BvbGljeS5jb20iLCJ1dGkiOiJLNUliNTFocnpFRzVsUl9IdTZ3OUFBIiwidmVyIjoiMS4wIn0.NPgc8DukL2PunOBEVJXf9Rj3Widw4fc_PEV0ssMPWSRj4gfDtJrbCg-IVOmzpKKvHkYGD-IvWXzLLx9jiRITWnJ71t0oZiPB0mTEfZSef94VSlFB2XeRvtkz55OjMtggi76TNJ0C1--BUbrFUQDVF0MmDELkYt1iKM2Jzeh1xyaMrZeGP9L7g5zdotqtHhCr3LqDq3qS1Xuae_Yd3uIL3kJabjnQv7UlgMIJG1RXG3yA0MBj6fvy4-vJXYHfyHmksaaKSigPOFsiOs-eZE9yas7yBD66fslr-_j-OQHCbCX2k-Vz1oRAGabF72_DIxVm-cn9015si1GTZJkOVbKfkg';
    }
}

Power_Bi_Shortcodes::get_instance();
