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
		$power_bi_scheduler_settings 	= get_option( 'power_bi_settings' );
		$sunday_start_time 				= $power_bi_scheduler_settings['power_bi_schedule_sunday_start_time'];
		$sunday_pause_time 				= $power_bi_scheduler_settings['power_bi_schedule_sunday_pause_time'];
		$monday_start_time 				= $power_bi_scheduler_settings['power_bi_schedule_monday_start_time'];
		$monday_pause_time 				= $power_bi_scheduler_settings['power_bi_schedule_monday_pause_time'];
		$tuesday_start_time 			= $power_bi_scheduler_settings['power_bi_schedule_tuesday_start_time'];
		$tuesday_pause_time 			= $power_bi_scheduler_settings['power_bi_schedule_tuesday_pause_time'];
		$wednesday_start_time 			= $power_bi_scheduler_settings['power_bi_schedule_wednesday_start_time'];
		$wednesday_pause_time 			= $power_bi_scheduler_settings['power_bi_schedule_wednesday_pause_time'];
		$thursday_start_time 			= $power_bi_scheduler_settings['power_bi_schedule_thursday_start_time'];
		$thursday_pause_time 			= $power_bi_scheduler_settings['power_bi_schedule_thursday_pause_time'];
		$friday_start_time 				= $power_bi_scheduler_settings['power_bi_schedule_friday_start_time'];
		$friday_pause_time 				= $power_bi_scheduler_settings['power_bi_schedule_friday_pause_time'];
		$saturday_start_time 			= $power_bi_scheduler_settings['power_bi_schedule_saturday_start_time'];
		$saturday_pause_time 			= $power_bi_scheduler_settings['power_bi_schedule_saturday_pause_time'];
		
		// Resource Start Event
		$this->handle_start_pause_cron_power_bi_sch("start");
		// Resource Pause Event
		$this->handle_start_pause_cron_power_bi_sch("pause");
		// prepare array and run per day cron
		$days_arry = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
		foreach ($days_arry as $day_name) {
			add_action( 'power_bi_schedule_resource_'.$day_name.'_start_cron', array( $this, 'power_bi_schedule_resource_start_fn') );
			add_action( 'power_bi_schedule_resource_'.$day_name.'_pause_cron', array( $this, 'power_bi_schedule_resource_pause_fn') );
		}
		
	}

	function power_bi_schedule_resource_start_fn() {
		// execute the code for running event starting
		$resource_state = $this->check_resource_capacity_state();
		if($resource_state == "Paused") {
			$process_response = $this->handle_azure_resource_service("resume");
			if($process_response) {
				_custlog("service started @ ".Date('Y-m-d : h:i:s'));
			}
		}
	}
	function power_bi_schedule_resource_pause_fn() {
		//excute the code to stop / pause resource for power bi
		$resource_state = $this->check_resource_capacity_state();
		if($resource_state == "Succeeded") {
			$process_response = $this->handle_azure_resource_service("suspend");
			if($process_response) {
				_custlog("service paused @ ".Date('Y-m-d : h:i:s'));
			}
		}
	}

	protected function handle_azure_resource_service($action = "") {
		// get saved power bi settings
		$power_bi_settings 	= get_option( 'power_bi_settings' );
		$subscription_id 	= $power_bi_settings['power_bi_azure_subscription_id'];
		$resource_group 	= $power_bi_settings['power_bi_azure_resource_group'];
		$capacity 			= $power_bi_settings['power_bi_azure_capacity'];
		// get saved management azure credential for access token
		$powerbi_azure_credentials = get_option('power_bi_management_azure_credentials');
		// call url for start / resume resource capacity
		$request_url = "https://management.azure.com/subscriptions/".$subscription_id."/resourceGroups/".$resource_group."/providers/Microsoft.PowerBIDedicated/capacities/".$capacity."/".$action."?api-version=2017-10-01";

		$authorization = "Authorization: Bearer " . $powerbi_azure_credentials['access_token'];
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

		if ($err) {
          $err = json_decode($err, true);
		  return $err;
		} else {
		  $response = json_decode($response, true);
		  return $response;
		}
	}

	function handle_start_pause_cron_power_bi_sch($start_pause = "") {
		$power_bi_scheduler_settings = get_option( 'power_bi_settings' );
		$weekdayname = strtolower(date("l"));
		switch ($weekdayname) {
		    case "sunday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
			    break;
		    case "monday":
		        if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
		        	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
		        break;
		    case "tuesday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
		        break;
		   	case "wednesday":
			   	if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			   		if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				   		wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				   	}
			   	}
		        break;
		    case "thursday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
		        break;
		    case "friday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				        wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
		        break;
		    case "saturday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
			        	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
			        }
		        }
		        break;
		}
	}
	function check_resource_capacity_state() {
		// get saved power bi settings
		$power_bi_settings 	= get_option( 'power_bi_settings' );
		$subscription_id 	= $power_bi_settings['power_bi_azure_subscription_id'];
		$resource_group 	= $power_bi_settings['power_bi_azure_resource_group'];
		$capacity 			= $power_bi_settings['power_bi_azure_capacity'];
		// get saved management azure credential for access token
		$powerbi_azure_credentials = get_option('power_bi_management_azure_credentials');
		// call url for start / resume resource capacity
		$request_url = "https://management.azure.com/subscriptions/".$subscription_id."/resourceGroups/".$resource_group."/providers/Microsoft.PowerBIDedicated/capacities/".$capacity."?api-version=2017-10-01";


		$authorization = "Authorization: Bearer " . $powerbi_azure_credentials['access_token'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $request_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_HTTPGET => true,
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

		if ($err) {
          $err = json_decode($err, true);
		  return $err;
		} else {
		  $response = json_decode($response, true);
		  $resource_state = $response['properties']['state'];
		  return $resource_state;
		}
	}
}


Power_Bi_Schedule_Resources::get_instance();


