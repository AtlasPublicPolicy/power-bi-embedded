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
