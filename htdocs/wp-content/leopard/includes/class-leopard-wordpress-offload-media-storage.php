<?php
/**
 * Storege
 *
 * @since      1.0.2
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\AwsException;

class Leopard_Wordpress_Offload_Media_Storage {
    /**
     * @var string
     */

    protected $_region = null;

    public $_array_regions = null;

    public $dir = 's3://';

    /**
     * @var string
     */
    protected $_version = 'latest';

    /**
     * @var string
     */
    protected $bucket = null;

    /**
     * @var string
     */
    protected $directory = '';

    /**
     * @var string
     */
    protected $_key = null;

    /**
     * @var string
     */
    protected $_secret = null;

    /**
     * @var S3Client|null
     */
    protected $s3_client = null; //instancia de s3

    protected static $_instance = null;

    /**
     * instancia de s3
     * AwsS3 constructor.
     */
    public function __construct( $key, $secret ) {

        $this->_key    = $key;
        $this->_secret = $secret;
        $this->Load_Regions();

    }

    public static function identifier() {
        return 's3';
    }

    public static function name() {
        return esc_html__('Amazon S3', 'leopard-wordpress-offload-media');
    }

    public function Init_S3_Client( $Region, $Version, $key, $Secret ) {

        $sdk = new Aws\Sdk( array(
            'region'      => $Region,
            'version'     => $Version,
            'credentials' => array(
                'key'    => $key,
                'secret' => $Secret,
            )
        ) );

        return $sdk->createS3();

    }

    public function Load_Regions() {

        $this->_array_regions = array(
            '0'  => array( 'us-east-1', 'US East (N. Virginia)' ),
            '1'  => array( 'us-east-2', 'US East (Ohio)' ),
            '2'  => array( 'us-west-1', 'US West (N. California)' ),
            '3'  => array( 'us-west-2', 'US West (Oregon)' ),
            '4'  => array( 'ca-central-1', 'Canada (Central)' ),
            '5'  => array( 'ap-south-1', 'Asia Pacific (Mumbai)' ),
            '6'  => array( 'ap-northeast-2', 'Asia Pacific (Seoul)' ),
            '7'  => array( 'ap-southeast-1', 'Asia Pacific (Singapore)' ),
            '8'  => array( 'ap-southeast-2', 'Asia Pacific (Sydney)' ),
            '9'  => array( 'ap-northeast-1', 'Asia Pacific (Tokyo)' ),
            '10' => array( 'eu-central-1', 'EU (Frankfurt)' ),
            '11' => array( 'eu-west-1', 'EU (Ireland)' ),
            '12' => array( 'eu-west-2', 'EU (London)' ),
            '13' => array( 'sa-east-1', 'South America (São Paulo)' ),
            '14' => array( 'ap-east-1', 'Asia Pacific (Hong Kong)' ),
        );
    }

    public function handler_response($response){
        $response = json_decode($response, true);
        return isset($response['error']) ? $response['error'] : ['code' => '400', 'message' => esc_html__('Error, please try again.', 'leopard-wordpress-offload-media')];
    }

    public function Get_Regions() {
        $regions = array();
        foreach ($this->_array_regions as $key => $region) {
            $regions[] = $region[0];
        }
        return $regions;
    }

    public static function get_instance() {
        $self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

        if ( is_null( $self::$_instance ) ) {
            $self::$_instance = new $self;
        }

        return $self::$_instance;
    }

    public function Checking_Credentials() {

        try {

            // Instantiate the S3 client with your AWS credentials
            $S3_Client = $this->Init_S3_Client( $this->_array_regions[0][0], $this->_version, $this->_key, $this->_secret );

            $buckets = $S3_Client->listBuckets();

            update_option( 'nou_leopard_offload_media_connection_success', 1 );

        } catch ( Exception $e ) {

            update_option( 'nou_leopard_offload_media_connection_success', 0 );

            $buckets = 0;

        }

        return $buckets;

    }

    public function setClient($Region){
        return $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
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

            $buckets = $S3_Client->listBuckets();
            $regions = $this->Get_Regions();
            
            echo "<option value='0'>" . esc_html__( 'Choose a bucket', 'leopard-wordpress-offload-media' ) . "</option>";

            foreach ( $buckets['Buckets'] as $bucket ) {

                try {
                    $result = $S3_Client->getBucketLocation(array(
                        'Bucket' => $bucket['Name'],
                    ));
                } catch ( S3Exception $e ) {
                    $result = false;
                }
                
                if ( $result ){
                    if(in_array($result['LocationConstraint'], $regions)){
                        $selected = ( ( $Bucket_Selected == $bucket['Name'] . "_nou_wc_as3s_separator_" . $result['LocationConstraint'] ) ? 'selected="selected"' : '' );

                        ?>
                        <option <?php echo $selected; ?> value="<?php echo esc_attr($bucket['Name'] . "_nou_wc_as3s_separator_" . $result['LocationConstraint']); ?>"> <?php echo esc_html($bucket['Name'] . " - " . $result['LocationConstraint']); ?> </option>
                        <?php
                    }    

                }

            }

        } catch ( Exception $e ) {

            //
        }

        return ob_get_clean();

    }

    public function rebuild_key($Key, $custom_prefix=''){
        $prefix = $this->getBucketMainFolder();
        $new_key = $Key;
        if(strpos($Key, $prefix) !== false){
            $new_key = str_replace($prefix, '', $Key);
        }
        $new_key = $prefix.$custom_prefix.$new_key;
        return $new_key;
    }

    public function set_object_permission($Bucket, $Region, $array_files, $acl='public-read') {

        $base_folder = array_shift( $array_files );

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $result = 0;
        foreach ( $array_files as $File_Name ) {

            if ( $base_folder != '' ) {
                $Key = $base_folder . "/" . $File_Name;
            } else {
                $Key = $File_Name;
            }

            try {
                $result = $S3_Client->putObjectAcl(['Bucket' => $Bucket, 'Key' => $this->rebuild_key($Key), 'ACL' => $acl]);
            } catch ( Exception $e ) {
                //
                error_log($e);
            }
            

        }

        return $result;
    }

    public function Get_Presigned_URL( $Bucket, $Region, $Key ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $cmd = $S3_Client->getCommand( 'GetObject', [
            'Bucket' => $Bucket,
            'Key'    => $this->rebuild_key($Key)
        ] );

        $valid_time = ( get_option( 'nou_leopard_offload_media_time_valid_number' ) ? get_option( 'nou_leopard_offload_media_time_valid_number' ) : '5' );

        $request = $S3_Client->createPresignedRequest( $cmd, '+'. $valid_time . ' minutes' );

        // Get the actual presigned-url
        return (string) $request->getUri();
    }

    public function Get_Access_of_Object( $Bucket, $Region, $Key ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $Access = 'private';
        try {
            // Get an objectAcl
            $result = $S3_Client->getObjectAcl( array(
                'Bucket' => $Bucket,
                'Key'    => $this->rebuild_key($Key)
            ));
            
            if ( isset( $result['Grants'][1] ) )
                if ( $result['Grants'][1]['Permission'] == 'READ' )
                    $Access = 'public';
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $Access;

    }

    public function build_filemanager_view_type($Current_Folder, $Region){
        $type = isset($_SESSION['leopard_wordpress_offload_media_view_type']) ? $_SESSION['leopard_wordpress_offload_media_view_type'] : 'list';
        ?>
        <div class="view-switch filemanager-display">
            <a href=""  <?php echo 'data-region="'.esc_attr($Region).'"';?> <?php echo 'data-current_folder="'.esc_attr($Current_Folder).'"';?> class="view view-list <?php if($type == 'list'){echo 'current';}?>">
                <span class="screen-reader-text">List View</span>
            </a>
            <a href=""  <?php echo 'data-region="'.esc_attr($Region).'"';?> <?php echo 'data-current_folder="'.esc_attr($Current_Folder).'"';?> class="view view-grid <?php if($type == 'grid'){echo 'current';}?>">
                <span class="screen-reader-text">Grid View</span>
            </a>
            <a href="" data-type="shortcode" class="use view-shortcode" title="<?php esc_html_e('Use shortcode', 'leopard-wordpress-offload-media');?>">
                <span class="screen-reader-text">shortcode View</span>
            </a>
            <a href="" data-type="url" class="use view-url current" title="<?php esc_html_e('Use full URL', 'leopard-wordpress-offload-media');?>">
                <span class="screen-reader-text">URL View</span>
            </a>
        </div>
        <?php
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


                echo '<span class="folderName">' . $Top_Folder . '</span>';

                ?>

            </div>

            <ul class="data animated nou_leopard_wom_ul_File_Manager <?php echo $type;?>">

                <?php

                $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

                // Register the stream wrapper from an S3Client object
                $S3_Client->registerStreamWrapper();

                if ( is_dir( "s3://" . $Current_Folder ) && ( $dh = opendir( "s3://" . $Current_Folder ) ) ) {

                    while ( ( $object = readdir( $dh ) ) !== false ) {

                        if ( is_dir( "s3://" . $Current_Folder . "/" . $object ) ) {

                            ?>
                            <li class="folders">
                                <a href="#" class="select-folder" data-region='<?php echo $Region; ?>' data-current_folder='<?php echo $Current_Folder . "/" . $object; ?>'>
                                    <span class="icon folder full"></span>
                                    <span class="name"><?php echo $object; ?></span>
                                </a>
                            </li>
                            <?php

                        } else {

                            $Key = $Current_Folder . "/" . $object;
                            $Key = str_replace( $Bucket . "/", "", $Key );
                            $type = substr(strrchr($object, '.'), 1);
                            $acl = $this->Get_Access_of_Object($Bucket, $Region, $Key);
                            ?>
                            <li class="files nou_leopard_wom_ul_File_Manager_li_File">
                                <a href="#" title="<?php echo $object; ?>" data-value="<?php echo $object; ?>" data-original="<?php echo $Key;?>" data-key="<?php echo leopard_wordpress_offload_media_get_url_from_key($Key); ?>">
                                    <span class="icon file f-<?php echo $type;?>"><?php echo $type;?></span>
                                    <span class="name"><?php echo $object; ?></span>
                                    <label class="switch">
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" <?php checked($acl, 'public');?>>
                                            <label class="onoffswitch-label" for="myonoffswitch"><?php esc_html_e('Public', 'leopard-wordpress-offload-media');?></label>
                                    </label>
                                </a>
                            </li>
                            <?php

                        }

                    }

                    closedir( $dh );

                }

                ?>

            </ul>

        </div>

        <?php

        return ob_get_clean();

    }

    public function getObjectUrl( $Bucket, $Region, $File_Name ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        return $S3_Client->getObjectUrl( $Bucket, $File_Name );

    }

    public function get_base_url( $Bucket, $Region, $Keyname ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        try{
            $result = $S3_Client->putObject( array(
                'Bucket'     => $Bucket,
                'Key'        => $Keyname,
                'Body'   => 'Leopard Offload Media -> getting the base url',
                'ACL'    => 'public-read'
            ) );

            if ( ! $result ) {
                error_log( print_r( 'Error when uploading result_of_array', true ) );
                $base_url = 0;
            }
            else
                $base_url = str_replace( "/" .$Keyname , "", $result[ 'ObjectURL' ] );

            return $base_url;
        } catch(Exception $e){
            return 0;
        }

    }

    public function getBucketMainFolder(){
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $url = get_option('nou_leopard_offload_media_bucket_folder_main', '');
        if(empty($url)){
            return '';
        }
        if(substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }

        return $url.'/';
    }

    public function Upload_Media_File( $Bucket, $Region, $array_files, $basedir_absolute, $private_or_public = 'public', $prefix='' ) {

        $params = [];
        $options = [];
        $result = '';

        $cacheControl = get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000');

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
        $private_or_public = ( $private_or_public == 'private' ? $private_or_public : 'public-read' );

        if(file_exists($SourceFile)){

            $args = array(
                'Bucket'     => $Bucket,
                'Key'        => $this->rebuild_key($Key, $prefix),
                'SourceFile' => $SourceFile,
                'ACL'        => $private_or_public,
                'CacheControl' => $cacheControl
            );
            $type = substr(strrchr($Key, '.'), 1);
            if ( $this->should_gzip_file( $SourceFile, $type ) && false !== ( $gzip_body = gzencode( file_get_contents( $SourceFile ) ) ) ) {
                unset( $args['SourceFile'] );
                $args['Body']            = $gzip_body;
                $args['ContentEncoding'] = 'gzip';

                $mime_types = $this->get_mime_types_to_gzip( true );
                $mimes = array_keys($mime_types);
                if(in_array($type, $mimes)){
                    $args['ContentType'] = $mime_types[$type];
                }
            }

            try {
                $result = $S3_Client->putObject( $args );
            } catch (Exception $e) {
                error_log($e->getMessage());
                $result = false;
            }
        }

        if ( ! $result ) {
            error_log( print_r( 'Error when uploading result_of_array: '.$SourceFile, true ) );
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
            
            if(file_exists($SourceFile)){
                $args = array(
                    'Bucket'     => $Bucket,
                    'Key'        => $this->rebuild_key($Key, $prefix),
                    'SourceFile' => $SourceFile,
                    'ACL'        => $private_or_public,
                    'CacheControl' => $cacheControl
                );

                $type = substr(strrchr($Key, '.'), 1);
                if ( $this->should_gzip_file( $SourceFile, $type ) && false !== ( $gzip_body = gzencode( file_get_contents( $SourceFile ) ) ) ) {
                    unset( $args['SourceFile'] );
                    $args['Body']            = $gzip_body;
                    $args['ContentEncoding'] = 'gzip';

                    $mime_types = $this->get_mime_types_to_gzip( true );
                    $mimes = array_keys($mime_types);
                    if(in_array($type, $mimes)){
                        $args['ContentType'] = $mime_types[$type];
                    }
                }

                try {
                    $result_of_array = $S3_Client->putObject( $args );
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $result_of_array = false;
                }

                if ( ! $result_of_array ) {
                    error_log( print_r( 'Error when uploading result_of_array: '.$SourceFile, true ) );
                }
            }    

        }

        return $result;

    }



    /**
     * Should gzip file
     *
     * @param string $file_path
     * @param string $type
     *
     * @return bool
     */
    public function should_gzip_file( $file_path, $type ) {
        $gzip = get_option('nou_leopard_offload_media_gzip', '');
        
        if(empty($gzip)){
            return false;
        }

        $mimes = $this->get_mime_types_to_gzip( true );

        if ( is_readable( $file_path ) && in_array($type, $mimes) ) {
            return true;
        }

        return false;
    }

    /**
     * Get mime types to gzip
     *
     * @param bool $media_library
     *
     * @return array
     */
    protected function get_mime_types_to_gzip( $media_library = false ) {
        $mimes = apply_filters( 'nou_leopard_offload_media_gzip_mime_types', array(
            'css'   => 'text/css',
            'eot'   => 'application/vnd.ms-fontobject',
            'html'  => 'text/html',
            'ico'   => 'image/x-icon',
            'js'    => 'application/javascript',
            'json'  => 'application/json',
            'otf'   => 'application/x-font-opentype',
            'rss'   => 'application/rss+xml',
            'svg'   => 'image/svg+xml',
            'ttf'   => 'application/x-font-ttf',
            'woff'  => 'application/font-woff',
            'woff2' => 'application/font-woff2',
            'xml'   => 'application/xml',
        ), $media_library );

        return $mimes;
    }

    /**
     * Get mime types all
     *
     * @param bool $media_library
     *
     * @return array
     */
    protected function get_allowed_mime_types() {
        $mimes = array(
            // Image formats
            'jpg'                 => 'image/jpeg',
            'jpeg'                 => 'image/jpeg',
            'jpe'                 => 'image/jpeg',
            'gif'                          => 'image/gif',
            'png'                          => 'image/png',
            'bmp'                          => 'image/bmp',
            'tif'                     => 'image/tiff',
            'tiff'                     => 'image/tiff',
            'ico'                          => 'image/x-icon',

            // Video formats
            'asf'                      => 'video/x-ms-asf',
            'asx'                      => 'video/x-ms-asf',
            'wmv'                          => 'video/x-ms-wmv',
            'wmx'                          => 'video/x-ms-wmx',
            'wm'                           => 'video/x-ms-wm',
            'avi'                          => 'video/avi',
            'divx'                         => 'video/divx',
            'flv'                          => 'video/x-flv',
            'mov'                       => 'video/quicktime',
            'qt'                       => 'video/quicktime',
            'mpeg'                 => 'video/mpeg',
            'mpg'                 => 'video/mpeg',
            'mpe'                 => 'video/mpeg',
            'mp4'                      => 'video/mp4',
            'm4v'                      => 'video/mp4',
            'ogv'                          => 'video/ogg',
            'webm'                         => 'video/webm',
            'mkv'                          => 'video/x-matroska',
            
            // Text formats
            'txt'               => 'text/plain',
            'csv'                          => 'text/csv',
            'tsv'                          => 'text/tab-separated-values',
            'ics'                          => 'text/calendar',
            'rtx'                          => 'text/richtext',
            'css'                          => 'text/css',
            'htm'                     => 'text/html',
            'html'                     => 'text/html',
            
            // Audio formats
            'mp3'                  => 'audio/mpeg',
            'm4a'                  => 'audio/mpeg',
            'm4b'                  => 'audio/mpeg',
            'ra'                       => 'audio/x-realaudio',
            'ram'                       => 'audio/x-realaudio',
            'wav'                          => 'audio/wav',
            'ogg'                      => 'audio/ogg',
            'oga'                      => 'audio/ogg',
            'mid'                     => 'audio/midi',
            'midi'                     => 'audio/midi',
            'wma'                          => 'audio/x-ms-wma',
            'wax'                          => 'audio/x-ms-wax',
            'mka'                          => 'audio/x-matroska',
            
            // Misc application formats
            'rtf'                          => 'application/rtf',
            'js'                           => 'application/javascript',
            'pdf'                          => 'application/pdf',
            'swf'                          => 'application/x-shockwave-flash',
            'class'                        => 'application/java',
            'tar'                          => 'application/x-tar',
            'zip'                          => 'application/zip',
            'gz'                      => 'application/x-gzip',
            'gzip'                      => 'application/x-gzip',
            'rar'                          => 'application/rar',
            '7z'                           => 'application/x-7z-compressed',
            'exe'                          => 'application/x-msdownload',
            
            // MS Office formats
            'doc'                          => 'application/msword',
            'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
            'wri'                          => 'application/vnd.ms-write',
            'xla'              => 'application/vnd.ms-excel',
            'xls'              => 'application/vnd.ms-excel',
            'xlt'              => 'application/vnd.ms-excel',
            'xlw'              => 'application/vnd.ms-excel',
            'mdb'                          => 'application/vnd.ms-access',
            'mpp'                          => 'application/vnd.ms-project',
            'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc' => 'application/onenote',
            'onetoc2' => 'application/onenote',
            'onetmp' => 'application/onenote',
            'onepkg' => 'application/onenote',
            
            // OpenOffice formats
            'odt'                          => 'application/vnd.oasis.opendocument.text',
            'odp'                          => 'application/vnd.oasis.opendocument.presentation',
            'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg'                          => 'application/vnd.oasis.opendocument.graphics',
            'odc'                          => 'application/vnd.oasis.opendocument.chart',
            'odb'                          => 'application/vnd.oasis.opendocument.database',
            'odf'                          => 'application/vnd.oasis.opendocument.formula',
            
            // WordPerfect formats
            'wp'                       => 'application/wordperfect',
            'wpd'                       => 'application/wordperfect',
            
            // iWork formats
            'key'                          => 'application/vnd.apple.keynote',
            'numbers'                      => 'application/vnd.apple.numbers',
            'pages'                        => 'application/vnd.apple.pages',
        );

        return $mimes;
    }

    public function delete_Objects_no_base_folder_nou( $Bucket, $Region, $array_files ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $result = 0;
        foreach ( $array_files as $Key ) {

            try{
                $result = $S3_Client->deleteObject( array(
                    'Bucket' => $Bucket,
                    'Key'    => $this->rebuild_key($Key)
                ) );
            } catch(Exception $e){
                //
            }

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
            $result = $S3_Client->deleteObject( array(
                'Bucket' => $Bucket,
                'Key'    => $this->rebuild_key($Key)
            ) );
            
            $enable_webp = get_option('nou_leopard_offload_media_webp');
            if($enable_webp){
                try {
                    if(strpos($Key, '.png') !== false || strpos($Key, '.jpg') !== false || strpos($Key, '.jpeg') !== false){
                        $S3_Client->deleteObject( array(
                            'Bucket' => $Bucket,
                            'Key'    => $this->rebuild_key($Key.'.webp')
                        ) );
                    }
                } catch (Exception $e) {
                    //
                }
            }

        }

        return $result;
    }

    /**
     * @param $key
     *
     * @return \Guzzle\Service\Resource\Model
     */
    public function create_Bucket( $Bucket, $Region='us-east-1' ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        try {
            $result = $S3_Client->createBucket( [
                'Bucket' => $Bucket,
                'ACL' => 'public-read',
                'CreateBucketConfiguration' => [
                    'LocationConstraint' => $Region,
                ],
            ] );
            update_option('nou_leopard_offload_media_connection_bucket_selected_select', $Bucket.'_nou_wc_as3s_separator_'.$Region);
            leopard_wordpress_offload_media_bucket_base_url();

        } catch ( AwsException $e ) {
            $result = ['message' => esc_html__('Access Denied to Bucket — Looks like we don\'t have write access to this bucket. It\'s likely that the user you\'ve provided credentials for hasn\'t been granted the correct permissions.', 'leopard-wordpress-offload-media'), 'code' => '400'];
        }

        return $result;

    }

    /**
     * download files
     *
     * @param $key
     * @param $filename
     */
    public function download_file( $Bucket, $Region, $array_files, $basedir_absolute ) {
        $result = false;
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
                $result = $S3_Client->getObject( array(
                    'Bucket' => $Bucket,
                    'Key'    => $this->rebuild_key($Key),
                    'SaveAs' => $SaveAs
                ) );
            } catch(Exception $e) {
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
        $result = false;
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
                
            $results = $S3_Client->getObject( array(
                'Bucket' => $Bucket,
                'Key'    => $this->rebuild_key($Key),
                'SaveAs' => $SaveAs
            ) );
            $result = $SaveAs;
        } catch(Exception $e) {
            error_log($e->getMessage());
        }

        return $result;

    }

    /**
     * @param $key
     *
     * @return \Guzzle\Service\Resource\Model
     */
    public function getObject( $Bucket, $Region, $key, $expires = null ) {
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        $data = array(
            'Bucket' => $Bucket,
            'Key'    => $this->rebuild_key($key)
        );
        
        if ( !is_null( $expires ) ) {
            $data['ResponseExpires'] = $expires;
        }

        $object = $S3_Client->getObject($data);

        return $object->toArray();
    }

    /**
     * @param Update CORS
     * 
     * @since      1.0.4
     * @return \Guzzle\Service\Resource\Model
     */
    public function putBucketCors( $Bucket, $Region, $origin=array('*'), $allowed_methods=array('GET', 'HEAD'), $max_age_seconds='3600' ) {
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        
        try{

            $cors = array(
                array(
                    'AllowedOrigins' => $origin,
                    'AllowedMethods' => $allowed_methods,
                    'MaxAgeSeconds' => $max_age_seconds,
                    'AllowedHeaders' => array('*')
                ),
            );
            $result = $S3_Client->putBucketCors(
                array(
                    'Bucket' => $Bucket,
                    'CORSConfiguration' => array('CORSRules' => $cors)
                )
            );
           return $result;
        } catch ( AwsException $e ) {
            //
        }
    }

    public function update_cache_control_objects( $Bucket, $Region='' ) {
        set_time_limit(0);
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $radio_private_or_public = get_option('nou_leopard_offload_media_private_public_radio_button', 'public');
        $cacheControl = get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000');
        $private_or_public = ( $radio_private_or_public == 'private' ? $radio_private_or_public : 'public-read' );


        $S3_Client->registerStreamWrapper();
        $Current_Folder = $Bucket;
        $dir = array();
        try{
            if ( is_dir( "s3://" . $Current_Folder ) && ( $dh = opendir( "s3://" . $Current_Folder ) ) ) {

                while ( ( $object = readdir( $dh ) ) !== false ) {

                    if ( is_dir( "s3://" . $Current_Folder . "/" . $object ) ) {
                        $dir[] = $object;
                    } else {
                        try{
                            $Key = $Current_Folder . "/" . $object;
                            $Key = str_replace( $Bucket . "/", "", $Key );
                            $args = array(
                                'Bucket'                => $Bucket,
                                'CopySource'            => $Bucket . '/' . $Key,
                                'Key'                   => $Key,
                                'ACL'                   => $private_or_public,
                                'CacheControl'          => $cacheControl,
                                'MetadataDirective'     => 'COPY'
                            );
                            $this->copyObject($Region, $args);
                        } catch (Exception $e){
                            //
                        }
                    }

                }

                closedir( $dh );

            }
        }catch(Exception $e){

        }

        if(!empty($dir)){
            foreach ($dir as $prefix) {
                try{
                    $results = $S3_Client->getPaginator('ListObjects', [
                        'Bucket' => $Bucket,
                        "Prefix" => $prefix.'/'
                    ]);
                    foreach ($results as $result) {
                        foreach ($result['Contents'] as $object) {
                            $key = $object['Key'];
                            $args = array(
                                'Bucket'                => $Bucket,
                                'CopySource'            => $Bucket . '/' . $key,
                                'Key'                   => $key,
                                'ACL'                   => $private_or_public,
                                'CacheControl'          => $cacheControl,
                                'MetadataDirective'     => 'COPY'
                            );
                            $this->copyObject($Region, $args);
                        }
                    }
                } catch (Exception $e){
                    //
                }
            }
        }
        return '';
    }

    public function get_all_objects( $Bucket, $Region='' ){
        set_time_limit(0);
        
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $S3_Client->registerStreamWrapper();
        $Current_Folder = $Bucket;
        $dir = array();
        $files = array();

        try{
            if ( is_dir( $this->dir . $Current_Folder ) && ( $dh = opendir( $this->dir . $Current_Folder ) ) ) {

                while ( ( $object = readdir( $dh ) ) !== false ) {

                    if ( is_dir( $this->dir . $Current_Folder . "/" . $object ) && !in_array($object, $dir) ) {
                        $dir[] = $object;
                    } else {
                        $Key = $Current_Folder . "/" . $object;
                        $Key = str_replace( $Bucket . "/", "", $Key );
                        if(!isset($files[$Bucket . '/' . $Key])){
                            $files[$Bucket . '/' . $Key] = $Key;
                        }    
                    }

                }

                closedir( $dh );

            }
        }catch(Exception $e){

        }

        if(!empty($dir)){
            foreach ($dir as $prefix) {
                try{
                    $results = $S3_Client->getPaginator('ListObjects', [
                        'Bucket' => $Bucket,
                        "Prefix" => $prefix.'/'
                    ]);
                    foreach ($results as $result) {
                        foreach ($result['Contents'] as $object) {
                            $key = $object['Key'];

                            if(!isset($files[$Bucket . '/' . $key])){
                                $files[$Bucket . '/' . $key] = $key;
                            } 
                        }
                    }
                } catch (Exception $e){
                    //
                }
            }
        }

        return $files;
    }

    public function copyObject($Region, $args){
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        return $S3_Client->copyObject($args);
    }

    public function updateMetadaObject($Bucket, $Region, $data){
        
        if(empty($data)){
            return false;
        }

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $S3_Client->registerStreamWrapper();

        $cacheControl = get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000');
        $private_or_public = ( $data['acl'] == 'private' ? $data['acl'] : 'public-read' );

        $args = array(
            'Bucket'                => $Bucket,
            'CopySource'            => $Bucket . '/' . $data['key'],
            'Key'                   => $data['key'],
            'ACL'                   => $private_or_public,
            'CacheControl'          => $cacheControl,
            'MetadataDirective'     => 'REPLACE'
        );

        $type = substr(strrchr($data['key'], '.'), 1);
        if ( $this->should_gzip_file( $this->dir.$Bucket.'/'.$data['key'], $type ) ) {
            $args['ContentEncoding'] = 'gzip';
        }

        $mime_types = array_unique(array_merge($this->get_mime_types_to_gzip( true ), $this->get_allowed_mime_types()));
        $mimes = array_keys($mime_types);
        if( in_array($type, $mimes) ){
            $args['ContentType'] = $mime_types[$type];
        }
        
        return $S3_Client->copyObject($args);
    }

    public static function docs_link_credentials(){
        return '';
    }

    public static function docs_link_create_bucket(){
        return '';
    }

    public function putFileContent($Bucket, $Region, $path, $content){
        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
        $S3_Client->registerStreamWrapper();
        return file_put_contents($path, $content);
    }

    public function copyObjectFromBucket($Bucket, $Region, $data){
        try{
            $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );
            $S3_Client->registerStreamWrapper();
            if(file_exists( $this->dir.$data['source'] )){
                $old_path = $this->dir.$data['source'];
                $new_permanent_path = $this->dir.$data['bucket'].'/'.$data['key'];
                copy($old_path, $new_permanent_path);
            }
            return true;
        } catch (Exception $e){
            return false;
        }
    }

}