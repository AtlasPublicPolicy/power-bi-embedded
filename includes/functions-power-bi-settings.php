<?php

function power_bi_section_callback() {

	echo __( 'The following fields must be filled correctly to get an access token.', 'power-bi' );

}

function power_bi_username_render() {

	$options = get_option( 'power_bi_settings' );
	?>
	<input type='text' name='power_bi_settings[power_bi_username]' value='<?php echo $options['power_bi_username']; ?>'>
	<?php

}


function power_bi_password_render() {

	$options = get_option( 'power_bi_settings' );
	?>
	<input type='password' name='power_bi_settings[power_bi_password]' value='<?php echo $options['power_bi_password']; ?>'>
	<?php

}


function power_bi_client_id_render() {

	$options = get_option( 'power_bi_settings' );
	?>
	<input type='text' name='power_bi_settings[power_bi_client_id]' value='<?php echo $options['power_bi_client_id']; ?>'>
	<?php

}


function power_bi_client_secret_render() {

	$options = get_option( 'power_bi_settings' );
	?>
	<input type='text' name='power_bi_settings[power_bi_client_secret]' value='<?php echo $options['power_bi_client_secret']; ?>'>
	<?php

}

function power_bi_oauth_success_render() {

	$powerbi_credentials = get_option('power_bi_credentials');
	$token = $powerbi_credentials['access_token'];
	$error = $powerbi_credentials['error_description'];

	if( isset( $token ) ) {
		echo '<span class="dashicons dashicons-yes"></span> Connected';
	} elseif ( $error ) {
		echo '<span class="dashicons dashicons-no-alt"></span> ' . $error;
	}

}
// For power_bi_schedule_section_callback
function power_bi_schedule_section_callback() {

	echo __( 'User configurable schedule to start and pause Power BI Resource.', 'power-bi' );

}
function power_bi_schedule_label_render() {
	?>
	<td><strong>START</strong></td><td><strong>PAUSE</strong></td>
	<?php
}
function power_bi_schedule_sunday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_sunday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_sunday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_sunday_pause_time]">
		<option value="">PAUSE</option>
		<?php display_time_dropdown($options['power_bi_schedule_sunday_pause_time']); ?>
	</select>
	
	<?php

}
function power_bi_schedule_monday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_monday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_monday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_monday_pause_time]">
		<option value="">PAUSE</option>
		<?php display_time_dropdown($options['power_bi_schedule_monday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_tuesday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_tuesday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_tuesday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_tuesday_pause_time]">
		<option value="">PAUSE</option>
		<?php display_time_dropdown($options['power_bi_schedule_tuesday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_wednesday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_wednesday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_wednesday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_wednesday_pause_time]">
		<option value="">PAUSE</option>
		<?php display_time_dropdown($options['power_bi_schedule_wednesday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_thursday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_thursday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_thursday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_thursday_pause_time]">
		<option value="">PAUSE</option>
		<?php display_time_dropdown($options['power_bi_schedule_thursday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_friday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_friday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_friday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_friday_pause_time]">
		<option value="">PAUSE</option>
		<?php display_time_dropdown($options['power_bi_schedule_friday_pause_time']); ?>
	</select>
	<?php
}
function power_bi_schedule_saturday_render() {
	$options = get_option( 'power_bi_settings' );
	?>
	<select name="power_bi_settings[power_bi_schedule_saturday_start_time]">
		<option value="">START</option>
		<?php display_time_dropdown($options['power_bi_schedule_saturday_start_time']); ?>
	</select>&nbsp;<select name="power_bi_settings[power_bi_schedule_saturday_pause_time]">
		<option value="">PAUSE</option>
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
function power_bi_debug_pr($array, $exit = FALSE)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
    if ($exit) {
        exit();
    }
}