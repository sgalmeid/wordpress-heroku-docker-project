<?php
/**
 * AWS S3 Client
 *
 * @since      1.0.0
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */

class Leopard_Wordpress_Offload_Media_Aws_Client extends Leopard_Wordpress_Offload_Media_Storage {
    
    public static function docs_link_credentials(){
        return 'https://aws.amazon.com/blogs/security/wheres-my-secret-access-key/';
    }

    public static function docs_link_create_bucket(){
        return 'https://docs.aws.amazon.com/en_us/quickstarts/latest/s3backup/step-1-create-bucket.html';
    }
}