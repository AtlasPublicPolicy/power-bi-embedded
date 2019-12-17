<?php
/**
 * Handles Power BI Schedule Resources.
 *
 * @package Power_Bi
 */
class Power_Bi_Schedule_Resources {

    const RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME = 'power_bi_reschedule_resource_capacity_cron';

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new self();
            $instance->setup_scheduler_resources_events();
            
		    $days_arry = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

    		foreach ($days_arry as $day_name) {

                $start_name = "power_bi_schedule_resource_{$day_name}_start_cron";
                $pause_name = "power_bi_schedule_resource_{$day_name}_pause_cron";
                $capacity_name = "power_bi_schedule_resource_{$day_name}_capacity_cron";

                $start_cron = wp_next_scheduled ( $start_name );
                if($start_cron){
                    $start_cron = date("F j, Y, g:i a", $start_cron);
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":{$start_name}: " . var_export($start_cron, true));
                }

                $pause_cron = wp_next_scheduled ( $pause_name );
                if($pause_cron){
                    $pause_cron = date("F j, Y, g:i a", $pause_cron);
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":{$pause_name}: " . var_export($pause_cron, true));
                }

		        $power_bi_scheduler_settings 	= get_option( 'power_bi_settings' );
                $setting_name = 'power_bi_schedule_' . $day_name . '_capacity';
                $sku_name = $power_bi_scheduler_settings[$setting_name];
                $capacity_cron = wp_next_scheduled ( $capacity_name, array($sku_name) );
                if($capacity_cron){
                    $capacity_cron = date("F j, Y, g:i a", $capacity_cron);
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":{$capacity_name}:" . var_export($capacity_cron, true));
                }
            }

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
			add_action( 'power_bi_schedule_resource_'.$day_name.'_capacity_cron', array( $this, 'power_bi_schedule_resource_update_capacity_fn'), 10, 2);
		}
        add_action( self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array( $this, 'power_bi_schedule_resource_update_capacity_fn'), 10, 2);
		
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

    // This is the action method wp_cron will call
	function power_bi_schedule_resource_update_capacity_fn($sku_name = 'A1') {

        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name: ' . var_export($sku_name, true));

		//excute the code to update capacity sku for power bi

		$resource_state = $this->check_resource_capacity_state();

		$process_response = $this->handle_azure_capacity_update($sku_name);

        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':process_response: ' . var_export($process_response, true));

		if($process_response) {
			_custlog("service capacity updated to {$sku_name} @ ".Date('Y-m-d : h:i:s'));
		}

        $trans_log = array(
            'time' => date("l, F j, Y, G:i", time()), 
            'date_i18n' => date_i18n("l, F j, Y, G:i"),
            'resource_state' => $resource_state,
            'process_response' => $process_response
        );
        set_transient('power_bi_schedule_resource_update_capacity_fn', $trans_log, WEEK_IN_SECONDS);
        
        // if unsuccessful at updating the resource sku then reschedule  else clear any rescheduled updates

        if(isset($process_response['error']) && wp_next_scheduled ( self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array($sku_name) ) === false) {

            $retry_count = get_transient(self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME);
            $retry_count = $retry_count === false ? 1 : ++$retry_count;
            set_transient(self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME,  $retry_count, HOUR_IN_SECONDS);
            error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":retry_count: " . var_export($retry_count, true));

            if($retry_count > 9){
                error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":retry_count: " . var_export($retry_count, true));
                return;
            }

            $result = wp_schedule_single_event(time() + 60, self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array($sku_name));

            error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":result: " . var_export($result, true));

            $cron_name = self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME; 
            $capacity_cron = wp_next_scheduled ( self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array($sku_name) );
            $capacity_cron = date("F j, Y, g:i a", $capacity_cron);
            error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":{$cron_name}:" . var_export($capacity_cron, true));

        }else{

            delete_transient(self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME);

            $capacity_skus = Power_Bi_Schedule_Resources::get_instance()->list_skus();
            foreach($capacity_skus as $sku){
                wp_clear_scheduled_hook( self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array($sku['name']) );
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
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_sunday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_sunday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_sunday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_sunday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_sunday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_sunday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron sunday:' . var_export($result, true));

				    }
			    }
			    break;
		    case "monday":
		        if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
		        	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_monday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_monday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_monday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_monday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_monday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_monday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron monday:' . var_export($result, true));

				    }
			    }
		        break;
		    case "tuesday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_tuesday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_tuesday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_tuesday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_tuesday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_tuesday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_tuesday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron tuesday:' . var_export($result, true));

				    }
			    }
		        break;
		   	case "wednesday":
			   	if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			   		if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				   		wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				   	}
			   	}
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_wednesday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_wednesday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_wednesday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_wednesday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_wednesday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_wednesday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron wednesday:' . var_export($result, true));

				    }
			    }
		        break;
		    case "thursday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				    	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_thursday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_thursday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_thursday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_thursday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_thursday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_thursday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron thursday:' . var_export($result, true));

				    }
			    }
		        break;
		    case "friday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
				        wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
				    }
			    }
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_friday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_friday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_friday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_friday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_friday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_friday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron friday:' . var_export($result, true));

				    }
			    }
		        break;
		    case "saturday":
			    if($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time'] != "") {
			    	if (! wp_next_scheduled ( 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron' )) {
			        	wp_schedule_event(custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
			        }
		        }
                // update capacity sku
			    if($start_pause == 'start' && $power_bi_scheduler_settings['power_bi_schedule_saturday_capacity'] != "" && $power_bi_scheduler_settings['power_bi_schedule_saturday_start_time'] != "") {

                    $sku_name = $power_bi_scheduler_settings['power_bi_schedule_saturday_capacity'];
                    error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name:' . var_export($sku_name, true));

			    	if ( wp_next_scheduled ( 'power_bi_schedule_resource_saturday_capacity_cron', array($sku_name) ) === false) {

				        $time = custom_power_bi_strtotime($power_bi_scheduler_settings['power_bi_schedule_saturday_start_time']);
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':time: ' . date("F j, Y, g:i a", $time));

				        $result = wp_schedule_event($time, 'weekly', 'power_bi_schedule_resource_saturday_capacity_cron', array($sku_name));
                        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event capacity_cron saturday:' . var_export($result, true));

				    }
			    }
		        break;
		}
	}
	function check_resource_capacity_state($return_full_response = false) {
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
          if($return_full_response){
            return $response;
          }
		  $resource_state = $response['properties']['state'];
		  return $resource_state;
		}
	}
    public function list_skus($action = "") {

        $skus = get_transient('power_bi_ms_capacity_skus');
        if($skus !== false && count($skus) > 0){
            return $skus;
        }
        
        // get saved power bi settings
        $power_bi_settings  = get_option( 'power_bi_settings' );
        $subscription_id    = $power_bi_settings['power_bi_azure_subscription_id'];
        $resource_group     = $power_bi_settings['power_bi_azure_resource_group'];
        $capacity           = $power_bi_settings['power_bi_azure_capacity'];
        // get saved management azure credential for access token
        $powerbi_azure_credentials = get_option('power_bi_management_azure_credentials');

        $request_url = "https://management.azure.com/subscriptions/{$subscription_id}/resourceGroups/{$resource_group}/providers/Microsoft.PowerBIDedicated/capacities/{$capacity}/skus?api-version=2017-10-01";

        $authorization = "Authorization: Bearer " . $powerbi_azure_credentials['access_token'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $request_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
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
            return array('error' => $err);
        } 

        $response = json_decode($response, true);

        if(!isset($response['value'])){
            return array('error' => 'no value');
        }

        $skus = array();
        foreach($response['value'] as $r){
            $skus[] = $r['sku'];
        }

        set_transient('power_bi_ms_capacity_skus', $skus, HOUR_IN_SECONDS);
        
        return $skus;

    }
    protected function handle_azure_capacity_update($sku_name = "") {

        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':sku_name: ' . var_export($sku_name, true));

        // get saved power bi settings
        $power_bi_settings  = get_option( 'power_bi_settings' );
        $subscription_id    = $power_bi_settings['power_bi_azure_subscription_id'];
        $resource_group     = $power_bi_settings['power_bi_azure_resource_group'];
        $capacity           = $power_bi_settings['power_bi_azure_capacity'];
        // get saved management azure credential for access token
        $powerbi_azure_credentials = get_option('power_bi_management_azure_credentials');
        // call url for start / resume resource capacity
        $request_url = "https://management.azure.com/subscriptions/".$subscription_id."/resourceGroups/".$resource_group."/providers/Microsoft.PowerBIDedicated/capacities/".$capacity."?api-version=2017-10-01";

        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':request_url: ' . var_export($request_url, true));

        $authorization = "Authorization: Bearer " . $powerbi_azure_credentials['access_token'];
        $curl = curl_init();
    
        $data = array(
            "sku" => array(
                "name" => $sku_name,
                "tier"=> "PBIE_Azure"
            ),
        );
 
        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':data: ' . var_export($data, true));

        $payload = json_encode($data);

        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':payload: ' . var_export($payload, true));

        curl_setopt_array($curl, array(
          CURLOPT_URL => $request_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "PATCH",
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_POSTFIELDS => $payload,
          CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            $authorization,
            'Content-Length: ' . strlen($payload)
            ),
          )
        );

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
}


Power_Bi_Schedule_Resources::get_instance();


