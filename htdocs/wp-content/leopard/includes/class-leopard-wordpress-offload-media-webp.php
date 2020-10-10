<?php

/**
 * Webp generate
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.2
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */
use WebPConvert\WebPConvert;

class Leopard_Wordpress_Offload_Media_Webp {

	protected $post_id = null;
	protected $provider = null;
    protected $client = null;
    protected $region = null;
    protected $bucket = null;
    protected $bucket_url = null;
    protected $base_folder = null;
    protected $basedir_absolute = null;
    protected $objects = [];

	function __construct($post_id) {

		$this->post_id = $post_id;

		list( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute ) = leopard_offload_media_aws_array_media_actions_function( $post_id );

		$Bucket_Selected = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
		$this->provider = $aws_s3_client;
		$this->bucket = $Bucket;
	    $this->region = $Region;
        $this->bucket_url = leopard_offload_media_get_bucket_url();
        $this->client = $this->provider->setClient($this->region);
        $this->basedir_absolute = $basedir_absolute;
        $this->base_folder = array_shift( $array_files );

        foreach ( $array_files as $key ) {
			if ( $this->base_folder != '' ) {
                $new_key = $this->base_folder . "/" . $key;
            } else {
                $new_key = $key;
            }
			$this->objects[] = $new_key;
		}
	}

	public function get_key_from_url($old_url){
		return Leopard_Wordpress_Offload_Media_Utils::get_key_from_url($old_url);
	}

	private function should_convert($source){
		if(strpos($source, '.png') !== false || strpos($source, '.jpg') !== false || strpos($source, '.jpeg') !== false){
			return true;
		}
		return false;
	}

	private function build_key($key, $with_bucket=true){
		$key = $this->provider->getBucketMainFolder().$key;
		if(!$with_bucket){
			return $key;
		}
		return $this->bucket.'/'.$key;
	}

    private function update_permission($key){
        $array_aux = explode( '/', $key );
        $main_file = array_pop( $array_aux );
        $array_files[] = implode( "/", $array_aux );
        $array_files[] = $main_file;
        $data = $this->provider->set_object_permission($this->bucket, $this->region, $array_files);
    }

	private function convert($key, $options=[]){
		$data = get_post_meta($this->post_id, '_nou_leopard_wom_webp_info', true);
		$upload_dir = wp_upload_dir();
		$basedir_absolute = $upload_dir['basedir'];
		$success = false;
        $msg = '';
        $new_key = $this->build_key($key);
        $source = $this->provider->dir.$new_key;
        if($this->should_convert($key)){
        	$destination = $this->basedir_absolute. '/' . $key . '.webp';
        	if(file_exists($source) && is_readable($source)){
        		$content = file_get_contents($source);
        		$file = $key . '.webp';
        		$temp = wp_tempnam( $file );
				if ( ! $temphandle = @fopen( $temp, 'w+' ) ) {
					@unlink( $temp );
				}else{
					Leopard_Wordpress_Offload_Media_Utils::put_contents($temp, $content);
		        	try {
						WebPConvert::convert($temp, $destination, $options);
						if(file_exists( $destination )){

							// Here, upload file to cloud
							$path = $source . '.webp';
							$new_content = file_get_contents( $destination );
							$this->provider->putFileContent($this->bucket, $this->region, $path, $new_content);
							$this->update_permission($this->build_key($key, false) . '.webp');
							// then remove file from local
							@unlink( $destination );
							$success = true;

						}
					} catch (Exception $e) {
						$msg = $e->getMessage();
					}
				}
			}
        }

        if($success){
        	// save data to DB
        	$url = $this->bucket_url . '/' . $this->build_key($key, false) . '.webp';
        	if(!empty($data) && is_array($data)){
        		$data[] = $url;
        	}else{
        		$data = [$url];
        	}
			update_post_meta($this->post_id, '_nou_leopard_wom_webp_info', array_unique($data));
        }

		return [
            'success' 	=> $success,
            'msg' 		=> $msg,
            'file' 		=> $destination
        ];
	}

	public function do_converts(){
		try {
			$this->client->registerStreamWrapper();
			foreach ($this->objects as $key) {
				$this->convert($key);
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
}
