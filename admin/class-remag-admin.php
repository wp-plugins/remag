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

class Remag_Admin {
	protected static $instance = null;
	protected $plugin_screen_hook_suffix = null;

	private function __construct() {
		if ($_GET['page'] == 'remag') {

			if ($_GET['mode'] == 'settings') {
				if (isset($_POST['data'])) {
					update_option('remag_settings', array_merge(
						(array)get_option('remag_settings'),
						(array)json_decode($_POST['data'], true)
					));
				}
				$this->return_json(get_option('remag_settings'));
			} else {

				$query = array(
				    'posts_per_page' => 20,
				    'offset'         => 0,
				    'post_status'	 => array('draft', 'future', 'private', 'publish')
				);

				if (is_array($_POST['ids']) && !empty($_POST['ids'])) {
					$query['posts_per_page'] = -1; // no limit
					$posts = get_posts(array_merge($query, array(
					    'include'        => implode(', ', $_POST['ids'])
					)));
					foreach ($posts as $key => $post) {
						$posts[$key]->meta = get_post_custom($post->ID);
						$posts[$key]->permalink = get_permalink($post->ID);
					}
					$this->return_json($posts);
				}
				else if ($_GET['json']) {

					$res = array();
					$i = 0;

					while ($posts = get_posts($query)) {
						foreach ($posts as $post) {
							$res[] = array('ID' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status);
						}
						$i++;
						$query['offset'] = $i*$query['posts_per_page'];
					}

					$this->return_json(array('blog_title' => get_bloginfo('name'), 'posts' => $res));
				}
			}
		}

		$plugin = Remag::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function enqueue_admin_styles() {
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Remag::VERSION );
		}

	}

	public function enqueue_admin_scripts() {
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Remag::VERSION );
		}

	}

	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Remag', $this->plugin_slug ),
			__( 'Remag', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' ),
			// data URL for menu icon SVG
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMjBweCIgaGVpZ2h0PSIyMHB4IiB2aWV3Qm94PSIwIDAgMjAgMjAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDIwIDIwIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxyZWN0IHg9IjUiIHk9IjUiIGZpbGw9IiM5ODk4OTkiIHdpZHRoPSIxMCIgaGVpZ2h0PSIyIi8+DQoJPHJlY3QgeD0iNSIgeT0iOSIgZmlsbD0iIzk4OTg5OSIgd2lkdGg9IjEwIiBoZWlnaHQ9IjIiLz4NCgk8cmVjdCB4PSI1IiB5PSIxMyIgZmlsbD0iIzk4OTg5OSIgd2lkdGg9IjEwIiBoZWlnaHQ9IjIiLz4NCgk8cGF0aCBmaWxsPSIjOTg5ODk5IiBkPSJNMCwwdjIwaDIwVjBIMHogTTE4LDE4SDJWMmgxNlYxOHoiLz4NCjwvZz4NCjwvc3ZnPg0K',
			'25.0016931' // reduce risk of conflict with the other plugin
		);
	}

	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Manage your magazine', $this->plugin_slug ) . '</a>'
			),
			$links
		);
	}

	protected function return_json ($data) {
		header('Content-type: application/json');
		$json_data = json_encode($data);
		echo $_GET['callback'] ? $_GET['callback'].'('.$json_data.')' : $json_data;
		exit;
	}

}