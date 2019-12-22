<?php
/**
 * Handles displays and hooks for the Power BI custom settings page.
 *
 * @package Power_Bi
 */
class Power_Bi_Settings {

    private $day_variations = array('_1', '_2');
    private $days_of_week = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

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
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'update_option_power_bi_settings', array( $this, 'update_schedule' ) , 10, 3);
        add_action( 'power_bi_action_cron', array( Power_Bi_Schedule_Resources::get_instance(), 'action_cron_fun'), 10, 2);
    }

    /**
     * [add_admin_menu description]
     */
    public function add_admin_menu() {
        add_submenu_page( 'edit.php?post_type=powerbi', __( 'Power BI Settings', 'power-bi' ), __( 'Settings', 'power-bi' ), 'manage_options', 'powerbi', array( $this, 'power_bi__options_page' ) );
    }

    /**
     * [settings_init description]
     * @return [type] [description]
     */
    public function settings_init(  ) {

        register_setting( 'power_bi', 'power_bi_settings' );

        add_settings_section(
            'power_bi_section',
            __( 'Azure Authorization', 'power-bi' ),
            'power_bi_section_callback',
            'power_bi'
        );

        add_settings_field(
            'power_bi_username',
            __( 'User Name', 'power-bi' ),
            'power_bi_username_render',
            'power_bi',
            'power_bi_section'
        );

        add_settings_field(
            'power_bi_password',
            __( 'Password', 'power-bi' ),
            'power_bi_password_render',
            'power_bi',
            'power_bi_section'
        );

        add_settings_field(
            'power_bi_client_id',
            __( 'Client ID', 'power-bi' ),
            'power_bi_client_id_render',
            'power_bi',
            'power_bi_section'
        );

        add_settings_field(
            'power_bi_client_secret',
            __( 'Client Secret', 'power-bi' ),
            'power_bi_client_secret_render',
            'power_bi',
            'power_bi_section'
        );

        add_settings_field(
            'power_bi_oauth_success',
            __( 'Oauth Status', 'power-bi' ),
            'power_bi_oauth_success_render',
            'power_bi',
            'power_bi_section'
        );
        // Schedule Power BI Resource
        add_settings_section(
            'power_bi_schedule_section',
            __( 'Power BI Resource On/Off Schedule', 'power-bi' ),
            'power_bi_schedule_section_callback',
            'power_bi'
        );
        // Added Option to view resource status
        add_settings_field(
            'power_bi_azure_resource_state',
            __( 'Power BI Resource Status', 'power-bi' ),
            'power_bi_azure_resource_state_render',
            'power_bi',
            'power_bi_schedule_section'
        );
        // New setting for adding other required fields for make azure api call
        add_settings_field(
            'power_bi_azure_tenant_id',
            __( 'Tenant ID or Directory ID under Azure Active Directory for Office 365', 'power-bi' ),
            'power_bi_azure_tenant_id_render',
            'power_bi',
            'power_bi_schedule_section'
        );
        add_settings_field(
            'power_bi_azure_subscription_id',
            __( 'Subscription ID for Power BI Resource', 'power-bi' ),
            'power_bi_azure_subscription_id_render',
            'power_bi',
            'power_bi_schedule_section'
        );
        add_settings_field(
            'power_bi_azure_resource_group',
            __( 'Resource Group Name', 'power-bi' ),
            'power_bi_azure_resource_group_render',
            'power_bi',
            'power_bi_schedule_section'
        );
        add_settings_field(
            'power_bi_azure_capacity',
            __( 'Resource Name', 'power-bi' ),
            'power_bi_azure_capacity_render',
            'power_bi',
            'power_bi_schedule_section'
        );

        add_settings_field(
            'power_bi_schedule_time',
            __( 'Info:', 'power-bi' ),
            array($this, 'power_bi_info_render'),
            'power_bi',
            'power_bi_schedule_section'
        );
        add_settings_field(
            'power_bi_schedule_sunday_time',
            __( 'Sunday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('sunday')
        );
        add_settings_field(
            'power_bi_schedule_monday_time',
            __( 'Monday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('monday')
        );
        add_settings_field(
            'power_bi_schedule_tuesday_time',
            __( 'Tuesday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('tuesday')
        );
        add_settings_field(
            'power_bi_schedule_wednesday_time',
            __( 'Wednesday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('wednesday')
        );
        add_settings_field(
            'power_bi_schedule_thursday_time',
            __( 'Thursday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('thursday')
        );
        add_settings_field(
            'power_bi_schedule_friday_time',
            __( 'Friday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('friday')
        );
        add_settings_field(
            'power_bi_schedule_saturday_time',
            __( 'Saturday', 'power-bi' ),
            array($this, 'power_bi_schedule_day_render'),
            'power_bi',
            'power_bi_schedule_section',
            array('saturday')
        );

    }

    /**
     * get plugin setting page for further call
     *
     * @return [type] [description]
     */
    function get_power_bi_settings() {
        return get_option( 'power_bi_settings' );    
    }
    

    /**
     * [power_bi__options_page description]
     *
     * @return [type] [description]
     */
    public function power_bi__options_page() {

        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved', 'wporg' ), 'updated' );
        }
        
        ?>

        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php settings_errors( 'wporg_messages' ); ?>
            <form action='options.php' method='post'>

                <?php
                settings_fields( 'power_bi' );
                do_settings_sections( 'power_bi' );
                submit_button();
                ?>

            </form>
        </div>
        <?php

    }

    public function power_bi_info_render() {
            echo esc_html( date("l, F j, Y, G:i", time()) ); 
            echo '<br />';
            echo esc_html( date_i18n("l, F j, Y, G:i") ) . ' (WordPress)'; 
            echo '<br />';
            $capacity_state = Power_Bi_Schedule_Resources::get_instance()->check_resource_capacity_state(true);
            echo 'resource state: ' . $capacity_state['properties']['state'] ?? '';
            echo '<br />';
            echo 'sku name: ' . $capacity_state['sku']['name'] ?? '';
            echo '<br />';
            echo 'sku tier: ' . $capacity_state['sku']['tier'] ?? '';
            echo '<br />';
            echo 'sku capacity: ' . $capacity_state['sku']['capacity'] ?? '';
            echo '<br />';
            echo 'location: ' . $capacity_state['location'] ?? '';
            echo '<br />------------------------------------------------------<br />';
            $capacity_fun_result = get_transient('power_bi_schedule_resource_update_capacity_fn');
            echo 'result from last capacity update: <br /><pre>';
            var_export($capacity_fun_result, false);
            echo '</pre>';
    }

    public function update_schedule($old_value, $value, $option){
        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":old_value: " . var_export($old_value, true));
        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":value: " . var_export($value, true));
        error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ":{$option}: " . var_export($option, true));

        $all_actions = array('resume', 'suspend');
        $capacity_skus = Power_Bi_Schedule_Resources::get_instance()->list_skus();
        foreach($capacity_skus as $sku){
            $all_actions[] = $sku['name'];
        }

        foreach($all_actions as $action){
            wp_clear_scheduled_hook( 'power_bi_action_cron', array($action) );
        }

        foreach($this->days_of_week as $day){
            foreach($this->day_variations as $variation){

                $time_name = $day . $variation . '_time';

                $time = $value[$time_name];

                if(strlen($time) < 1){
                    continue;
                }

                $action_name = $day . $variation . '_action';
                $action = $value[$action_name];

                if(strlen($action) < 1){
                    continue;
                }

                $day_time = strtotime($day);
                $day_time += intval(substr($time, 0, 2)) * 3600 +  intval(substr($time, 3, 2)) * 60;

                $time = custom_power_bi_strtotime(date('Y-m-d H:i:s', $day_time));

                $result = wp_schedule_event($time, 'weekly', 'power_bi_action_cron', array($action));
                error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':wp_schedule_event @ ' . date('Y-m-d H:i:s', $time) . ' : power_bi_action_cron:' . var_export($result, true));

            }

        }
    }

    public function power_bi_schedule_day_render($param) {

        $day = $param[0];

        $ob = '';

        foreach($this->day_variations as $variation){
            $day_variation = $day . $variation;
            $select_time_name = "power_bi_settings[{$day_variation}_time]";
            $select_action_name = "power_bi_settings[{$day_variation}_action]";
            $options = get_option('power_bi_settings');
            $time_name = $day_variation . '_time';
            $time = $options[$time_name] ? $options[$time_name] : '';
            $action_name = $day_variation . '_action';
            $action = $options[$action_name] ? $options[$action_name] : '';
    
            $ob .= "<select name='{$select_time_name}'>";
            $ob .= "<option value=''>";
            $ob .= __( 'TIME', 'power-bi' ); 
            $ob .= "</option>";
            $ob .= $this->time_options($time);
            $ob .= "</select>";
            $ob .= "<select name='{$select_action_name}'>";
            $ob .= "<option value=''>";
            $ob .= __( 'ACTION', 'power-bi' );
            $ob .= "</option>";
            $ob .= $this->action_options($action);
            $ob .= '</select>';
            $ob .= '<br />';
        }
        echo $ob;

    }

    public function time_options($sel =  "") {
        $ob = '';

        for($hours=0; $hours<24; $hours++) {

            for($mins=0; $mins<60; $mins+=10) {

                $time = str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT);

                $selected = selected($sel, $time, false);

                $ob .= "<option value='{$time}' {$selected}>{$time}</option>";

            }

        }

        return $ob;

    }

    public function action_options($sel =  "") {

    
        $selected = selected($sel, 'suspend', false);

        $ob = "<option value='suspend' {$selected}>Suspend</option>";

        $selected = selected($sel, 'resume', false);

        $ob .= "<option value='resume' {$selected}>Resume</option>";

        $capacity_skus = Power_Bi_Schedule_Resources::get_instance()->list_skus();
    
        if(isset($capacity_skus['error'])){
            error_log(basename(__FILE__) . ':' . __FUNCTION__ . ':' . __LINE__ . ':capacity_skus: ' . var_export($capacity_skus, true));
            return $ob; 
        }

        foreach($capacity_skus as $sku){
            $name = $sku['name'];
            $selected = selected($sel, $name, false);
            $ob .= "<option value='{$name}' {$selected} >Set to {$sku['name']}</option>";
        }
    
        return $ob;
    }
}

Power_Bi_Settings::get_instance();
