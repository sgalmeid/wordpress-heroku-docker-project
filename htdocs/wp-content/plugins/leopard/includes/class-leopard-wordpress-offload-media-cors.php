<?php
if (!defined('ABSPATH')) {exit;}
/**
 * Cross Origin Resource Sharing (CORS)
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.4
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */

class Leopard_Wordpress_Offload_Media_Cors {

	/**
     * @var string
     */

    protected $client = null;
    protected $region = null;
    protected $bucket = null;
    protected $allow_methods = null;
    protected $origin = null;
    protected $maxageseconds = null;


    public function __construct() {

        $Bucket_Selected = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
		
		$this->allow_methods = get_option('nou_leopard_offload_media_cors_allow_methods', array('GET', 'HEAD', 'OPTIONS'));
		$this->maxageseconds = get_option('nou_leopard_offload_media_cors_maxageseconds', '3600');

		$origin = get_option('nou_leopard_offload_media_cors_origin', '*');
		if(strpos($origin, ',') !== false){
			$this->origin = explode(',', $origin);
		}else{
			$this->origin = array($origin);
		}

		$this->client = leopard_offload_media_provider();

		if($this->client::identifier() == 'google'){
			$this->bucket = $Bucket_Selected;
		}else{

			$Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_Selected );

	        if ( count( $Array_Bucket_Selected ) == 2 ){
	            $this->bucket = $Array_Bucket_Selected[0];
	            $this->region = $Array_Bucket_Selected[1];
	        }
	        else{
	            $this->bucket = 'none';
	            $this->region = 'none';
	        }

	    }

    }

    public function putBucketCors() {
    	return $this->client->putBucketCors( $this->bucket, $this->region, $this->origin, $this->allow_methods, $this->maxageseconds );
    }
}
