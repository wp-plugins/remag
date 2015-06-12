<?php
/**
 * Remag WordPress plugin
 *
 * @package   Remag WordPress plugin
 * @author    Remag team <dev@nozbe.com>
 * @license   GPL-2.0+
 * @link      http://remag.me
 * @copyright 2014-2015 Nozbe
 */

class Remag {
	const VERSION = '1.0.0';
	protected $plugin_slug = 'remag';
	protected static $instance = null;

	private function __construct() {
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide  ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}
	}

	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();
				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}
	}

	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	public static function before_blog_loaded() {
		$hook = new RemagHook();

		$settings = (array)get_option('remag_settings');

		if (isset($settings['smartbanner']) && $settings['smartbanner'] === true) {
			add_action('wp_head', array($hook, 'smartbanner'));
		}
	}

	private static function get_blog_ids() {
		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	private static function single_activate() {
	}

	private static function single_deactivate() {
	}

	// public function load_plugin_textdomain() {
	//
	// 	$domain = $this->plugin_slug;
	// 	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	//
	// 	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	// 	load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	//
	// }

}

class RemagHook {

	public function smartbanner() {
		return;
		$settings = (array)get_option('remag_settings');
		$app_store_id = '';
		$identifier = '';
		
		if (isset($settings['magazine'])) {
			$app_store_id = $settings['magazine']['app_store_id'];
			$identifier = $settings['magazine']['identifier'];
		}
		echo '<meta name="apple-itunes-app" content="app-id=' . $app_store_id . '">' . "\n";
		echo '<script src="https://api.remag.me/magazines/' . $identifier . '/smartbanner.js"></script>' . "\n";
	}
}
