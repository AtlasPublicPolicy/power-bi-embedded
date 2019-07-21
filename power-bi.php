<?php
/**
 * Plugin Name: Power BI Embedded for WordPress
 * Plugin URI: https://github.com/atlaspolicy/wordpress-power-bi-embedded
 * Description: Use Power BI Embedded to embed dashboards, reports, Q&A, visuals, and tiles in your WordPress website.
 * Version: 1.1.1
 * Author: Atlas Public Policy
 * Author URI: http://www.atlaspolicy.com
 * Text Domain: power-bi
 * Domain Path: /languages/
 * License: GNU General Public License v3.0
 *
 * @package Power_Bi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'POWER_BI_VERSION', '1.1.1' );
define( 'POWER_BI_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'POWER_BI_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-power-bi.php';

/**
 * Gets the instance of the `Power_Bi` class.  This function is useful for quickly grabbing data
 * used throughout the plugin.
 */
function power_bi() {
	return Power_Bi::get_instance();
}

// Let's roll!
power_bi(); // this sets up the instance
