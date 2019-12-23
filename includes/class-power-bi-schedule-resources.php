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
        }

        return $instance;
    }

    /**
     * Constructor method.
     */
    private function __construct() {}

    public function action_cron_fun($action){

        switch($action){
            case 'suspend':
                $this->power_bi_schedule_resource_pause_fn();
                break;
            case 'resume':
                $this->power_bi_schedule_resource_start_fn();
                break;
            default:
                $this->power_bi_schedule_resource_update_capacity_fn($action);
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

    // This is the action method wp_cron will call
    function power_bi_schedule_resource_update_capacity_fn($sku_name = 'A1') {

        //excute the code to update capacity sku for power bi

        $resource_state = $this->check_resource_capacity_state();

        $process_response = $this->handle_azure_capacity_update($sku_name);

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

            if($retry_count > 9){
                return;
            }

            $result = wp_schedule_single_event(time() + 60, self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array($sku_name));

            $cron_name = self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME; 
            $capacity_cron = wp_next_scheduled ( self::RESCHEDULE_RESOURCE_CAPACITY_UPDATE_NAME, array($sku_name) );
            $capacity_cron = date("F j, Y, g:i a", $capacity_cron);

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
        $power_bi_settings  = get_option( 'power_bi_settings' );
        $subscription_id    = $power_bi_settings['power_bi_azure_subscription_id'];
        $resource_group     = $power_bi_settings['power_bi_azure_resource_group'];
        $capacity           = $power_bi_settings['power_bi_azure_capacity'];
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

    function check_resource_capacity_state($return_full_response = false) {
        // get saved power bi settings
        $power_bi_settings  = get_option( 'power_bi_settings' );
        $subscription_id    = $power_bi_settings['power_bi_azure_subscription_id'];
        $resource_group     = $power_bi_settings['power_bi_azure_resource_group'];
        $capacity           = $power_bi_settings['power_bi_azure_capacity'];
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
          $resource_state = isset($response['properties']['state']) ? $response['properties']['state'] : '';
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

        // get saved power bi settings
        $power_bi_settings  = get_option( 'power_bi_settings' );
        $subscription_id    = $power_bi_settings['power_bi_azure_subscription_id'];
        $resource_group     = $power_bi_settings['power_bi_azure_resource_group'];
        $capacity           = $power_bi_settings['power_bi_azure_capacity'];
        // get saved management azure credential for access token
        $powerbi_azure_credentials = get_option('power_bi_management_azure_credentials');
        // call url for start / resume resource capacity
        $request_url = "https://management.azure.com/subscriptions/".$subscription_id."/resourceGroups/".$resource_group."/providers/Microsoft.PowerBIDedicated/capacities/".$capacity."?api-version=2017-10-01";

        $authorization = "Authorization: Bearer " . $powerbi_azure_credentials['access_token'];
        $curl = curl_init();
    
        $data = array(
            "sku" => array(
                "name" => $sku_name,
                "tier"=> "PBIE_Azure"
            ),
        );
 
        $payload = json_encode($data);

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


