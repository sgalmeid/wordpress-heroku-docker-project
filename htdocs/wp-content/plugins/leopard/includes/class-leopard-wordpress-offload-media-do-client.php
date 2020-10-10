<?php
/**
 * DigitalOcean Spaces Client
 *
 * @since      1.0.2
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */

class Leopard_Wordpress_Offload_Media_DO_Client extends Leopard_Wordpress_Offload_Media_Storage {

    public $region = null;

    public $dir = 's3://';

    public function setRegion($region){
        if(!empty($region)){
            $this->region = $region;
        }
    }

    public static function identifier() {
        return 'DO';
    }

    public static function name() {
        return esc_html__('DigitalOcean Spaces', 'leopard-wordpress-offload-media');
    }

    private function build_endpoint($spaceName = "", $Region = "nyc3"){
        $host = 'digitaloceanspaces.com';
        if(!empty($spaceName)) {
          $endpoint = "https://".$spaceName.".".$Region.".".$host;
        }else{
          $endpoint = "https://".$Region.".".$host;
        }
        return $endpoint;
    }

    public function Init_S3_Client( $Region = "", $Version, $key, $Secret ) {
        
        if(!empty($this->region)){
            $Region = $this->region;
        }else{
            $regions = $this->Get_Regions();
            if( empty($Region) || !in_array($Region, $regions) ){
                $Region = get_option('nou_leopard_offload_media_bucket_regional', 'nyc3');
            }
        }

        $endpoint = $this->build_endpoint('', $Region);

        $sdk = new Aws\Sdk( array(
            'endpoint'      => $endpoint,
            'region'        => $Region,
            'version'       => $Version,
            'credentials'   => array(
                'key'    => $key,
                'secret' => $Secret,
            ),
            'signature_version' => 'v4-unsigned-body'
        ) );
        return $sdk->createS3();
    }

    public function Load_Regions() {

        $this->_array_regions = array(
            '0'  => array( 'nyc3', 'New York City, United States' ),
            '1'  => array( 'sfo2', 'San Francisco, United States' ),
            '2'  => array( 'sgp1', 'Singapore' ),
            '3'  => array( 'fra1', 'Frankfurt, Germany' ),
            '4'  => array( 'ams3', 'Amsterdam' ),
        );
    }

    public function format_region($LocationConstraint){
        if(strpos($LocationConstraint, '<LocationConstraint') !== false){
            return str_replace('<LocationConstraint xmlns="http://s3.amazonaws.com/doc/2006-03-01/">', '', $LocationConstraint);
        }
        return $LocationConstraint;
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
            $Region = get_option('nou_leopard_offload_media_bucket_regional', 'nyc3');
            $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

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
                    $region = $this->format_region($result['LocationConstraint']);
                    if(in_array($region, $regions)){
                        $selected = ( ( $Bucket_Selected == $bucket['Name'] . "_nou_wc_as3s_separator_" . $region ) ? 'selected="selected"' : '' );

                        ?>
                        <option <?php echo $selected; ?> value="<?php echo esc_attr($bucket['Name'] . "_nou_wc_as3s_separator_" . $region); ?>"> <?php echo esc_html($bucket['Name'] . " - " . $region); ?> </option>
                        <?php
                    }    

                }

            }

        } catch ( Exception $e ) {

            //
        }

        return ob_get_clean();

    }

    /**
     * @param $key
     *
     * @return \Guzzle\Service\Resource\Model
     */
    public function create_Bucket( $Bucket, $Region='sfo2' ) {

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

        } catch ( Exception $e ) {
            $result = ['message' => esc_html__('Access Denied to Bucket — Looks like we don\'t have write access to this bucket. It\'s likely that the user you\'ve provided credentials for hasn\'t been granted the correct permissions.', 'leopard-wordpress-offload-media'), 'code' => '400'];
        }

        return $result;

    }

    public function Get_Access_of_Object( $Bucket, $Region, $Key ) {

        $S3_Client = $this->Init_S3_Client( $Region, $this->_version, $this->_key, $this->_secret );

        // Get an objectAcl
        $result = $S3_Client->getObjectAcl( array(
            'Bucket' => $Bucket,
            'Key'    => $Key
        ));

        $Access = 'private';

        if ( isset( $result['Grants'][0] ) )
            if ( $result['Grants'][0]['Permission'] == 'READ' )
                $Access = 'public';

        return $Access;

    }

    public function get_base_url($bucket, $Region, $Keyname=''){
        return $this->build_endpoint($bucket, $Region);
    }

    public static function docs_link_credentials(){
        return 'https://www.digitalocean.com/community/tutorials/how-to-create-a-digitalocean-space-and-api-key';
    }

    public static function docs_link_create_bucket(){
        return 'https://www.digitalocean.com/community/tutorials/how-to-create-a-digitalocean-space-and-api-key';
    }

}