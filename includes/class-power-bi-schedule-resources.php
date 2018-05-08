<?php
/**
 * Handles Power BI Schedule Resources.
 *
 * @package Power_Bi
 */
class Power_Bi_Schedule_Resources {
	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
			$instance->setup_scheduler_resources_events();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 */
	private function __construct() {}

	/**
	 * Sets up setup_scheduler_resources_events.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	protected function setup_scheduler_resources_events() {
		$power_bi_scheduler_settings = get_option( 'power_bi_settings' );
		$sunday_start_time = $power_bi_scheduler_settings['power_bi_schedule_sunday_start_time'];
		$sunday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_sunday_pause_time'];
		$monday_start_time = $power_bi_scheduler_settings['power_bi_schedule_monday_start_time'];
		$monday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_monday_pause_time'];
		$tuesday_start_time = $power_bi_scheduler_settings['power_bi_schedule_tuesday_start_time'];
		$tuesday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_tuesday_pause_time'];
		$wednesday_start_time = $power_bi_scheduler_settings['power_bi_schedule_wednesday_start_time'];
		$wednesday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_wednesday_pause_time'];
		$thursday_start_time = $power_bi_scheduler_settings['power_bi_schedule_thursday_start_time'];
		$thursday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_thursday_pause_time'];
		$friday_start_time = $power_bi_scheduler_settings['power_bi_schedule_friday_start_time'];
		$friday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_friday_pause_time'];
		$saturday_start_time = $power_bi_scheduler_settings['power_bi_schedule_saturday_start_time'];
		$saturday_pause_time = $power_bi_scheduler_settings['power_bi_schedule_saturday_pause_time'];
		
		// Resource Start Event
		$this->handle_start_pause_cron_power_bi_sch("start");

		add_action( 'power_bi_schedule_resource_start_cron', array( $this, 'power_bi_schedule_resource_start_fn') );
		// Resource Pause Event
		$this->handle_start_pause_cron_power_bi_sch("pause");
		add_action( 'power_bi_schedule_resource_pause_cron', array( $this, 'power_bi_schedule_resource_pause_fn') );
	}

	function power_bi_schedule_resource_start_fn() {
		// execute the code for running event starting
		_custlog("service started @ ".time());
		$powerbi_credientials = get_option('power_bi_credientials');


		$request_url = "https://management.azure.com/subscriptions/b6e6a952-b4d5-40df-8dd5-90e826279ce7/resourceGroups/atlas_ev_hub/providers/Microsoft.PowerBIDedicated/capacities/atlasevhub/resume?api-version=2017-01-01-preview";



            //request.Headers.Add("Authorization", String.Format("Bearer {0}", accessToken));


		$authorization = "Authorization: Bearer ".$powerbi_credientials['access_token'];
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $request_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_HTTPHEADER => array(
		    "Cache-Control: no-cache",
		    "Content-Type: application/json",
		    $authorization,
		    "Content-length: 0"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		_custlog("Response:");
		_custlog($response);
		_custlog("Error:");
		_custlog($err);


		if ($err) {
          $$err = json_decode($err, true);
		  return $err;
		} else {
		  $token = json_decode($response, true);
		  return $token;
		}

	}
	function power_bi_schedule_resource_pause_fn() {
		//excute the code to stop / pause resource ofr power bi
		_custlog("service paused @ ".time());
	}
	function handle_start_pause_cron_power_bi_sch($start_pause = "") {
		$power_bi_scheduler_settings = get_option( 'power_bi_settings' );
		$weekdayname = strtolower(date("l"));
		switch ($weekdayname) {
		    case "sunday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
			        wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$start_pause.'_cron');
			    }
		        break;
		    case "monday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$start_pause.'_cron');
		        }
		        break;
		    case "tuesday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$start_pause.'_cron');
		        }
		        break;
		   	case "wednesday":
		   		if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$start_pause.'_cron');
		        }
		        break;
		    case "thursday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$start_pause.'_cron');
		        }
		        break;
		    case "friday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$start_pause.'_cron');
		        }
		        break;
		    case "saturday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_start_cron');
		        }
		        break;
		}
	}

}


Power_Bi_Schedule_Resources::get_instance();
