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
		_custlog("service started @ ".time());
		$powerbi_credientials = get_option('power_bi_credientials');

		$request_url = "https://management.azure.com/subscriptions/b6e6a952-b4d5-40df-8dd5-90e826279ce7/resourceGroups/atlas_ev_hub/providers/Microsoft.PowerBIDedicated/capacities/atlasevhub/resume?api-version=2017-01-01-preview";

		$authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6ImlCakwxUmNxemhpeTRmcHhJeGRacW9oTTJZayIsImtpZCI6ImlCakwxUmNxemhpeTRmcHhJeGRacW9oTTJZayJ9.eyJhdWQiOiJodHRwczovL21hbmFnZW1lbnQuYXp1cmUuY29tLyIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzFiYjQ4ZGE0LTMxNDMtNDAzMS1iZGFlLWNjYzA0MDc1MDhmZS8iLCJpYXQiOjE1MjU3MDA2NzYsIm5iZiI6MTUyNTcwMDY3NiwiZXhwIjoxNTI1NzA0NTc2LCJhaW8iOiJZMmRnWU5BTnVqUEg4V2ovb3RkSFY3MklybVE3QXdBPSIsImFwcGlkIjoiMDY1M2RlYWMtODI2NS00Y2VlLWFiZWQtMzg1NmU4NDVlOGM2IiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvMWJiNDhkYTQtMzE0My00MDMxLWJkYWUtY2NjMDQwNzUwOGZlLyIsIm9pZCI6ImQ0OTUxNmIzLTdlOWEtNGY1Ni04OTI2LTM0MjFmOWUzMWMxYiIsInN1YiI6ImQ0OTUxNmIzLTdlOWEtNGY1Ni04OTI2LTM0MjFmOWUzMWMxYiIsInRpZCI6IjFiYjQ4ZGE0LTMxNDMtNDAzMS1iZGFlLWNjYzA0MDc1MDhmZSIsInV0aSI6IndBT0lFME0yZWs2MlNPVGU1WklWQUEiLCJ2ZXIiOiIxLjAifQ.d2hxjfHb3Fu3mYbJGpRJxnfC66BdlAa5kRwTX_EHXGsz95Yd4WNiB4t9AqEtKiKwcKP4xhVubVaKsnM6BX8AZnsWj4xuBzaLPrRfSfoJBlsIGAw8wAFgJQJKEZal_RokdHgAxS1CoBmMNLee6UWM_Rs7g4cFQpyvkOFE7qwLqBEsZGlrjUFoYkG45NujFK1evHzj7iveRlhynElZ2D51puEcN3pbdMWij-lMArt-tOfOX2Twk2jh0TFhEW6CSZdlDqlVVah48cBC_ZxQMuGPXZNOBXG-MUiKk4w4cGRbl6sjsKibkT58jyeCUHXvG4Ap9sDaz3lXRXyMzTKmVZ_fnw";
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
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
			        wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
			    }
		        break;
		    case "monday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
		        }
		        break;
		    case "tuesday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
		        }
		        break;
		   	case "wednesday":
		   		if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
		        }
		        break;
		    case "thursday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
		        }
		        break;
		    case "friday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
		        }
		        break;
		    case "saturday":
		    	if(!wp_next_scheduled('power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron')) {
		        	wp_schedule_event(strtotime($power_bi_scheduler_settings['power_bi_schedule_'.$weekdayname.'_'.$start_pause.'_time']), 'weekly', 'power_bi_schedule_resource_'.$weekdayname.'_'.$start_pause.'_cron');
		        }
		        break;
		}
	}

}


Power_Bi_Schedule_Resources::get_instance();


