<?php
/**
 * Handles displays and hooks for the Power BI custom settings page.
 *
 * @package Power_Bi
 */

/** helper function sanitize inputs */
function power_bi_sanitize_settings($input) {
	return $input;
}

class Power_Bi_Settings
{
	/**
	 * Returns the instance.
	 */
	public static function get_instance()
	{
		static $instance = null;
		if (is_null($instance)) {
			$instance = new self();
			$instance->setup_actions();
		}
		return $instance;
	}
	/**
	 * Constructor method.
	 */
	private function __construct()
	{
	}
	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions()
	{
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'settings_init'));
	}
	/**
	 * [add_admin_menu description]
	 */
	public function add_admin_menu()
	{
		add_submenu_page('edit.php?post_type=powerbi', __('Power BI Settings', 'embed-power-bi'), __('Settings', 'embed-power-bi'), 'manage_options', 'powerbi', array($this, 'power_bi__options_page'));
	}

	/**
	 * [settings_init description]
	 * @return [type] [description]
	 */
	public function settings_init()
	{
		register_setting('power_bi', 'power_bi_settings', 'power_bi_sanitize_settings');

		add_settings_section(
			'power_bi_section',
			__('Azure Authorization', 'embed-power-bi'),
			'power_bi_section_callback',
			'power_bi'
		);
		add_settings_field(
			'power_bi_username',
			__('User Name', 'embed-power-bi'),
			'power_bi_username_render',
			'power_bi',
			'power_bi_section'
		);
		add_settings_field(
			'power_bi_password',
			__('Password', 'embed-power-bi'),
			'power_bi_password_render',
			'power_bi',
			'power_bi_section'
		);
		add_settings_field(
			'power_bi_client_id',
			__('Client ID', 'embed-power-bi'),
			'power_bi_client_id_render',
			'power_bi',
			'power_bi_section'
		);
		add_settings_field(
			'power_bi_client_secret',
			__('Client Secret', 'embed-power-bi'),
			'power_bi_client_secret_render',
			'power_bi',
			'power_bi_section'
		);
		add_settings_field(
			'power_bi_oauth_success',
			__('Oauth Status', 'embed-power-bi'),
			'power_bi_oauth_success_render',
			'power_bi',
			'power_bi_section'
		);
		// Schedule Power BI Resource
		add_settings_section(
			'power_bi_schedule_section',
			__('Power BI Resource On/Off Schedule', 'embed-power-bi'),
			'power_bi_schedule_section_callback',
			'power_bi'
		);
		// Added Option to view resource status
		add_settings_field(
			'power_bi_azure_resource_state',
			__('Power BI Resource Status', 'embed-power-bi'),
			'power_bi_azure_resource_state_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		// New setting for adding other required fields for make azure api call
		add_settings_field(
			'power_bi_azure_tenant_id',
			__('Tenant ID or Directory ID under Azure Active Directory for Office 365', 'embed-power-bi'),
			'power_bi_azure_tenant_id_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_azure_subscription_id',
			__('Subscription ID for Power BI Resource', 'embed-power-bi'),
			'power_bi_azure_subscription_id_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_azure_resource_group',
			__('Resource Group Name', 'embed-power-bi'),
			'power_bi_azure_resource_group_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_azure_capacity',
			__('Resource Name', 'embed-power-bi'),
			'power_bi_azure_capacity_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_sunday_time',
			__('Sunday', 'embed-power-bi'),
			'power_bi_schedule_sunday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_monday_time',
			__('Monday', 'embed-power-bi'),
			'power_bi_schedule_monday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_tuesday_time',
			__('Tuesday', 'embed-power-bi'),
			'power_bi_schedule_tuesday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_wednesday_time',
			__('Wednesday', 'embed-power-bi'),
			'power_bi_schedule_wednesday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_thursday_time',
			__('Thursday', 'embed-power-bi'),
			'power_bi_schedule_thursday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_friday_time',
			__('Friday', 'embed-power-bi'),
			'power_bi_schedule_friday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
		add_settings_field(
			'power_bi_schedule_saturday_time',
			__('Saturday', 'embed-power-bi'),
			'power_bi_schedule_saturday_render',
			'power_bi',
			'power_bi_schedule_section'
		);
	}
	/**
	 * get plugin setting page for further call
	 *
	 * @return [type] [description]
	 */
	function get_power_bi_settings()
	{
		return get_option('power_bi_settings');
	}
	/**
	 * [power_bi__options_page description]
	 *
	 * @return [type] [description]
	 */
	public function power_bi__options_page()
	{
		if (isset($_GET['settings-updated'])) {
			$nonce = isset($_REQUEST['power_bi_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['power_bi_nonce'])) : '';
			if (!empty($nonce) && wp_verify_nonce(sanitize_text_field($nonce), 'power_bi_settings')) {
				add_settings_error('wporg_messages', 'wporg_message', __('Settings Saved', 'embed-power-bi'), 'updated');
			}
			add_settings_error('wporg_messages', 'wporg_message', __('Settings Saved', 'embed-power-bi'), 'updated');
			// clear all cron setup previously //
			$days_arry = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
			foreach ($days_arry as $day_name) {
				// START //
				wp_clear_scheduled_hook('power_bi_schedule_resource_' . $day_name . '_start_cron');
				// PAUSE //
				wp_clear_scheduled_hook('power_bi_schedule_resource_' . $day_name . '_pause_cron');
			}
		}
		?>
		<div class="wrap">
			<h1>
				<?php echo esc_html(get_admin_page_title()); ?>
			</h1>
			<?php settings_errors('wporg_messages'); ?>
			<form action='options.php' method='post'>
				<?php
				settings_fields('power_bi');
				wp_nonce_field('power_bi_settings', 'power_bi_nonce', true, true);
				do_settings_sections('power_bi');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
Power_Bi_Settings::get_instance();