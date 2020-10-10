<?php
if (!defined('ABSPATH')) {exit;}
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */
class Leopard_Wordpress_Offload_Media {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Leopard_Wordpress_Offload_Media_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LEOPARD_WORDPRESS_OFFLOAD_MEDIA_VERSION' ) ) {
			$this->version = LEOPARD_WORDPRESS_OFFLOAD_MEDIA_VERSION;
		} else {
			$this->version = '1.0.24';
		}
		$this->plugin_name = 'leopard-wordpress-offload-media';

		$this->load_dependencies();
		$this->set_locale();
		$this->include_vendor();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Leopard_Wordpress_Offload_Media_Loader. Orchestrates the hooks of the plugin.
	 * - Leopard_Wordpress_Offload_Media_i18n. Defines internationalization functionality.
	 * - Leopard_Wordpress_Offload_Media_Admin. Defines all hooks for the admin area.
	 * - Leopard_Wordpress_Offload_Media_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-leopard-wordpress-offload-media-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-leopard-wordpress-offload-media-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-leopard-wordpress-offload-media-admin.php';

		/**
		 * Public
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-leopard-wordpress-offload-media-public.php';

		$this->loader = new Leopard_Wordpress_Offload_Media_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Leopard_Wordpress_Offload_Media_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Leopard_Wordpress_Offload_Media_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin 		= new Leopard_Wordpress_Offload_Media_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_licenser 	= new Leopard_Wordpress_Offload_Media_Licenser(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DIR_FILE, $this->plugin_name, $this->version );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'sync_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'setup_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_licenser, 'show_admin_notices' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 1000 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 1000 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'init', $plugin_admin, 'hanlder_settings' );
		$this->loader->add_action( 'init', $plugin_admin, 'hanlder_kill_sync_process' );

		if(nou_leopard_offload_media_is_plugin_setup()){

			$this->loader->add_filter( 'wp_update_attachment_metadata', $plugin_admin, 'wp_update_attachment_metadata', 110, 2 );

			$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'attachment_provider_meta_box', 10, 4 );
			$this->loader->add_filter( 'delete_attachment', $plugin_admin, 'delete_attachment', 20 );
			$this->loader->add_filter( 'media_row_actions', $plugin_admin, 'media_row_actions_extra', 10, 2 );
			$this->loader->add_filter( "bulk_actions-upload", $plugin_admin, 'bulk_actions_extra_options' );
			$this->loader->add_filter( 'handle_bulk_actions-upload', $plugin_admin, 'do_bulk_actions_extra_options', 10, 3 );

			$this->loader->add_action( 'post_action_nou_leopard_wom_copy_to_s3', $plugin_admin, 'post_action_copy_to_c3', 10, 1 );
			$this->loader->add_action( 'post_action_nou_leopard_wom_remove_from_s3', $plugin_admin, 'post_action_remove_from_s3', 10, 1 );
			$this->loader->add_action( 'post_action_nou_leopard_wom_copy_to_server_from_s3', $plugin_admin, 'post_action_copy_to_server_from_c3', 10, 1 );
			$this->loader->add_action( 'post_action_nou_leopard_wom_remove_from_server', $plugin_admin, 'post_action_remove_from_server', 10, 1 );
			$this->loader->add_action( 'post_action_nou_leopard_wom_build_webp', $plugin_admin, 'post_action_build_webp', 10, 1 );
			$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'cloud_served_filtering',10, 1 );
			$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'cloud_served_filter_request_query' , 10);

			$this->loader->add_filter( 'wp_get_attachment_url', $plugin_admin, 'wp_get_attachment_url', 99, 2 );
			$this->loader->add_filter( 'wp_get_attachment_image_src', $plugin_admin, 'maybe_encode_wp_get_attachment_image_src', 99, 4 );
			$this->loader->add_filter( 'get_image_tag', $plugin_admin, 'maybe_encode_get_image_tag', 99, 6 );
			$this->loader->add_filter( 'wp_prepare_attachment_for_js', $plugin_admin, 'maybe_encode_wp_prepare_attachment_for_js', 99, 3 );
			$this->loader->add_filter( 'image_get_intermediate_size', $plugin_admin, 'maybe_encode_image_get_intermediate_size', 99, 3 );
			$this->loader->add_filter( 'get_attached_file', $plugin_admin, 'get_attached_file', 10, 2 );
			$this->loader->add_filter( 'ajax_query_attachments_args', $plugin_admin, 'ajax_query_attachments_args', 10 );

		}

	}

	public function define_public_hooks(){
		$plugin_public = new Leopard_Wordpress_Offload_Media_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 100000 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts', 100000 );
	}

	/**
	 * Register all vendor
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function include_vendor() {
		include_once( ABSPATH . 'wp-includes/pluggable.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		include_once( ABSPATH . 'wp-includes/theme.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		require_once( ABSPATH . 'wp-admin/includes/screen.php' );

		include_once LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . '/vendor/autoload.php';
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-storage.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-google.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-aws-client.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-wasabi-client.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-do-client.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-utils.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-filter.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-filter-s3-to-local.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-filter-local-to-s3.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-assets.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-sync.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-ajax.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-compatibility.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-download.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-background-process.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-sync-process.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-copy-to-cloud-process.php' );
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-webp.php' );

		if ( nou_leopard_offload_media_is_plugin_setup() ) {
			
			$background_process = new Leopard_Wordpress_Offload_Media_Sync_Process();
			$copy_background_process = new Leopard_Wordpress_Offload_Media_Copy_To_Cloud_Process();
			
			$ajax = new Leopard_Wordpress_Offload_Media_Ajax();

			if(nou_leopard_offload_media_enable_rewrite_urls()){
				$filter_S3_to_local = new Leopard_Wordpress_Offload_Media_Filter_S3_To_Local();
				$filter_local_to_S3 = new Leopard_Wordpress_Offload_Media_Filter_Local_To_S3();
				$compatibility = new Leopard_Wordpress_Offload_Media_Compatibility();
			}

			if(class_exists('Easy_Digital_Downloads')){
				require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-edd.php' );
				$edd = new Leopard_Wordpress_Offload_Media_Edd();
			}

			if (is_plugin_active('woocommerce/woocommerce.php')) {
				require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-woocommerce.php' );
				$woo = new Leopard_Wordpress_Offload_Media_Woocommerce();
			}

			if (is_plugin_active('woocommerce/woocommerce.php') || class_exists('Easy_Digital_Downloads')) {
				require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-shortcodes.php' );
				$shortcodes = new Leopard_Wordpress_Offload_Media_Shortcodes();
			}

			$enable_assets = get_option('nou_leopard_offload_media_assets_rewrite_urls_checkbox', '');
			if ($enable_assets) {
				$assets = new Leopard_Wordpress_Offload_Media_Assets();
			}
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Leopard_Wordpress_Offload_Media_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
