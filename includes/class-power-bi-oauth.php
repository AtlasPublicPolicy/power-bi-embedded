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
        // add management azure token
        add_action( 'init', array( $this, 'add_management_azure_token' ) );
    }

    public function add_token() {
        $power_bi_credentials = get_option('power_bi_credentials');

        if( isset( $power_bi_credentials['access_token'] ) || isset( $power_bi_credentials['error'] ) ) {
            $token_credentials = $this->get_token();
            update_option('power_bi_credentials', $token_credentials);

            return;
        }

        $token_credentials = $this->get_token();

        if( isset( $token_credentials['access_token'] ) || isset( $token_credentials['error'] ) ) {
            if ( ! add_option( 'power_bi_credentials', $token_credentials ) ) {
                update_option('power_bi_credentials', $token_credentials);
            }
        }
    }

    public function add_management_azure_token() {
        $powerbi_azure_credentials = get_option('power_bi_management_azure_credentials');

        if( isset( $powerbi_azure_credentials['access_token'] ) || isset( $powerbi_azure_credentials['error'] ) ) {
            $token_credentials = $this->get_token_management_azure();
            update_option('power_bi_management_azure_credentials', $token_credentials);

            return;
        }

        $token_credentials = $this->get_token_management_azure();

        if( isset( $token_credentials['access_token'] ) || isset( $token_credentials['error'] ) ) {
            if ( ! add_option( 'power_bi_management_azure_credentials', $token_credentials ) ) {
                update_option('power_bi_management_azure_credentials', $token_credentials);
            }
        }
    }

    public function get_token() {
        $token_transient = get_transient( 't_token' );

        if(! empty( $token_transient )) {
            return $token_transient;

        } else {

            $user_credentials = get_option( 'power_bi_settings' );

            $user_name         = $user_credentials['power_bi_username'];
            $password          = $user_credentials['power_bi_password'];
            $client_id         = $user_credentials['power_bi_client_id'];
            $client_secret     = $user_credentials['power_bi_client_secret'];

            $curl = curl_init();
            if(!$curl) {
                die("Embedded PowerBi could not initialize a cURL handle.  Please have your hosting provider install curl");
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://login.windows.net/common/oauth2/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_SSL_VERIFYPEER => false,
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
                $err = json_decode($err, true);
                return $err;
            } else {
                $token = json_decode($response, true);
                set_transient( 't_token', $token, HOUR_IN_SECONDS );
                return $token;
            }
        } }

    // Provided new get token request for https://management.azure.com/
    function get_token_management_azure() {
        $user_credentials 	= get_option( 'power_bi_settings' );

        $client_id         	= $user_credentials['power_bi_client_id'];
        $client_secret     	= $user_credentials['power_bi_client_secret'];
        $azure_tenant_id    = $user_credentials['power_bi_azure_tenant_id'];

        $curl = curl_init();
        if(!$curl) {
            die("Embedded PowerBi could not initialize a cURL handle.  Please have your hosting provider install curl");
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://login.microsoftonline.com/".$azure_tenant_id."/oauth2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"grant_type\"\r\n\r\nclient_credentials\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_id\"\r\n\r\n" . $client_id . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"resource\"\r\n\r\nhttps://management.azure.com/\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_secret\"\r\n\r\n" . $client_secret . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
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
            $err = json_decode($err, true);
            return $err;
        } else {
            $token = json_decode($response, true);
            return $token;
        }
    }
}

Power_Bi_Oauth::get_instance();
