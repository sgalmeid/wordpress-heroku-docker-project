<?php

/**
 * Shortcodes
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.5
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */
class Leopard_Wordpress_Offload_Media_Shortcodes {

	function __construct() {
		self::init();	
	}

	public static function init() {

		$shortcodes = array(
			'leopard_wordpress_offload_media_storage' => __CLASS__ . '::Show_Presigned_URL'
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, $function );
		}

		shortcode_atts( array( 'key' => '', 'name' => '' ), array(), 'leopard_wordpress_offload_media_storage' );

	}

	public static function Show_Presigned_URL( $atts ) {

		$key  = isset( $atts['key'] ) ? $atts['key'] : '';
		$Name = isset( $atts['name'] ) ? $atts['name'] : '';
		$download_url = '';

		list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();
		$url = $aws_s3_client->Get_Presigned_URL($Bucket, $Region, $key);
		if ( $url ) {
			$download_url = $url;
		}

		return $download_url;

	}
	
}
