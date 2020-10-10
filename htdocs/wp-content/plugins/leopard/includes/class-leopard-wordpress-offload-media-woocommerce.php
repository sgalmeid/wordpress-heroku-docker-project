<?php

/**
 * Woocommerce Downloads Integration
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.2
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */

class Leopard_Wordpress_Offload_Media_Woocommerce extends Leopard_Wordpress_Offload_Media_Download {

	/**
	 * Register the compatibility hooks for the plugin.
	 */
	function compatibility_init() {
		add_action( 'woocommerce_process_product_meta', array( $this, 'woocommerce_save_download' ), 40, 2 );
		add_filter( 'woocommerce_file_download_path', array( $this, 'filter_download_files' ) );
	}

	public function woocommerce_save_download($post_id, $post){
		$files = get_post_meta($post_id, '_downloadable_files', true);
		if(!empty($files)){
			foreach ($files as $key => $file) {
				$attachment_id = $this->get_post_id($file['file']);
				if($attachment_id){
					$is_permission = get_post_meta($attachment_id, 'leopard_downloadable_file_permission', true);
					if($is_permission != 'yes'){
						list( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute ) = leopard_offload_media_aws_array_media_actions_function( $attachment_id );
						$aws_s3_client->set_object_permission( $Bucket, $Region, $array_files, 'private' );
						update_post_meta($attachment_id, 'leopard_downloadable_file_permission', 'yes');
					}	
				}	
			}
		}
	}

	public function filter_download_files($value){

		if ( empty( $value ) || is_admin() ) {
			return $value;
		}

		if (strpos($value, 'leopard_wordpress_offload_media_storage') !== false) {
			return do_shortcode($value);
		}

		$attachment_id = $this->get_post_id($value);
		if($attachment_id){
			$key = get_post_meta($attachment_id, '_wp_attached_file', true);
			if($key){
				list( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute ) = leopard_offload_media_aws_array_media_actions_function( $attachment_id );
				$url = $aws_s3_client->Get_Presigned_URL($Bucket, $Region, $key);
				if ( $url ) {
					$value = $url;
				}
			}	
		}

		return $value;
	}
}
