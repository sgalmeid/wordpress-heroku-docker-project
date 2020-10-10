<?php
if (!defined('ABSPATH')) {exit;}
/**
 * Sync Data
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.8
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */

class Leopard_Wordpress_Offload_Media_Sync {

	/**
     * @var string
     */

    protected $provider = null;
    protected $client = null;
    protected $region = null;
    protected $bucket = null;
    protected $objects = null;
    protected $bucket_url = null;

    protected $provider_backup = null;
    protected $client_backup = null;
    protected $region_backup = null;
    protected $bucket_backup = null;
    protected $objects_backup = null;
    protected $bucket_url_backup = null;


    public function __construct() {

        $provider  = get_option('nou_leopard_offload_media_sync_provider_from');
        $settings = get_option('nou_leopard_offload_media_sync_settings_from');
        $Bucket_Selected = get_option('nou_leopard_offload_media_sync_bucket_from');
		
        $this->provider = leopard_offload_media_provider($provider, $settings);

		if($this->provider::identifier() == 'google'){
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
        $this->bucket_url = get_option('nou_leopard_offload_media_sync_bucket_base_url_from');
        $this->client = $this->provider->setClient($this->region);

    }

    public function setBackupData(){
        $type = get_option('nou_leopard_offload_media_sync_type');
        $Bucket_bk_Selected = get_option('nou_leopard_offload_media_sync_bucket_to');

        if($type == 'cloud'){
            $provider  = get_option('nou_leopard_offload_media_sync_provider_to');
            $settings = get_option('nou_leopard_offload_media_sync_settings_to');
    		$this->provider_backup = leopard_offload_media_provider($provider, $settings);
        }else{
            $this->provider_backup = $this->provider;
        }

        if($this->provider_backup::identifier() == 'google'){
            $this->bucket_backup = $Bucket_bk_Selected;
        }else{

            $Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_bk_Selected );

            if ( count( $Array_Bucket_Selected ) == 2 ){
                $this->bucket_backup = $Array_Bucket_Selected[0];
                $this->region_backup = $Array_Bucket_Selected[1];
            }
            else{
                $this->bucket_backup = 'none';
                $this->region_backup = 'none';
            }

        }
        $this->client_backup = $this->provider_backup->setClient($this->region_backup);

        if($type == 'cloud'){
            $this->bucket_url_backup = get_option('nou_leopard_offload_media_sync_bucket_base_url_to');
        }else{
            $this->bucket_url_backup = str_replace($this->bucket, $this->bucket_backup, $this->bucket_url);
        }
    }

    public function setObjects(){
    	$this->objects = $this->provider->get_all_objects($this->bucket, $this->region);
    }

    public function setObjectsBackup(){
    	$this->objects_backup = $this->provider_backup->get_all_objects($this->bucket_backup, $this->region_backup);
    }

    public function getObjects(){
    	return $this->objects;
    }

    public function getObjectsBackup(){
    	return $this->objects_backup;
    }

    private function get_post_id($key){
        return leopard_wordpress_offload_media_get_post_id($key);
    }

    private function maybe_update_post_meta($key){
        $post_id = $this->get_post_id($key);
        if($post_id){

            $info = get_post_meta( $post_id, '_nou_leopard_wom_amazonS3_info', true );
            $info['provider'] = $this->provider_backup::identifier();
            $info['region'] = $this->region_backup;
            $info['bucket'] = $this->bucket_backup;
            update_post_meta( $post_id, '_nou_leopard_wom_amazonS3_info', $info );

            $path = get_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', true );
            $new_path = str_replace($this->bucket_url, $this->bucket_url_backup, $path);
            update_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', $new_path );
        }
        return true;
    }

    private function maybe_update_assets($key){

        $type = substr(strrchr($key, '.'), 1);
        if(!in_array($type, ['css', 'js'])){
            return false;
        }

        $uploaded = get_option('nou_leopard_offload_media_uploaded_assets');
        if(!empty($uploaded) && is_array($uploaded)){
            foreach ($uploaded as $k => $src) {
                if(strpos($src, $key) !== false){
                    $new_src = str_replace($this->bucket_url, $this->bucket_url_backup, $src);
                    $uploaded[$k] = leopard_wordpress_offload_media_s3_to_cloudfront_url($new_src, $this->bucket_url_backup);
                }
            }
            update_option('nou_leopard_offload_media_uploaded_assets', $uploaded);
        }
        return true;
    }

    private function maybe_update_content_css($path, $content){
        $type = substr(strrchr($path, '.'), 1);
        if($type == 'css'){
            return str_replace($this->bucket_url, $this->bucket_url_backup, $content);
        }

        return $content;
    }

    private function maybe_update_permission($data){
        $key = $data['key'];
        $acl = $this->provider->Get_Access_of_Object($this->bucket, $this->region, $key);
        $data['acl'] = $acl;
        $array_aux = explode( '/', $key );
        $main_file = array_pop( $array_aux );
        $array_files[] = implode( "/", $array_aux );
        $array_files[] = $main_file;

        $this->provider_backup->set_object_permission($this->bucket_backup, $this->region_backup, $array_files, $acl);

        if($this->provider_backup::identifier() == 'google'){
            return [];
        }

        return $data;
    }

    public function sync($data){
        try{
            $old_content = $this->getFileContentBackup($data);
            if($old_content){
                $this->putFileContent($this->provider_backup->dir.$this->bucket_backup.'/'.$data['key'], $old_content);

                // $this->maybe_update_post_meta($data['key']);
                // $this->maybe_update_assets($data['key']);
                $new_data = $this->maybe_update_permission($data);
                $this->provider_backup->updateMetadaObject($this->bucket_backup, $this->region_backup, $new_data);

                return true;
            }
            return false;
        } catch (Exception $e){
            return false;
        }
    }

    private function getFileContentBackup($data){
        $source = $data['source'];
        $SourceFile = $this->provider->dir.$source;
        $type = substr(strrchr($data['key'], '.'), 1);

        $this->client->registerStreamWrapper();
        if(file_exists($SourceFile) && is_readable($SourceFile)){
            try{
                if ( $this->provider->should_gzip_file( $SourceFile, $type ) && false !== ( $gzip_body = gzencode( file_get_contents( $SourceFile ) ) ) ) {
                    return $gzip_body;
                }
                
                return file_get_contents($SourceFile);
            } catch (Exception $e){
                error_log($SourceFile);
            }
        }
        return false;
    }

    private function putFileContent($path, $content){
        $new_content = $this->maybe_update_content_css($path, $content);
        return $this->provider_backup->putFileContent($this->bucket_backup, $this->region_backup, $path, $new_content);
    }

    private function syncBucket($data){
        try{
            $data['bucket'] = $this->bucket;
            $this->provider_backup->copyObjectFromBucket($this->bucket_backup, $this->region_backup, $data);
            return true;
        } catch (Exception $e){
            return false;
        }
    }
}
