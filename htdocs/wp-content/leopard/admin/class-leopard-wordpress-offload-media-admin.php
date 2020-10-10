<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/admin
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */
class Leopard_Wordpress_Offload_Media_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function cloud_served_filtering($post){
		$current_screen = get_current_screen();
		if ( $current_screen->base !== 'upload' ) return;
		$selected = 'all';
	    $request_attr = 'leopard_served';
	    if ( isset($_REQUEST[$request_attr]) ) {
	      $selected = $_REQUEST[$request_attr];
	    }

	    echo '<select id="leopard_served" name="leopard_served">';
	    echo '<option value="all">' . esc_html__( 'All files cloud vs server', 'leopard-wordpress-offload-media' ) . ' </option>';
	    echo '<option value="1" '.selected($selected, 1, false).'>' . esc_html__( 'All files stored in the server', 'leopard-wordpress-offload-media' ) . ' </option>';
	    echo '<option value="2" '.selected($selected, 2, false).'>' . esc_html__( 'All files stored in the cloud', 'leopard-wordpress-offload-media' ) . ' </option>';
	    echo '</select>';
	}

	public function ajax_query_attachments_args($query){
		
		if(isset($_REQUEST['query'])){

			$request_query = $_REQUEST['query'];

			if( !isset($request_query['leopard_served']) ){
		    	return $query;
		    }

		    if( 1 == $request_query['leopard_served'] ){
		    	$query['meta_query'] = array(
		    		'relation' => 'OR',
			        array(
			            'key'     => '_wp_nou_leopard_wom_s3_path',
			            'compare' => 'NOT EXISTS',
			        ),
			        array(
			            'key'     => '_wp_nou_leopard_wom_s3_path',
			            'value'   => '_wp_nou_leopard_wom_s3_path_not_in_used',
			            'compare' => '==',
			        ),
			    );
		    }

		    if( 2 == $request_query['leopard_served'] ){
		    	$query['meta_query'] = array(
			        array(
			            'key'     => '_wp_nou_leopard_wom_s3_path',
			            'value'   => '_wp_nou_leopard_wom_s3_path_not_in_used',
			            'compare' => '!=',
			        ),
			    );
		    }
		}
		return $query;
	}

	public function cloud_served_filter_request_query($query){
		if( !(is_admin() AND $query->is_main_query()) ){ 
	      return $query;
	    }

	    if( 'attachment' !== $query->query['post_type'] ){
	      	return $query;
	    }

	    if( !isset($_REQUEST['leopard_served']) ){
	    	return $query;
	    }

	    if( 1 == $_REQUEST['leopard_served'] ){
		    $query->set( 'meta_query', array(
	    		'relation' => 'OR',
		        array(
		            'key'     => '_wp_nou_leopard_wom_s3_path',
		            'compare' => 'NOT EXISTS',
		        ),
		        array(
		            'key'     => '_wp_nou_leopard_wom_s3_path',
		            'value'   => '_wp_nou_leopard_wom_s3_path_not_in_used',
		            'compare' => '==',
		        ),
		    ) );
	    }

	    if( 2 == $_REQUEST['leopard_served'] ){
		    $query->set( 'meta_query', array(
	            array(
	                'key'     => '_wp_nou_leopard_wom_s3_path',
	                'compare' => '!=',
	                'value'   => '_wp_nou_leopard_wom_s3_path_not_in_used',
	                'type'    => 'CHAR',
	            )
	        ) );
	    }
	    
		return $query;
	}

	public function sync_notice(){
		$has_bk_option = false;
		$percen = leopard_wordpress_offload_media_calculator_sync_processed();
		$sync_status = get_option('nou_leopard_offload_media_synced_status', 0);
		if($sync_status > 0){
			$has_bk_option = true;
		}

		if($has_bk_option){

			$class = 'leopard-sync-notice notice notice-error';
			$text    = esc_html__( 'Kill sync process.', 'leopard-wordpress-offload-media' );
			$link    = esc_url( add_query_arg( array('page' => 'leopard_offload_media', 'leopard_action' => 'kill_sync'), admin_url('admin.php') ) );

			$message = esc_html__( 'Leopard Offload Media Synchronize:', 'leopard-wordpress-offload-media' );
			
			printf( '<div class="%1$s"><div class="leopard-sync-notice-wrap"><div class="leopard-sync-notice-title">%5$s</div><div class="iziToastloading spin_loading"></div><div class="progress-bar"><span id="percent" style="line-height: 9px;height: 9px;right: -5%;"></span><span class="bar" style="height: 9px;"><span class="progress"></span></span></div><div class="current-sync-process progress_count">%2$s</div> <a href="%3$s"><strong>%4$s</strong></a></div></div>', $class, $percen.'%', $link, $text, $message );
		}
	}

	public function hanlder_kill_sync_process(){
		if(isset($_GET['leopard_action']) && $_GET['leopard_action'] == 'kill_sync'){
			
			update_option('nou_leopard_offload_media_synced_data', []);
			update_option('nou_leopard_offload_media_synced_status', 0);

			$background_process = new Leopard_Wordpress_Offload_Media_Sync_Process();
			$background_process->kill_process();
			wp_redirect(admin_url('admin.php?page=leopard_offload_media&tab=sync'));
			exit;
		}
	}

	/**
	 * Warning if setup not complete.
	 *
	 * @since    1.0.0
	 */
	public function setup_notice(){
		
		$class = 'notice notice-error';

		if(!nou_leopard_offload_media_version_check()){
			$message = wp_kses( __( "<strong>Leopard Offload Media</strong>: Your PHP version is low. Please, upgrade PHP 7.0 or higher.", 'leopard-wordpress-offload-media' ), array( 'strong' => array() ) );
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}else{

			$active 		= get_option('nou_leopard_offload_media_license_active');
			$emailAddress 	= get_option('nou_leopard_offload_media_license_email');
			$purchase_key 	= get_option('nou_leopard_offload_media_license_key');
		    if ( empty($purchase_key) || empty($emailAddress) || $active != '1' ){
		    	return '';
		    }

			if ( ! nou_leopard_offload_media_is_plugin_setup() ) {
					
				$text    = esc_html__( 'Click here!', 'leopard-wordpress-offload-media' );
				$link    = esc_url( add_query_arg( array('page' => 'leopard_offload_media'), admin_url('admin.php') ) );

				$message = wp_kses( __( "<strong>Leopard Offload Media</strong>: Your settings are incorrect, or the bucket does not exist. Please verify your settings and update them.", 'leopard-wordpress-offload-media' ), array( 'strong' => array() ) );
				
				printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', $class, $message, $link, $text );
			}
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Leopard_Wordpress_Offload_Media_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Leopard_Wordpress_Offload_Media_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leopard-wordpress-offload-media-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'izitoast', plugin_dir_url( __FILE__ ) . 'css/iziToast.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Leopard_Wordpress_Offload_Media_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Leopard_Wordpress_Offload_Media_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$provider = leopard_offload_media_provider();
		wp_enqueue_script( 'izitoast', plugin_dir_url( __FILE__ ) . 'js/iziToast.min.js', array( 'jquery', 'jquery-ui-sortable' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'-media', plugin_dir_url( __FILE__ ) . 'js/media.js', array('jquery',
				'media-editor', // Used in image filters.
				'media-views',
				'media-grid',
				'wp-util',
				'wp-api'), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leopard-wordpress-offload-media-admin.js', array( 'jquery' ), $this->version, true );

		wp_enqueue_script( 'leopard-settings', plugin_dir_url( __FILE__ ) . 'js/settings.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'leopard-cloud-filter', plugin_dir_url( __FILE__ ) . 'js/cloud-filter.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'jquery', 'leopard_wordpress_offload_media_params', array(
			'ajax_url' 				=> esc_url( admin_url( 'admin-ajax.php' ) ),
			'ajax_nonce' 			=> wp_create_nonce( 'leopard_wordpress_offload_media_nonce' ),
			'is_plugin_setup' 			=> (nou_leopard_offload_media_is_plugin_setup() ? 1 : 0),
			'sync_provider_required'             => esc_html__('Please, select provider.', 'leopard-wordpress-offload-media'),
			'scan_title'             => esc_html__('Scanning...', 'leopard-wordpress-offload-media'),
			'sync_title'             => esc_html__('May take a few minutes to scan your bucket.', 'leopard-wordpress-offload-media'),
			'download_title'             => esc_html__('Leopard - Download all files from bucket to server.', 'leopard-wordpress-offload-media'),
			'remove_all_files_from_server_title'             => esc_html__('Leopard - Remove all files from server.', 'leopard-wordpress-offload-media'),
			'remove_all_files_from_bucket_title'             => esc_html__('Leopard - Remove all files from bucket.', 'leopard-wordpress-offload-media'),
			'copy_all_files_to_bucket_title'             => esc_html__('Leopard - Offloading media library items to bucket.', 'leopard-wordpress-offload-media'),
			'confirm_kill_process' => esc_html__('Are you sure you want to kill this process?', 'leopard-wordpress-offload-media'),
			'sync_percen'             => leopard_wordpress_offload_media_calculator_sync_processed(),
			'sync_processed'             => get_option('nou_leopard_offload_media_processed_sync_data'),
			'uploading_title'             => esc_html__('Uploading...', 'leopard-wordpress-offload-media'),
			'upload_title'             => esc_html__('Upload', 'leopard-wordpress-offload-media'),
			'close_title'             => esc_html__('Close', 'leopard-wordpress-offload-media'),
			'create_title'             => esc_html__('Create', 'leopard-wordpress-offload-media'),
			'confirm_kill_process_btn' => esc_html__('Kill process', 'leopard-wordpress-offload-media'),
			'popup_title'             => esc_html__('Leopard - Offloading media library', 'leopard-wordpress-offload-media'),
			'copy_to_s3_text'             => leopard_offload_media_text_actions('nou_leopard_wom_copy_to_s3'),
			'remove_from_s3_text'         => leopard_offload_media_text_actions('nou_leopard_wom_remove_from_s3'),
			'copy_to_server_from_s3_text' => leopard_offload_media_text_actions('nou_leopard_wom_copy_to_server_from_s3'),
			'remove_from_server_text'     => leopard_offload_media_text_actions('nou_leopard_wom_remove_from_server'),
			'build_webp_text'     => leopard_offload_media_text_actions('nou_leopard_wom_build_webp'),
			'current_provider'     => $provider::name(),
			'strings' => array(
				'provider'      => _x( 'Storage Provider', 'Storage provider key name', 'leopard-wordpress-offload-media' ),
				'provider_name' => _x( 'Storage Provider', 'Storage provider name', 'leopard-wordpress-offload-media' ),
				'bucket'        => _x( 'Bucket', 'Bucket name', 'leopard-wordpress-offload-media' ),
				'key'           => _x( 'Path', 'Path to file in bucket', 'leopard-wordpress-offload-media' ),
				'region'        => _x( 'Region', 'Location of bucket', 'leopard-wordpress-offload-media' ),
				'url'           => esc_html__( 'URL', 'leopard-wordpress-offload-media' ),
				),
			'filter_all' 		=> esc_html__( 'All files cloud vs server', 'leopard-wordpress-offload-media' ),
			'filter_cloud_served' => array(
				[
					'slug' => '1',
					'name' => esc_html__( 'All files stored in the server', 'leopard-wordpress-offload-media' )
				],
				[
					'slug' => '2',
					'name' => esc_html__( 'All files stored in the cloud', 'leopard-wordpress-offload-media' )
				]
			)
		) );
	}


	/**
	 * Add menu item.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		$active 		= get_option('nou_leopard_offload_media_license_active');
		$emailAddress 	= get_option('nou_leopard_offload_media_license_email');
		$purchase_key 	= get_option('nou_leopard_offload_media_license_key');
	    if ( empty($purchase_key) || empty($emailAddress) || $active != '1' ) {
			add_menu_page( 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ),
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				'edit_theme_options', 
				'leopard_offload_media_licenser', 
				array($this, 'license_menu_callback'),
				plugin_dir_url( __FILE__ ). 'images/logo.png'
			);
		}else{
			add_menu_page( 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ),
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				'edit_theme_options', 
				'leopard_offload_media', 
				array($this, 'admin_menu_callback'), 
				plugin_dir_url( __FILE__ ). 'images/logo.png'
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'Storage Settings', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'Bucket Settings', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media&tab=generalsettings', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'Assets', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media&tab=assets', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'URL Rewriting', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media&tab=RewriteUrl', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'CORS', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media&tab=cors', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'Advanced', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media&tab=advanced', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Leopard Offload Media', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'Sync Data', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media&tab=sync', 
				array($this, 'admin_menu_callback')
			);
			add_submenu_page( 
				'leopard_offload_media', 
				esc_html__( 'Product License', 'leopard-wordpress-offload-media' ), 
				esc_html__( 'Product License', 'leopard-wordpress-offload-media' ),
				'edit_theme_options', 
				'leopard_offload_media_licenser', 
				array($this, 'license_menu_callback')
			);
		}	
	}


	/**
	 * Menu item callback.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu_callback(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/leopard-wordpress-offload-media-admin-display.php';
	}


	/**
	 * Menu item callback.
	 *
	 * @since    1.0.0
	 */
	public function license_menu_callback(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/leopard-wordpress-offload-media-admin-license.php';
	}

	public function activate_license(){
		if ( is_admin() ) {
		    $error = '';
		    $message = '';
		    $licenser = new Leopard_Wordpress_Offload_Media_Licenser(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DIR_FILE, $this->plugin_name, $this->version );
		    $licenser->active_plugin($error, $message);
		}    
	}

	public function deactivate_license(){
		if ( is_admin() ) {
		    $licenser = new Leopard_Wordpress_Offload_Media_Licenser(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DIR_FILE, $this->plugin_name, $this->version );
		    $licenser->deactivate();
		}    
	}

	/**
	 * Hanlder settings save data.
	 *
	 * @since    1.0.0
	 */
	public function hanlder_settings(){
		if( isset( $_POST['nou_leopard_wom_settings_nonce'] ) ) {
          	if ( wp_verify_nonce( $_POST['nou_leopard_wom_settings_nonce'], 'nou_leopard_wom_settings_nonce' ) ) {

          		if( isset($_POST['nou_leopard_offload_media_license_key']) || isset($_POST['nou_leopard_offload_media_license_email']) ){
          			update_option('nou_leopard_offload_media_license_key', $_POST['nou_leopard_offload_media_license_key']);
          			update_option('nou_leopard_offload_media_license_email', $_POST['nou_leopard_offload_media_license_email']);

          			$this->activate_license();
          		}

          		if( isset($_POST['nou_leopard_offload_media_deactivate_license']) && $_POST['nou_leopard_offload_media_deactivate_license'] === 'ok' ){
          			$this->deactivate_license();
          		}

          		if(isset($_POST['nou_leopard_wom_connection_access_key_text']) || isset($_POST['nou_leopard_wom_connection_secret_access_key_text']) || isset($_POST['nou_leopard_wom_connection_provider']) || isset($_POST['nou_leopard_wom_connection_credentials'])){
          			
          			$default = get_option('nou_leopard_offload_media');
          			$default['access_key'] = isset($_POST['nou_leopard_wom_connection_access_key_text']) ? $_POST['nou_leopard_wom_connection_access_key_text'] : '';
          			$default['secret_access_key'] = isset($_POST['nou_leopard_wom_connection_secret_access_key_text']) ? $_POST['nou_leopard_wom_connection_secret_access_key_text'] : '';
          			$default['provider'] = isset($_POST['nou_leopard_wom_connection_provider']) ? $_POST['nou_leopard_wom_connection_provider'] : 'aws';
          			$credentials = isset($_POST['nou_leopard_wom_connection_credentials']) ? $_POST['nou_leopard_wom_connection_credentials'] : '';
          			update_option('nou_leopard_offload_media_google_credentials', json_decode(stripslashes($credentials), true));
          			update_option('nou_leopard_offload_media', $default);

          			if(isset($_POST['nou_leopard_wom_connection_provider']) && $_POST['nou_leopard_wom_connection_provider'] == 'DO'){
          				$region = isset($_POST['nou_leopard_offload_media_bucket_regional']) ? $_POST['nou_leopard_offload_media_bucket_regional'] : 'nyc3';
          				update_option('nou_leopard_offload_media_bucket_regional', $region);
          			}

          			update_option('nou_leopard_offload_media_connection_bucket_selected_select', '');

          			$this->checking_credentials();
          		}

          		if(isset($_POST['nou_leopard_offload_media_general_tab'])){

	          		if(isset($_POST['nou_leopard_offload_media_connection_bucket_selected_select'])){

	          			update_option('nou_leopard_offload_media_connection_bucket_selected_select', $_POST['nou_leopard_offload_media_connection_bucket_selected_select']);

	          			if(!empty($_POST['nou_leopard_offload_media_connection_bucket_selected_select'])){
	          				$this->bucket_base_url();
	          			}

	          		}

	          		if(isset($_POST['nou_leopard_offload_media_bucket_folder_main'])){
	          			update_option('nou_leopard_offload_media_bucket_folder_main', $_POST['nou_leopard_offload_media_bucket_folder_main']);
	          		}else{
	          			update_option('nou_leopard_offload_media_copy_file_s3_checkbox', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_copy_file_s3_checkbox'])){
	          			update_option('nou_leopard_offload_media_copy_file_s3_checkbox', $_POST['nou_leopard_offload_media_copy_file_s3_checkbox']);
	          		}else{
	          			update_option('nou_leopard_offload_media_copy_file_s3_checkbox', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_remove_from_server_checkbox'])){
	          			update_option('nou_leopard_offload_media_remove_from_server_checkbox', $_POST['nou_leopard_offload_media_remove_from_server_checkbox']);
	          		}else{
	          			update_option('nou_leopard_offload_media_remove_from_server_checkbox', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_private_public_radio_button'])){
	          			update_option('nou_leopard_offload_media_private_public_radio_button', $_POST['nou_leopard_offload_media_private_public_radio_button']);
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_cache_control'])){
	          			update_option('nou_leopard_offload_media_cache_control', $_POST['nou_leopard_offload_media_cache_control']);
	          		}else{
	          			update_option('nou_leopard_offload_media_cache_control', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_accepted_filetypes'])){
	          			update_option('nou_leopard_offload_media_accepted_filetypes', $_POST['nou_leopard_offload_media_accepted_filetypes']);
	          		}else{
	          			update_option('nou_leopard_offload_media_accepted_filetypes', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_gzip'])){
	          			update_option('nou_leopard_offload_media_gzip', $_POST['nou_leopard_offload_media_gzip']);
	          		}else{
	          			update_option('nou_leopard_offload_media_gzip', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_cache_control'])){
	          			if(isset($_POST['nou_leopard_wom_update_cache_control']) && $_POST['nou_leopard_wom_update_cache_control'] == '1'){
		          			$this->update_cache_control();
		          		}
	          		}
	          		
	          	}

          		if(isset($_POST['nou_leopard_offload_media_time_valid_number'])){
          			update_option('nou_leopard_offload_media_time_valid_number', $_POST['nou_leopard_offload_media_time_valid_number']);
          		}

          		if(isset($_POST['nou_leopard_offload_media_order_link_checkbox'])){
          			update_option('nou_leopard_offload_media_order_link_checkbox', $_POST['nou_leopard_offload_media_order_link_checkbox']);
          		}

          		if(isset($_POST['nou_leopard_offload_media_textarea_email_link'])){
          			update_option('nou_leopard_offload_media_textarea_email_link', $_POST['nou_leopard_offload_media_textarea_email_link']);
          		}

          		if(isset($_POST['nou_leopard_offload_media_aws_path'])){
          			update_option('nou_leopard_offload_media_aws_path', $_POST['nou_leopard_offload_media_aws_path']);
          		}

          		if(isset($_POST['nou_leopard_offload_media_url_tab'])){
	          		if(isset($_POST['nou_leopard_offload_media_rewrite_urls_checkbox'])){
	          			update_option('nou_leopard_offload_media_rewrite_urls_checkbox', $_POST['nou_leopard_offload_media_rewrite_urls_checkbox']);
	          		}else{
	          			update_option('nou_leopard_offload_media_rewrite_urls_checkbox', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_cname'])){
	          			update_option('nou_leopard_offload_media_cname', $_POST['nou_leopard_offload_media_cname']);
	          		}else{
	          			update_option('nou_leopard_offload_media_cname', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_force_https_checkbox'])){
	          			update_option('nou_leopard_offload_media_force_https_checkbox', $_POST['nou_leopard_offload_media_force_https_checkbox']);
	          		}else{
	          			update_option('nou_leopard_offload_media_force_https_checkbox', '');
	          		}
	          	}

          		if(isset($_POST['nou_leopard_offload_media_url_tab_assets'])){
	          		
	          		if(isset($_POST['nou_leopard_offload_media_assets_rewrite_urls_checkbox'])){
	          			update_option('nou_leopard_offload_media_assets_rewrite_urls_checkbox', $_POST['nou_leopard_offload_media_assets_rewrite_urls_checkbox']);
	          		}else{
	          			update_option('nou_leopard_offload_media_assets_rewrite_urls_checkbox', '');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_pull_assets_path'])){
	          			update_option('nou_leopard_offload_media_pull_assets_path', $_POST['nou_leopard_offload_media_pull_assets_path']);
	          		}else{
	          			update_option('nou_leopard_offload_media_pull_assets_path', 'pull-assets/');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_minify_css'])){
	          			update_option('nou_leopard_offload_media_minify_css', $_POST['nou_leopard_offload_media_minify_css']);
	          		}else{
	          			update_option('nou_leopard_offload_media_minify_css', '');
	          		}
	          		
	          		if(isset($_POST['nou_leopard_offload_media_minify_js'])){
	          			update_option('nou_leopard_offload_media_minify_js', $_POST['nou_leopard_offload_media_minify_js']);
	          		}else{
	          			update_option('nou_leopard_offload_media_minify_js', '');
	          		}
	          	}

          		if(isset($_POST['nou_leopard_offload_media_cors_tab'])){
	          		
	          		if(isset($_POST['nou_leopard_offload_media_cors_origin'])){
	          			update_option('nou_leopard_offload_media_cors_origin', $_POST['nou_leopard_offload_media_cors_origin']);
	          		}else{
	          			update_option('nou_leopard_offload_media_cors_origin', '*');
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_cors_allow_methods'])){
	          			update_option('nou_leopard_offload_media_cors_allow_methods', $_POST['nou_leopard_offload_media_cors_allow_methods']);
	          		}else{
	          			update_option('nou_leopard_offload_media_cors_allow_methods', array('GET', 'HEAD', 'OPTIONS'));
	          		}

	          		if(isset($_POST['nou_leopard_offload_media_cors_maxageseconds'])){
	          			update_option('nou_leopard_offload_media_cors_maxageseconds', $_POST['nou_leopard_offload_media_cors_maxageseconds']);
	          		}else{
	          			update_option('nou_leopard_offload_media_cors_maxageseconds', '3600');
	          		}

	          		// Update CORS
	          		$this->putBucketCors();
	          	}

          		if(isset($_POST['nou_leopard_offload_media_advanced_tab'])){
	          		
	          		if(isset($_POST['nou_leopard_offload_media_emoji'])){
	          			update_option('nou_leopard_offload_media_emoji', $_POST['nou_leopard_offload_media_emoji']);
	          		}else{
	          			update_option('nou_leopard_offload_media_emoji', '');
	          		}
	          		
	          		if(isset($_POST['nou_leopard_offload_media_minify_html'])){
	          			update_option('nou_leopard_offload_media_minify_html', $_POST['nou_leopard_offload_media_minify_html']);
	          		}else{
	          			update_option('nou_leopard_offload_media_minify_html', '');
	          		}
	          		
	          		if(isset($_POST['nou_leopard_offload_media_webp'])){
	          			update_option('nou_leopard_offload_media_webp', $_POST['nou_leopard_offload_media_webp']);
	          		}else{
	          			update_option('nou_leopard_offload_media_webp', '');
	          		}
	          	}
          		
          	}
     	}
	}

	public function putBucketCors(){
		require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-cors.php' );
		$cors = new Leopard_Wordpress_Offload_Media_Cors();
		$cors->putBucketCors();
	}

	public function checking_credentials(){
		$aws_s3_client = leopard_offload_media_provider();
		$buckets = $aws_s3_client->Checking_Credentials();

		wp_redirect(admin_url('admin.php?page=leopard_offload_media&tab=generalsettings'));
		exit;
	}

	public function update_cache_control(){
		$Bucket_Selected = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
		$aws_s3_client = leopard_offload_media_provider();
		$region = 'none';
		
		if($aws_s3_client::identifier() == 'google'){
			$bucket = $Bucket_Selected;
		}else{

			$Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_Selected );

	        if ( count( $Array_Bucket_Selected ) == 2 ){
	            $bucket = $Array_Bucket_Selected[0];
	            $region = $Array_Bucket_Selected[1];
	        }
	        else{
	            $bucket = 'none';
	        }

	    }
	    
		//$aws_s3_client->update_cache_control_objects($bucket, $region);
	}

	public function bucket_base_url(){
		leopard_wordpress_offload_media_bucket_base_url();
	}

	/**
	 * Copying files to S3 and removing from server
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function wp_update_attachment_metadata( $data, $post_id ) {
		if(nou_leopard_offload_media_is_plugin_setup()){
			$copy_file_s3_checkbox = get_option('nou_leopard_offload_media_copy_file_s3_checkbox', '');
			if ( $copy_file_s3_checkbox ) {

				$pre = apply_filters( 'leopard_offload_media_pre_upload_attachment', true, $post_id, $data );
				if(!$pre){
					return $data;
				}

				$radio_private_or_public = get_option('nou_leopard_offload_media_private_public_radio_button', 'public');
				leopard_offload_media_copy_to_s3_function( $post_id, $radio_private_or_public, $data );
				
				leopard_offload_media_build_webp_function($post_id);
			}
		}
		return $data;
	}


	/**
	 * Removes an attachment and intermediate image size files from provider
	 *
	 * @param int  $post_id
	 * @param bool $force_new_provider_client if we are deleting in bulk, force new provider client
	 *                                        to cope with possible different regions
	 */
	public function delete_attachment( $post_id, $force_new_provider_client = false ) {
		if ( ! nou_leopard_offload_media_is_plugin_setup( true ) ) {
			return;
		}

		if ( ! ( $provider_object = leopard_offload_media_get_attachment_provider_info( $post_id ) ) ) {
			return;
		}

		leopard_offload_media_remove_from_s3_function($post_id);

		delete_post_meta( $post_id, '_nou_leopard_wom_amazonS3_info' );
		delete_post_meta( $post_id, '_nou_leopard_wom_webp_info' );
	}

	/**
	 * Show individual options
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function media_row_actions_extra( $actions, $post ) { // 3º parameter $this->detached

		if ( nou_leopard_offload_media_is_plugin_setup() ) {

			$post_id = get_the_ID( $post );

			return leopard_wordpress_offload_media_row_actions_extra($actions, $post_id);

		}

		return $actions;

	}

	/**
	 * Show bulk actions
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function bulk_actions_extra_options( $actions ) {

		if ( nou_leopard_offload_media_is_plugin_setup() ) {

			$actions['nou_leopard_wom_copy_to_s3']             = leopard_offload_media_text_actions('nou_leopard_wom_copy_to_s3');
			$actions['nou_leopard_wom_remove_from_s3']         = leopard_offload_media_text_actions('nou_leopard_wom_remove_from_s3');
			$actions['nou_leopard_wom_copy_to_server_from_s3'] = leopard_offload_media_text_actions('nou_leopard_wom_copy_to_server_from_s3');
			$actions['nou_leopard_wom_remove_from_server']     = leopard_offload_media_text_actions('nou_leopard_wom_remove_from_server');
			$actions['nou_leopard_wom_build_webp']     			= leopard_offload_media_text_actions('nou_leopard_wom_build_webp');

		}

		return $actions;

	}

	/**
	 * Do bulk actions
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function do_bulk_actions_extra_options( $location, $doaction, $post_ids ) {
		if ( nou_leopard_offload_media_is_plugin_setup() ) {
			nou_leopard_offload_media_do_bulk_actions_extra_options_function( $doaction, $post_ids );
		}

		return $location;

	}


	/**
	 * Copy to S3
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function post_action_copy_to_c3( $post_id ) {

		if ( nou_leopard_offload_media_is_plugin_setup() ) {
			$radio_private_or_public = get_option('nou_leopard_offload_media_private_public_radio_button', 'public');
			leopard_offload_media_copy_to_s3_function( $post_id, $radio_private_or_public );
			leopard_offload_media_build_webp_function( $post_id );

		}

		$sendback = wp_get_referer();

		wp_redirect( $sendback );

		die();

	}


	/**
	 * Remove from S3
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function post_action_remove_from_s3( $post_id ) {

		if ( nou_leopard_offload_media_is_plugin_setup() ) {
			leopard_offload_media_copy_to_server_from_s3_function( $post_id );
			leopard_offload_media_remove_from_s3_function( $post_id );
		}

		$sendback = wp_get_referer();

		wp_redirect( $sendback );

		die();

	}


	/**
	 * Copy to server from S3
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function post_action_copy_to_server_from_c3( $post_id ) {

		if ( nou_leopard_offload_media_is_plugin_setup() ) {

			leopard_offload_media_copy_to_server_from_s3_function( $post_id );

		}

		$sendback = wp_get_referer();

		wp_redirect( $sendback );

		die();

	}

	/**
	 * Remove from server
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function post_action_remove_from_server( $post_id ) {

		if ( nou_leopard_offload_media_is_plugin_setup() ) {

			leopard_offload_media_remove_from_server_function( $post_id );

		}

		$sendback = wp_get_referer();

		wp_redirect( $sendback );

		die();

	}

	/**
	 * Build WebP
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function post_action_build_webp( $post_id ) {

		if ( nou_leopard_offload_media_is_plugin_setup() ) {

			leopard_offload_media_build_webp_function( $post_id );

		}

		$sendback = wp_get_referer();

		wp_redirect( $sendback );

		die();

	}

	/**
	 * Get attachment url
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function wp_get_attachment_url( $url, $post_id ) {
		if ( nou_leopard_offload_media_is_plugin_setup() ) {
			if(nou_leopard_offload_media_enable_rewrite_urls()){
				$s3_path = get_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', true);
				$new_url = false;
				
				if ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) {
					$provider_object = leopard_wordpress_offload_media_get_real_provider($post_id);
					$new_url = $provider_object['base_url'];
				}

				if ( is_wp_error( $new_url ) || false === $new_url ) {
					return $url;
				}

				$new_url = apply_filters( 'leopard_offload_media_get_attachment_url', $new_url, $post_id );

				return leopard_wordpress_offload_media_s3_to_cloudfront_url($new_url);
			}
		}
		return $url;
	}


	/**
	 * Maybe encode URLs for images that represent an attachment
	 *
	 * @param array|bool   $image
	 * @param int          $attachment_id
	 * @param string|array $size
	 * @param bool         $icon
	 *
	 * @return array
	 */
	public function maybe_encode_wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		if ( ! ( $provider_object = leopard_offload_media_is_attachment_served_by_provider( $attachment_id ) ) ) {
			// Not served by provider, return
			return $image;
		}

		if ( isset( $image[0] ) ) {
			$url = leopard_offload_media_maybe_sign_intermediate_size( $image[0], $attachment_id, $size, $provider_object );
			$url = nou_leopard_offload_media_encode_filename_in_path( $url );

			$image[0] = $url;
		}

		return $image;
	}
	

	/**
	 * Maybe encode attachment URLs when retrieving the image tag
	 *
	 * @param string $html
	 * @param int    $id
	 * @param string $alt
	 * @param string $title
	 * @param string $align
	 * @param string $size
	 *
	 * @return string
	 */
	public function maybe_encode_get_image_tag( $html, $id, $alt, $title, $align, $size ) {
		if ( ! ( $provider_object = leopard_offload_media_is_attachment_served_by_provider( $id ) ) ) {
			// Not served by provider, return
			return $html;
		}

		if ( ! is_string( $html ) ) {
			return $html;
		}

		preg_match( '@\ssrc=[\'\"]([^\'\"]*)[\'\"]@', $html, $matches );

		if ( ! isset( $matches[1] ) ) {
			// Can't establish img src
			return $html;
		}

		$img_src     = $matches[1];
		$new_img_src = leopard_offload_media_maybe_sign_intermediate_size( $img_src, $id, $size, $provider_object );
		$new_img_src = nou_leopard_offload_media_encode_filename_in_path( $new_img_src );

		return str_replace( $img_src, $new_img_src, $html );
	}

	/**
	 * Maybe encode URLs when outputting attachments in the media grid
	 *
	 * @param array      $response
	 * @param int|object $attachment
	 * @param array      $meta
	 *
	 * @return array
	 */
	public function maybe_encode_wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		if ( ! ( $provider_object = leopard_offload_media_is_attachment_served_by_provider( $attachment->ID ) ) ) {
			// Not served by provider, return
			return $response;
		}

		if ( isset( $response['url'] ) ) {
			$response['url'] = nou_leopard_offload_media_encode_filename_in_path( $response['url'] );
		}

		$sizes = '';

		if ( isset( $response['sizes'] ) && is_array( $response['sizes'] ) ) {
			$sizes = $response['sizes'];
		}else{
			$meta = get_post_meta($attachment->ID, '_nou_leopard_wom_amazonS3_info', true);
			if ( isset( $meta['data']['sizes'] ) && is_array( $meta['data']['sizes'] ) ) {
				$sizes = $meta['data']['sizes'];
			}
		}
		
		if ($sizes) {
			foreach ( $sizes as $size => $value ) {
				if(isset($value['url'])){
					$url = leopard_offload_media_maybe_sign_intermediate_size( $value['url'], $attachment->ID, $size, $provider_object );
					$url = nou_leopard_offload_media_encode_filename_in_path( $url );

					$response['sizes'][ $size ]['url'] = $url;
				}
			}
		}
		$response['leopard_served'] = 'all';
		$s3_path = get_post_meta( $attachment->ID, '_wp_nou_leopard_wom_s3_path', true );
		if ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) {
			$response['leopard-cloud-class'] = "leopard-served-by-provider";
			$response['leopard_served'] = 1;
		}else{
			$response['leopard-cloud-class'] = "leopard-not-served";
		}

		return $response;
	}

	/**
	 * Maybe encode URLs when retrieving intermediate sizes.
	 *
	 * @param array        $data
	 * @param int          $post_id
	 * @param string|array $size
	 *
	 * @return array
	 */
	public function maybe_encode_image_get_intermediate_size( $data, $post_id, $size ) {
		if ( ! ( $provider_object = leopard_offload_media_is_attachment_served_by_provider( $post_id ) ) ) {
			// Not served by provider, return
			return $data;
		}

		if ( isset( $data['url'] ) ) {
			$url = leopard_offload_media_maybe_sign_intermediate_size( $data['url'], $post_id, $size, $provider_object );
			$url = nou_leopard_offload_media_encode_filename_in_path( $url );

			$data['url'] = $url;
		}

		return $data;
	}

	/**
	 * Return the provider URL when the local file is missing
	 * unless we know the calling process is and we are happy
	 * to copy the file back to the server to be used
	 *
	 * @param string $file
	 * @param int    $attachment_id
	 *
	 * @return string
	 */
	function get_attached_file( $file, $attachment_id ) {
		if($attachment_id > 0){
			$post_type = get_post_type( $attachment_id );
			if ( nou_leopard_offload_media_is_plugin_setup() && $post_type == 'attachment' ) {
				if ( file_exists( $file ) || ! ( $provider_object = leopard_offload_media_is_attachment_served_by_provider( $attachment_id ) ) ) {
					return $file;
				}

				$s3_path = get_post_meta( $attachment_id, '_wp_nou_leopard_wom_s3_path', true);
				if ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) {
					$provider_object = leopard_wordpress_offload_media_get_real_provider($attachment_id);
					$url = $provider_object['base_url'];
					return apply_filters( 'leopard_offload_media_get_attached_file', $url, $file, $attachment_id, $provider_object );
				}
			}
		}
		return $file;
	}

	/**
	 * Allow processes to update the file on provider via update_attached_file()
	 *
	 * @param string $file
	 * @param int    $attachment_id
	 *
	 * @return string
	 */
	function update_attached_file( $file, $attachment_id ) {
		if ( !nou_leopard_offload_media_is_plugin_setup() ) {
			return $file;
		}

		if ( ! ( $provider_object = leopard_offload_media_get_attachment_provider_info( $attachment_id ) ) ) {
			return $file;
		}

		$file = apply_filters( 'leopard_offload_media_update_attached_file', $file, $attachment_id, $provider_object );

		return $file;
	}

	public function attachment_provider_meta_box(){
		if(nou_leopard_offload_media_is_plugin_setup()){
			add_meta_box(
				'nou-s3-actions',
				esc_html__( 'Leopard - Offload media', 'leopard-wordpress-offload-media' ),
				array( $this, 'attachment_provider_actions_meta_box' ),
				'attachment',
				'side',
				'core'
			);
		}	
	}

	/**
	 * Render the S3 attachment meta box
	 */
	public function attachment_provider_actions_meta_box() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/leopard-wordpress-offload-media-admin-metabox.php';
	}

}
