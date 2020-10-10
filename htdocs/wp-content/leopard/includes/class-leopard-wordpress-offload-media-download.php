<?php

/**
 * Downloads Integration
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.2
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */

class Leopard_Wordpress_Offload_Media_Download {

	function __construct() {
		$this->compatibility_init();
	}

	public function compatibility_init() {}

	public function get_path_from_url($url){
		$domain = parse_url($url);
		$url = isset($domain['path']) ? $domain['path'] : '';
		if(!empty($url)){
			return ltrim($url, '/');
		}
		return false;
	}

	public function get_key_from_url($old_url){
		return Leopard_Wordpress_Offload_Media_Utils::get_key_from_url($old_url);
	}

	public function get_post_id($value){
		$url = $this->get_key_from_url($value);
		if($url){
			return leopard_wordpress_offload_media_get_post_id($url);
		}
		return false;
	}
}
