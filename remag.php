<?php
/**
 * @package   Remag WordPress plugin
 * @author    Remag team <dev@nozbe.com>
 * @license   GPL-2.0+
 * @link      http://remag.me
 * @copyright 2014-2015 Nozbe
 *
 * @wordpress-plugin
 * Plugin Name:       Remag
 * Plugin URI:        http://remag.me
 * Description:       Take your blog to a whole new level by converting it to a magazine app for Android, iPad and the iPhone.
 * Version:           1.0.0
 * Author:            Nozbe
 * Author URI:        http://nozbe.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-remag.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'Remag', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Remag', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Remag', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

    if (is_admin()) {
    	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-remag-admin.php' );
    	add_action( 'plugins_loaded', array( 'Remag_Admin', 'get_instance' ) );
    }
    else {
        Remag::before_blog_loaded();
    }

}
