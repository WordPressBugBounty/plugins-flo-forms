<?php
/*
Plugin Name:       Flo Forms
Plugin URI:        https://flothemes.com/floforms
Description:       A easy to use contact form builder plugin
Version:           1.0.42
Author:            Flothemes
Author URI:        https://flothemes.com
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: flo-forms
Domain Path: /languages/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define('FLO_FORMS_20', true); // flag that this is the new rewritten plugin


if( !function_exists('get_plugin_data') ){
  require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

$plugin_data = get_plugin_data( plugin_dir_path( __FILE__ ) . 'flo-forms.php' );
global $flo_forms_activated;
$flo_forms_activated = false;

if(isset($plugin_data['Version'])) {
  $plugin_version = $plugin_data['Version'];
}else{
  $plugin_version = '1.0.0';
}

define( 'FLO_FORMS_VERSION', $plugin_version );


if(is_admin()) {

  if ( isset($plugin_data['Name']) && $plugin_data['Name'] == 'Flo Forms Pro' && file_exists(plugin_dir_path(__FILE__) . 'api-manager/am-license-menu.php')) {

    // Load the API Key library if it is not already loaded. Must be placed in the root plugin file.
    if ( ! class_exists( 'FFP_License_Menu' ) ) {
      // Load WC_AM_Client class if it exists.
      require_once( plugin_dir_path( __FILE__ ) . 'api-manager/am-license-menu.php' );

      /**
       * @param string $file             Must be __FILE__ from the root plugin file, or theme functions file.
       * @param string $software_title   Must be exactly the same as the Software Title in the product.
       * @param string $software_version This product's current software version.
       * @param string $plugin_or_theme  'plugin' or 'theme'
       * @param string $api_url          The URL to the site that is running the API Manager. Example: https://www.toddlahman.com/
       *
       * @return \AM_License_Submenu|null
       */

      FFP_License_Menu::instance( __FILE__, 'Flo Forms Pro', $plugin_version, 'plugin', 'https://flothemes.com/' );

			if ( get_option( 'flo_forms_pro_activated' ) == 'Activated' ) {
			    $flo_forms_activated = true;
			}
    }

  }


}

// PRO Define
if($plugin_data['Name'] == 'Flo Forms Pro') {
  $is_pro_version = true;

  if(file_exists(dirname(__FILE__).'/pro/flo-forms-pro-public.php')) {
    include_once dirname(__FILE__).'/pro/flo-forms-pro-public.php';
  }
}else{
  $is_pro_version = false;
}

define( 'IS_FLO_FORMS_PRO', $is_pro_version );


if ( get_option( 'flo_forms_pro_activated' ) == 'Activated' ) {
		$flo_forms_activated = true;
}

if(!function_exists('flo_maybe_deactivate_flo_forms')){
	function flo_maybe_deactivate_flo_forms() {

		$plugin_dirname = dirname(__FILE__);

		if(is_admin() && strpos($plugin_dirname, 'flo-forms-pro') !== false ){
			// we will disable 'flo-forms' only if we are installing flo-forms-pro

			// this should make the 'is_plugin_active' function available
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			if(function_exists('is_plugin_active')){
				if ( is_plugin_active( 'floforms/flo-forms.php' ) || is_plugin_active( 'flo-forms/flo-forms.php' ) ) {

					deactivate_plugins('floforms/flo-forms.php');
          deactivate_plugins('flo-forms/flo-forms.php');

					// reload the current page to avoid warnings
					update_option('acf_generate_notice', 1);
					header("Location: ".$_SERVER['REQUEST_URI']);
				}
			}

			if(function_exists('is_plugin_active_for_network')){
				if ( is_plugin_active_for_network( 'floforms/flo-forms.php' ) || is_plugin_active_for_network( 'flo-forms/flo-forms.php' ) ) {

					deactivate_plugins('floforms/flo-forms.php');
          deactivate_plugins('flo-forms/flo-forms.php');

				}
			}
		}
	}
}

// if Flo Forms plugin is activated, we disable it to avoid conflicts
add_action('init','flo_maybe_deactivate_flo_forms', 1);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-flo-forms-activator.php
 */

if(!function_exists('activate_flo_forms_pro')) {
	function activate_flo_forms_pro() {


		require_once plugin_dir_path( __FILE__ ) . 'includes/class-flo-forms-activator.php';
		Flo_Forms_Activator::activate();
	}
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-flo-forms-deactivator.php
 */
if(!function_exists('deactivate_flo_forms_pro')) {
	function deactivate_flo_forms_pro() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-flo-forms-deactivator.php';
		Flo_Forms_Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'activate_flo_forms_pro' );
register_deactivation_hook( __FILE__, 'deactivate_flo_forms_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-flo-forms.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if(!function_exists('run_flo_forms_pro')) {
	function run_flo_forms_pro() {

		$plugin = new Flo_Forms();
		$plugin->run();

	}
}
//run_flo_forms_pro();
add_action('init','run_flo_forms_pro', 1);
