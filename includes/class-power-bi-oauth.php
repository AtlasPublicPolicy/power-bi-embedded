<?php
/**
 * Handles Power BI oauth.
 *
 * @package Power_Bi
 */
class Power_Bi_Oauth {

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
	private function setup_actions() {
		add_action( 'init', array( $this, 'add_token' ) );
	}

    public function add_token() {
        $powerbi_credientials = get_option('power_bi_credientials');

        if( isset( $powerbi_credientials['access_token'] ) || isset( $powerbi_credientials['error'] ) ) {
			$token_credientials = $this->get_token();
			update_option('power_bi_credientials', $token_credientials);

			return;
        }

        $token_credientials = $this->get_token();

        if( isset( $token_credientials['access_token'] ) || isset( $token_credientials['error'] ) ) {
			if ( ! add_option( 'power_bi_credientials', $token_credientials ) ) {
				update_option('power_bi_credientials', $token_credientials);
			}
        }
    }

    public function get_token() {
		$user_credientials = get_option( 'power_bi_settings' );

		$user_name         = $user_credientials['power_bi_username'];
		$password          = $user_credientials['power_bi_password'];
		$client_id         = $user_credientials['power_bi_client_id'];
		$client_secret     = $user_credientials['power_bi_client_secret'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://login.windows.net/common/oauth2/token",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"grant_type\"\r\n\r\npassword\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"username\"\r\n\r\n" . $user_name . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n" . $password . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_id\"\r\n\r\n" . $client_id . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"resource\"\r\n\r\nhttps://analysis.windows.net/powerbi/api\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_secret\"\r\n\r\n" . $client_secret . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
		  CURLOPT_HTTPHEADER => array(
		    "Cache-Control: no-cache",
		    "Postman-Token: b45c007e-0ab8-28d8-0960-6a2c37bf318e",
		    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
          $$err = json_decode($err, true);
		  return $err;
		} else {
		  $token = json_decode($response, true);
		  return $token;
		}
    }
}

Power_Bi_Oauth::get_instance();
