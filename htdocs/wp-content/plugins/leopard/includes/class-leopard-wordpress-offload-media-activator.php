<?php

/**
 * Fired during plugin activation
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */
class Leopard_Wordpress_Offload_Media_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_options();
		self::create_files();
	}

	/**
	 * Create options
	 *
	 * @since    1.0.0
	 */
	public static function create_options() {
		$default = get_option('nou_leopard_offload_media');
		if(empty($default)){
			$options = array(
				'provider' => 'aws',
				'access_key' => '',
				'secret_access_key' => '',
				'credentials' => ''
				);
			update_option('nou_leopard_offload_media', $options);
			update_option('nou_leopard_offload_media_rewrite_urls_checkbox', 'on');
			update_option('nou_leopard_offload_media_copy_file_s3_checkbox', 'on');
			update_option('nou_leopard_offload_media_private_public_radio_button', 'public');
		}

		$accepted_filetypes = get_option('nou_leopard_offload_media_accepted_filetypes');
		if(empty($accepted_filetypes)){
			update_option('nou_leopard_offload_media_accepted_filetypes', '');
		}
	}

	public static function create_files(){
		$upload_dir = wp_upload_dir();

		$files = array(
			array(
				'base'    => $upload_dir['basedir'] . '/leopard-wordpress-offload',
				'file'    => '.htaccess',
				'content' => 'deny from all',
			)
		);

		nou_leopard_offload_media_create_files($files);
	}
}
