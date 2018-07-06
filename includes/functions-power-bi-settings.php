<?php
// get power bi plugins setting in single call
function get_power_bi_plugin_settings() {
	$powerbi_resource_settings = Power_Bi_Settings::get_instance();
	return $powerbi_resource_settings->get_power_bi_settings();
}
function power_bi_section_callback() {

	echo __( 'The following fields must be filled correctly to get an access token. Help is available from Microsoft <a href="https://docs.microsoft.com/en-us/power-bi/developer/embedding-content" target=_blank>here</a>.', 'power-bi' );

}

function power_bi_username_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type='text' name='power_bi_settings[power_bi_username]' value='<?php echo $options['power_bi_username']; ?>'>
	<?php

}


function power_bi_password_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type='password' name='power_bi_settings[power_bi_password]' value='<?php echo $options['power_bi_password']; ?>'>
	<?php

}


function power_bi_client_id_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type='text' name='power_bi_settings[power_bi_client_id]' value='<?php echo $options['power_bi_client_id']; ?>'>
	<?php

}


function power_bi_client_secret_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type='text' name='power_bi_settings[power_bi_client_secret]' value='<?php echo $options['power_bi_client_secret']; ?>'>
	<?php

}

function power_bi_oauth_success_render() {

	$power_bi_credentials = get_option('power_bi_credentials');

	if( isset( $power_bi_credentials['access_token'] ) ) {
		echo '<span class="dashicons dashicons-yes"></span> Connected';
	} elseif ( isset( $power_bi_credentials['error_description'] ) ) {
		echo '<span class="dashicons dashicons-no-alt"></span> ' . $power_bi_credentials['error_description'];
	}

}
// For power_bi_schedule_section_callback
function power_bi_schedule_section_callback() {

	echo __( 'Configure schedule to suspend and resume the Power BI resource on Azure. When the resource is suspended, no charges are incurred. The WordPress time zone is used. Documentation of the Azure API used for this feature is available <a href="https://docs.microsoft.com/en-us/rest/api/power-bi-embedded/capacities" target=_blank>here</a>.', 'power-bi' );

}
// Providing azure resource related status to render the fields
function power_bi_azure_resource_state_render() {
	// get status of Power BI resource
	$powerbi_resource = Power_Bi_Schedule_Resources::get_instance();
	$resource_capacity_state = $powerbi_resource->check_resource_capacity_state();
	if($resource_capacity_state != "") {
		echo "<strong>".$resource_capacity_state."</strong>";
	}

}
// Providing azure resource related details to render the fields
function power_bi_azure_tenant_id_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type="text" name="power_bi_settings[power_bi_azure_tenant_id]" value="<?php echo $options['power_bi_azure_tenant_id']; ?>" />
	<?php
}
function power_bi_azure_subscription_id_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type="text" name="power_bi_settings[power_bi_azure_subscription_id]" value="<?php echo $options['power_bi_azure_subscription_id']; ?>" />
	<?php
}
function power_bi_azure_resource_group_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type="text" name="power_bi_settings[power_bi_azure_resource_group]" value="<?php echo $options['power_bi_azure_resource_group']; ?>" />
	<?php
}
function power_bi_azure_capacity_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<input type="text" name="power_bi_settings[power_bi_azure_capacity]" value="<?php echo $options['power_bi_azure_capacity']; ?>" />
	<?php
}
function power_bi_schedule_sunday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_sunday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_sunday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_sunday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_sunday_pause_time']); ?>
	</select>
	<?php

}
function power_bi_schedule_monday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_monday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_monday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_monday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_monday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_tuesday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_tuesday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_tuesday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_tuesday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_tuesday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_wednesday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_wednesday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_wednesday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_wednesday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_wednesday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_thursday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_thursday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_thursday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_thursday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_thursday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_friday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_friday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_friday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_friday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_friday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_saturday_render() {
	$options = get_power_bi_plugin_settings();
	?>
	<select name="power_bi_settings[power_bi_schedule_saturday_start_time]">
		<option value=""><?php echo __( 'START', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_saturday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_saturday_pause_time]">
		<option value=""><?php echo __( 'PAUSE', 'power-bi' ); ?></option>
		<?php display_time_dropdown($options['power_bi_schedule_saturday_pause_time']); ?>
	</select>
	<?php
}
function display_time_dropdown($sel =  "") {
	for($hours=0; $hours<24; $hours++)
	{
	    for($mins=0; $mins<60; $mins+=30)
	    {
	        $time = str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT);
	        if($sel != "" && $sel == $time) {
	        	echo '<option selected="selected" value= "'.$time.'">'.$time.'</option>';
	        } else {
	        	echo '<option value= "'.$time.'">'.$time.'</option>';
	        }

	    }
	}
}
// To add new cron custom schedule
add_filter( 'cron_schedules', 'power_bi_add_weekly_schedule' );
function power_bi_add_weekly_schedule( $schedules ) {
  $schedules['weekly'] = array(
    'interval' => 7 * 24 * 60 * 60, //7 days * 24 hours * 60 minutes * 60 seconds
    'display' => __( 'Once Weekly', 'power-bi' )
  );
  return $schedules;
}
// Debugging part
if (!function_exists('_custlog')) {
    function _custlog($message)
    {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log('<<<<<<<< :: DEBUG Array :: >>>>>>>>');
                error_log(print_r($message, true));
            } else {
                error_log('<<<<<<<< :: DEBUG String :: >>>>>>>>');
                error_log($message);
            }
        }
    }
}
if (!function_exists('power_bi_debug_pr')) {
	function power_bi_debug_pr($array, $exit = FALSE)
	{
	    echo "<pre>";
	    print_r($array);
	    echo "</pre>";
	    if ($exit) {
	        exit();
	    }
	}
}
// Add custom strtotime converter
function custom_power_bi_strtotime($strtotime) {
	// temp set up the time zone for setting up the event
	// Set default time as per the wp setup
	// Server Time Zone
	$server_time_zone = date_default_timezone_get();
	if(get_option( 'timezone_string' ) != "") {
		define( 'POWER_BI_TIMEZONE', (get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : date_default_timezone_get() ) );
		date_default_timezone_set( POWER_BI_TIMEZONE );
	} else {
		if(get_option('gmt_offset') != "") {
			setTimezoneByOffsetPB(get_option('gmt_offset'));
		}
	}
	$custom_str = strtotime($strtotime);
	// Reset server time zone
	date_default_timezone_set( $server_time_zone );
	return $custom_str;
}
function setTimezoneByOffsetPB($offset) 
{ 
  	$testTimestamp = time(); 
    date_default_timezone_set('UTC'); 
    $testLocaltime = localtime($testTimestamp,true);
    $testHour = $testLocaltime['tm_hour'];        

  	$abbrarray = timezone_abbreviations_list(); 
	foreach ($abbrarray as $abbr) 
	{ 
	  foreach ($abbr as $city) 
	  { 
	  	date_default_timezone_set($city['timezone_id']); 
        $testLocaltime = localtime($testTimestamp,true); 
        $hour = $testLocaltime['tm_hour'];        
        $testOffset = $hour - $testHour; 
        if($testOffset == $offset) 
        { 
            return true; 
        } 
	  } 
	} 
	return false; 
}