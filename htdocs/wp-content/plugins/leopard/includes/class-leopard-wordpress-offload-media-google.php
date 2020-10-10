<?php
/**
 * Google S3 Client
 *
 * @since      1.0.3
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Timestamp;

class Leopard_Wordpress_Offload_Media_Google extends Leopard_Wordpress_Offload_Media_Storage {

    const ACL_PUBLIC_READ = 'publicRead';
    const ACL_PRIVATE_READ = 'authenticatedRead';

    public $dir = 'gs://';

    public static function identifier() {
        return 'google';
    }

    public static function name() {
        return esc_html__('Google Cloud Storage', 'leopard-wordpress-offload-media');
    }

    public static function bucketLink($bucket) {
        return "https://console.cloud.google.com/storage/browser/$bucket";
    }

    public function Load_Regions() {

        $this->_array_regions = array(
            '0'  => array( 'MULTI_REGIONAL', 'Multi-Regional' ),
        );
    }

    public function handler_response($response){
        $response = json_decode($response, true);
        return isset($response['error']) ? $response['error'] : ['code' => '400', 'message' => esc_html__('Error, please try again.', 'leopard-wordpress-offload-media')];
    }

    public function Init_S3_Client( $Region, $Version, $credentials, $Secret ) {
        try {
            if (!empty($credentials) && is_array($credentials)) {
                $storage = new StorageClient([
                    'projectId' => $credentials['project_id'],
                    'keyFile' => $credentials
                ]);
                return $storage;
            }
            
        } catch ( Exception $e ) {
            update_option( 'nou_leopard_offload_media_connection_success', 0 );
            wp_die(esc_html__('Could not create Google storage client.', 'leopard-wordpress-offload-media'));
        }

        return false;
    }

    public function Checking_Credentials() {
        $S3_Client = $this->Init_S3_Client( $this->_array_regions[0][0], $this->_version, $this->_key, $this->_secret );
        if($S3_Client){
            update_option( 'nou_leopard_offload_media_connection_success', 1 );
        }else{
            update_option( 'nou_leopard_offload_media_connection_success', 0 );
        }
    }

    public static function docs_link_credentials(){
        return 'https://cloud.google.com/storage/docs/reference/libraries#setting_up_authentication';
    }

    public static function docs_link_create_bucket(){
        return 'https://cloud.google.com/storage/docs/creating-buckets';
    }

    public function get_base_url($bucket, $Region, $Keyname){
        return "https://$bucket.storage.googleapis.com";
    }

    public function Upload_Media_File( $Bucket, $Region, $array_files, $basedir_absolute, $private_or_public = 'public', $prefix='' ) {

        $base_folder = array_shift( $array_files );

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $File_Name = array_shift( $array_files );

        if ( $base_folder != '' ) {
            $Key = $base_folder . "/" . $File_Name;
        } else {
            $Key = $File_Name;
        }

        if ( $base_folder != '' ) {
            $SourceFile = $basedir_absolute . "/" . $base_folder . "/" . $File_Name;
        } else {
            $SourceFile = $basedir_absolute . "/" . $File_Name;
        }

        /*== We check if the file is going to be private or public ==*/
        $private_or_public = ( $private_or_public == 'private' ? 'authenticatedRead' : 'publicRead' );

        $cacheControl_settings = get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000');
        if($cacheControl_settings) {
            $CacheControl = $cacheControl_settings;
        }else{
            $CacheControl = false;
        }

        $result = ''; 
        $bucket = $S3_Client->bucket($Bucket);
        try {
            if(file_exists($SourceFile)){
                $args = [
                    'name' => $this->rebuild_key($Key, $prefix),
                    'predefinedAcl' => $private_or_public,
                    'metadata'=> [
                        'cacheControl' => $CacheControl
                    ]
                ];

                $body = fopen($SourceFile, 'r');
                $type = substr(strrchr($Key, '.'), 1);
                if ( $this->should_gzip_file( $SourceFile, $type ) && false !== ( $gzip_body = gzencode( file_get_contents( $SourceFile ) ) ) ) {
                    
                    $body = $gzip_body;
                    $args['metadata']['contentEncoding'] = 'gzip';

                    $mime_types = $this->get_mime_types_to_gzip( true );
                    $mimes = array_keys($mime_types);
                    if(in_array($type, $mimes)){
                        $args['metadata']['contentType'] = $mime_types[$type];
                    }
                }

                $object = $bucket->upload($body, $args);
                $result = $this->get_base_url($Bucket, '', '').'/'.$this->rebuild_key($Key, $prefix);
            }
        } catch (\Exception $ex) {
            error_log( $ex->getMessage() );
        }

        foreach ( $array_files as $File_Name ) {

            if ( $base_folder != '' ) {
                $Key = $base_folder . "/" . $File_Name;
            } else {
                $Key = $File_Name;
            }

            if ( $base_folder != '' ) {
                $SourceFile = $basedir_absolute . "/" . $base_folder . "/" . $File_Name;
            } else {
                $SourceFile = $basedir_absolute . "/" . $File_Name;
            }

            try {
                if(file_exists($SourceFile)){
                    $args = [
                        'name' => $this->rebuild_key($Key),
                        'predefinedAcl' => $private_or_public,
                        'metadata'=> [
                            'cacheControl' => false
                        ]
                    ];

                    $body = fopen($SourceFile, 'r');
                    $type = substr(strrchr($Key, '.'), 1);
                    if ( $this->should_gzip_file( $SourceFile, $type ) && false !== ( $gzip_body = gzencode( file_get_contents( $SourceFile ) ) ) ) {
                        
                        $body = $gzip_body;
                        $args['metadata']['contentEncoding'] = 'gzip';

                        $mime_types = $this->get_mime_types_to_gzip( true );
                        $mimes = array_keys($mime_types);
                        if(in_array($type, $mimes)){
                            $args['metadata']['contentType'] = $mime_types[$type];
                        }
                    }
                    
                    $object = $bucket->upload($body, $args);
                }    
            } catch (\Exception $ex) {
                error_log( $ex->getMessage() );
            }
        }

        return $result;

    }

    /**
     * obtiene todos los objetos de un bucket
     * @return \Guzzle\Service\Resource\ResourceIteratorInterface|mixed
     */
    public function Show_Buckets($Bucket_Selected='') {

        ob_start();

        if(empty($Bucket_Selected)){
            $Bucket_Selected = ( get_option( 'nou_leopard_offload_media_connection_bucket_selected_select' ) ? get_option( 'nou_leopard_offload_media_connection_bucket_selected_select' ) : '' );
        }

        try {

            // Instantiate the S3 client with your AWS credentials
            $S3_Client = $this->Init_S3_Client( $this->_array_regions[0][0], $this->_version, $this->_key, $this->_secret );

            $buckets = $S3_Client->buckets();
            $regions = $this->Get_Regions();
            
            echo "<option value='0'>" . esc_html__( 'Choose a bucket', 'leopard-wordpress-offload-media' ) . "</option>";

            foreach ( $buckets as $bucket ) {
                ?>
                <option <?php selected($bucket->name(), $Bucket_Selected); ?> value="<?php echo esc_attr($bucket->name());?>"><?php echo esc_html($bucket->name()); ?></option>
                <?php
            }

        } catch ( Exception $e ) {

            //
        }

        return ob_get_clean();

    }

    public function getObjectUrl( $Bucket, $Region, $File_Name ) {
        return "https://$Bucket.storage.googleapis.com/$File_Name";
    }

    /**
     * download files
     *
     * @param $key
     * @param $filename
     */
    public function download_file( $Bucket, $Region, $array_files, $basedir_absolute ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $base_folder = array_shift( $array_files );

        foreach ( $array_files as $File_Name ) {

            if ( $base_folder != '' ) {
                $Key = $base_folder . "/" . $File_Name;
            } else {
                $Key = $File_Name;
            }

            if ( $base_folder != '' ) {
                $SaveAs = $basedir_absolute . "/" . $base_folder . "/" . $File_Name;
            } else {
                $SaveAs = $basedir_absolute . "/" . $File_Name;
            }

            try{
                $dir = dirname( $SaveAs );
                if ( ! wp_mkdir_p( $dir ) ) {
                    $error_message = sprintf( __( 'The local directory %s does not exist and could not be created.', 'leopard-wordpress-offload-media' ), $dir );
                    error_log( sprintf( __( 'There was an error attempting to download the file %s from the bucket: %s', 'leopard-wordpress-offload-media' ), $File_Name, $error_message ) );

                    return false;
                }
                $bucket = $S3_Client->bucket($Bucket);
                $object = $bucket->object($this->rebuild_key($Key));
                $result = $object->downloadToFile($SaveAs);
            } catch (Exception $e){
                error_log($e->getMessage());
            }
        }

        return $result;

    }

    /**
     * download original file
     *
     * @param $key
     * @param $filename
     */
    public function download_original_file( $Bucket, $Region, $array_files, $basedir_absolute ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $base_folder = array_shift( $array_files );
        $File_Name = $array_files[0];

        if ( $base_folder != '' ) {
            $Key = $base_folder . "/" . $File_Name;
        } else {
            $Key = $File_Name;
        }

        if ( $base_folder != '' ) {
            $SaveAs = $basedir_absolute . "/" . $base_folder . "/" . $File_Name;
        } else {
            $SaveAs = $basedir_absolute . "/" . $File_Name;
        }

        try{
            $dir = dirname( $SaveAs );
            if ( ! wp_mkdir_p( $dir ) ) {
                $error_message = sprintf( __( 'The local directory %s does not exist and could not be created.', 'leopard-wordpress-offload-media' ), $dir );
                error_log( sprintf( __( 'There was an error attempting to download the file %s from the bucket: %s', 'leopard-wordpress-offload-media' ), $File_Name, $error_message ) );

                return false;
            }
            $bucket = $S3_Client->bucket($Bucket);
            $object = $bucket->object($this->rebuild_key($Key));
            $results = $object->downloadToFile($SaveAs);
            $result = $SaveAs;
        } catch (Exception $e){
            error_log($e->getMessage());
        }

        return $result;

    }

    /**
     * elimina un objeto de un bucket
     *
     * @param $key
     */
    public function deleteObject_nou( $Bucket, $Region, $array_files ) {

        $base_folder = array_shift( $array_files );

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $result = 0;

        foreach ( $array_files as $File_Name ) {

            if ( $base_folder != '' ) {
                $Key = $base_folder . "/" . $File_Name;
            } else {
                $Key = $File_Name;
            }

            $bucket = $S3_Client->bucket($Bucket);
            $object = $bucket->object($this->rebuild_key($Key));
            $result = $object->delete();
            

            $enable_webp = get_option('nou_leopard_offload_media_webp');
            if($enable_webp){
                try {
                    if(strpos($Key, '.png') !== false || strpos($Key, '.jpg') !== false || strpos($Key, '.jpeg') !== false){
                        $object = $bucket->object($this->rebuild_key($Key.'.webp'));
                        $object->delete();
                    }
                } catch (Exception $e) {
                    //
                }
            }

        }

        return $result;
    }

    public function set_object_permission($Bucket, $Region, $array_files, $acl='publicRead') {

        $base_folder = array_shift( $array_files );

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $result = 0;
        $acl = ( $acl == 'private' ? 'authenticatedRead' : 'publicRead' );
        foreach ( $array_files as $File_Name ) {

            if ( $base_folder != '' ) {
                $Key = $base_folder . "/" . $File_Name;
            } else {
                $Key = $File_Name;
            }

            try {
                $bucket = $S3_Client->bucket($Bucket);
                $object = $bucket->object($this->rebuild_key($Key));
                $result = $object->update([],['predefinedAcl' => $acl]);
            } catch ( Exception $e ) {
                error_log($e->getMessage());
            }
            

        }

        return $result;
    }

    public function Get_Presigned_URL( $Bucket, $Region, $Key ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $valid_time = ( get_option( 'nou_leopard_offload_media_time_valid_number' ) ? get_option( 'nou_leopard_offload_media_time_valid_number' ) : '5' );

        $bucket = $S3_Client->bucket($Bucket);
        $object = $bucket->object($this->rebuild_key($Key));
        return (string) $object->signedUrl(new \DateTime('+ ' . $valid_time . ' minutes'));

    }

    /**
     * @param Update CORS
     *
     * @return \Guzzle\Service\Resource\Model
     */
    public function putBucketCors( $Bucket, $Region, $origin=array('*'), $allowed_methods=array('GET', 'HEAD'), $max_age_seconds='3600' ) {
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        try{
            $cors = array(array(
                'origin' => $origin,
                'method' => $allowed_methods,
                'maxAgeSeconds' => $max_age_seconds,
                'responseHeader' => array('Content-Type')
            ));

            return $S3_Client->bucket($Bucket)->update([
                'cors' => $cors,
            ]);
        } catch ( Exception $e ) {
            error_log($e);
        }
    }

    /**
     * @param $key
     *
     * @return \Guzzle\Service\Resource\Model
     */
    public function create_Bucket( $Bucket, $Region='MULTI_REGIONAL' ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        try {
            $result = $S3_Client->createBucket($Bucket, ['predefinedAcl' => 'publicRead', 'storageClass' => $Region]);

            update_option('nou_leopard_offload_media_connection_bucket_selected_select', $Bucket);
            leopard_wordpress_offload_media_bucket_base_url();
            
        } catch ( AwsException $e ) {
            $result = $e->getMessage();
        }

        return $result;

    }

    public function Show_Keys_of_a_Folder_Bucket( $Current_Folder, $Region, $File_Selected = 'none' ) {

        ob_start();

        $Array_Current_Folder = explode( "/", $Current_Folder );

        $Bucket = array_shift( $Array_Current_Folder );

        $Top_Folder = array_pop( $Array_Current_Folder );

        $Path_S3_image = esc_url(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_URI.'admin/images/s3.png');
        $type = isset($_SESSION['leopard_wordpress_offload_media_view_type']) ? $_SESSION['leopard_wordpress_offload_media_view_type'] : 'list';
        ?>

        <div class="filemanager">
            <?php $this->build_filemanager_view_type($Current_Folder, $Region);?>
            <div class="breadcrumbs">

                <?php echo "<span class='folderName'><a href='".esc_url($Bucket)."' class='select-folder' data-region='".esc_attr($Region)."' data-current_folder='".esc_attr($Bucket)."'>/</a></span>";?>

                <?php

                $Current_Folder_Index = $Bucket;
                foreach ( $Array_Current_Folder as $Folder ) {
                    $Current_Folder_Index = $Current_Folder_Index . "/" . $Folder;
                    echo "<span class='folderName'><a href='".esc_url($Folder)."' class='select-folder' data-region='".esc_attr($Region)."' data-current_folder='".esc_attr($Current_Folder_Index)."'>".esc_html($Folder)."</a></span> <span class='folderName'>/</span>";
                }


                echo '<span class="folderName activate">' . $Top_Folder . '</span>';

                ?>

            </div>

            <ul class="data animated nou_leopard_wom_ul_File_Manager <?php echo $type;?>">

                <?php

                $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
                $Current_Sub_Folder = array();
                $Current_files = array();
                $bucket_path = $Current_Folder != $Bucket ? $Current_Folder."/" : $Bucket."/";

                if($Current_Folder != $Bucket){
                    $S3_Client->registerStreamWrapper();
                
                    if ( is_dir( "gs://".$bucket_path ) && ( $dh = opendir( "gs://".$bucket_path ) ) ) {
                        while ( ( $object = readdir( $dh ) ) !== false ) {
                            $tmp = str_replace($Bucket.'/', '', $Current_Folder);
                            $path = str_replace($tmp.'/', '', $object);
                            if (strpos($path, '/') !== false) {
                                $arr_path = explode( "/", $path );
                                if(isset($arr_path[0]) && !in_array($arr_path[0], $Current_Sub_Folder)){
                                    $Current_Sub_Folder[] = $arr_path[0];
                                }
                            }else{
                                if(!in_array($object, $Current_files)){
                                    $Current_files[] = $object;
                                }
                            }
                        }
                        closedir( $dh );
                    }
                }else{
                    $bucket = $S3_Client->bucket($Bucket);
                    foreach ($bucket->objects() as $object) {
                        $path = $object->name();
                        if (strpos($path, '/') !== false) {
                            $arr_path = explode( "/", $path );
                            if(isset($arr_path[0]) && !in_array($arr_path[0], $Current_Sub_Folder)){
                                $Current_Sub_Folder[] = $arr_path[0];
                            }
                        }else{
                            $Current_files[] = $path;
                        }
                    }
                }

                if(!empty($Current_files)){
                    foreach ($Current_files as $object) {
                        $type = substr(strrchr($object, '.'), 1);
                        $acl = $this->Get_Access_of_Object($Bucket, $Region, $object);
                        $tmp = str_replace($Bucket.'/', '', $Current_Folder);
                        $name = str_replace($tmp.'/', '', $object);
                        ?>
                            <li class="files nou_leopard_wom_ul_File_Manager_li_File">
                                <a href="#" title="<?php echo $object; ?>" data-path="<?php echo $bucket_path;?>" data-value="<?php echo $object; ?>" data-original="<?php echo $Key;?>" data-key="<?php echo leopard_wordpress_offload_media_get_url_from_key($object); ?>">
                                    <span class="icon file f-<?php echo $type;?>"><?php echo $type;?></span>
                                    <span class="name"><?php echo $name; ?></span>
                                    <label class="switch">
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" <?php checked($acl, 'public');?>>
                                            <label class="onoffswitch-label" for="myonoffswitch"><?php esc_html_e('Public', 'leopard-wordpress-offload-media');?></label>
                                    </label>
                                </a>
                            </li>
                        <?php
                    }
                }

                if(!empty($Current_Sub_Folder)){
                    foreach ($Current_Sub_Folder as $folder) {
                        ?>
                        <li class="folders">
                            <a href="#" class="select-folder" data-region='<?php echo $Region; ?>' data-current_folder='<?php echo $bucket_path. $folder; ?>'>
                                <span class="icon folder full"></span>
                                <span class="name"><?php echo $folder; ?></span>
                            </a>
                        </li>
                        <?php
                    }
                }
                ?>

            </ul>

        </div>

        <?php

        return ob_get_clean();

    }

    public function Get_Access_of_Object( $Bucket, $Region, $Key ) {

        $Access = 'private';
        
        try {
            $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
            $bucket = $S3_Client->bucket($Bucket);
            $object = $bucket->object($this->rebuild_key($Key));
            $acl = $object->acl();
            
            foreach ($acl->get() as $item) {
                if($item['role'] == 'READER' && $item['entity'] == 'allUsers'){
                    $Access = 'public';
                }else{
                    $Access = 'private';
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $Access;

    }

    public function get_all_objects( $Bucket, $Region='' ){
        set_time_limit(0);
        
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $files = array();
        $bucket = $S3_Client->bucket($Bucket);
        foreach ($bucket->objects() as $object) {
            $path = $object->name();
            $files[$Bucket . '/' . $path] = $path;
        }
        return $files;
    }

    public function updateMetadaObject($Bucket, $Region, $data){

        if(empty($data)){
            return false;
        }
        
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $S3_Client->registerStreamWrapper();

        $cacheControl = get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000');
        $args = [
                    'metadata' => [
                        'cacheControl' => $cacheControl   
                    ]
                ];

        $gzip = get_option('nou_leopard_offload_media_gzip', '');
        if($gzip){
            $args['metadata']['contentEncoding'] = 'gzip';
        }

        $mime_types = array_unique(array_merge($this->get_mime_types_to_gzip( true ), $this->get_allowed_mime_types()));
        $mimes = array_keys($mime_types);
        if( in_array($type, $mimes) ){
            $args['contentType'] = $mime_types[$type];
        }

        try {
            $bucket = $S3_Client->bucket($Bucket);
            $object = $bucket->object($data['key']);
            $result = $object->update($args);
        } catch ( Exception $e ) {
            //
        }
    }

    public function putFileContent($Bucket, $Region, $path, $content){
        try{
            $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
            $S3_Client->registerStreamWrapper();
            $Key = str_replace($this->dir.$Bucket.'/', '', $path);
            $cacheControl = get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000');
            $bucket = $S3_Client->bucket($Bucket);
            $args = [
                'name' => $Key,
                'predefinedAcl' => 'publicRead',
                'metadata'=> [
                    'cacheControl' => $cacheControl
                ]
            ];

            $type = substr(strrchr($Key, '.'), 1);
            $mime_types = array_unique(array_merge($this->get_mime_types_to_gzip( true ), $this->get_allowed_mime_types()));
            $mimes = array_keys($mime_types);
            if( in_array($type, $mimes) ){
                $args['contentType'] = $mime_types[$type];
            }

            $object = $bucket->upload($content, $args);
        } catch (Exception $e){
            error_log($e);
        }
    }

    public function copyObjectFromBucket($Bucket, $Region, $data){
        try{
            $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
            $S3_Client->registerStreamWrapper();
            $bucket = $S3_Client->bucket($Bucket);
            $object = $bucket->object($data['key']);
            $object->copy($data['bucket'], ['name' => $data['key']]);
            return true;
        } catch (Exception $e){
            return false;
        }
    }
}