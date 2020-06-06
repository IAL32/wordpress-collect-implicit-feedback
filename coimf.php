<?php
/**
 * Plugin Name:       Collect Implicit User Feedback
 * Plugin URI:        https://github.com/IAL32/wordpress-collect-implicit-feedback
 * Description:       Collects user navigation data
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      7.0
 * Author:            Adrian Castro
 * Author URI:        https://github.com/IAL32
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       coimf
 * Domain Path:       /languages
 */

defined( "WPINC" ) || die;

/**
 * In the debug status, the plugin will not make any permanent changes.
 */
define( "COIMF_DEBUG", true );
define( "COIMF_DRY_UPDATE", false );
define( "COIMF_COOKIE_FORCE", false );

define( "COIMF_ROOT_URL", plugin_dir_url( __FILE__ ) );
define( "COIMF_ROOT_FOLDER", plugin_dir_path( __FILE__ ) );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( "COIMF_VERSION", "1.0.0" );

define( "COIMF_API_VERSION", "v1" );

define( "COIMF_NAME", "Coimf" );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-coimf-activator.php
 */
function activate_Coimf() {
	require_once plugin_dir_path( __FILE__ ) . "includes/class-coimf-activator.php";
	\Coimf\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-coimf-deactivator.php
 */
function deactivate_Coimf() {
	require_once plugin_dir_path( __FILE__ ) . "includes/class-coimf-deactivator.php";
	\Coimf\Deactivator::deactivate();
}

register_activation_hook( __FILE__, "activate_Coimf" );
register_deactivation_hook( __FILE__, "deactivate_Coimf" );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . "includes/class-coimf.php";

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Coimf() {

	$vPlugin = new Coimf();
	$vPlugin->run();

}
run_Coimf();
