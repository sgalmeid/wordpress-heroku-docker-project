<?php
/**
 * Get provider
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.2
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */


/**
 * Check PHP version
 * @since      1.0.8
 * @return bool
 */
function nou_leopard_offload_media_version_check(){
	if ( function_exists( 'phpversion' ) && version_compare( phpversion(), LEOPARD_WORDPRESS_OFFLOAD_MEDIA_MINIMUM_PHP_VERSION, '>=' ) ) {
		return true;
	}
	return false;
}

/**
 * Load template with variables
 * @since      1.0.8
 * @return bool
 */
function nou_leopard_offload_media_load_template($filePath, $variables = array(), $print = true){
    $output = NULL;
    $path = LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR.$filePath;
    if(file_exists($path)){
        // Extract the variables to a local namespace
        extract($variables);

        // Start output buffering
        ob_start();

        // Include the template file
        include $path;

        // End buffering and return its contents
        $output = ob_get_clean();
    }
    if ($print) {
        print $output;
    }
    return $output;

}

/**
 * Create file
 *
 * @param bool $with_credentials Do provider credentials need to be set up too? Defaults to false.
 * @since      1.0.4
 * @return bool
 */
function nou_leopard_offload_media_get_domain() {
	if ( defined( "WPINC" ) && function_exists( "get_bloginfo" ) ) {
		return get_bloginfo( 'url' );
	} else {
		$base_url = ( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == "on" ) ? "https" : "http" );
		$base_url .= "://" . $_SERVER['HTTP_HOST'];
		$base_url .= str_replace( basename( $_SERVER['SCRIPT_NAME'] ), "", $_SERVER['SCRIPT_NAME'] );

		return $base_url;
	}
}

/**
 * Create file
 *
 * @param bool $with_credentials Do provider credentials need to be set up too? Defaults to false.
 * @since      1.0.4
 * @return bool
 */
function nou_leopard_offload_media_create_file( $file ) {
	$path = trailingslashit( $file['base'] ) . $file['file'];
	if ( wp_mkdir_p( $file['base'] ) && ! file_exists( $path ) ) {
		$file_handle = @fopen( $path, 'w' );
		if ( $file_handle ) {
			fwrite( $file_handle, $file['content'] );
			fclose( $file_handle );
			return $path;
		}
	}
	
	return false;
}
/**
 * Create files
 *
 * @param bool $with_credentials Do provider credentials need to be set up too? Defaults to false.
 * @since      1.0.4
 * @return bool
 */
function nou_leopard_offload_media_create_files( $files ) {
	
	foreach ( $files as $file ) {
		nou_leopard_offload_media_create_file($file);
	}

	// All good, let's do this
	return true;
}

/**
 * Check the plugin is correctly setup
 *
 * @param bool $with_credentials Do provider credentials need to be set up too? Defaults to false.
 *
 * @return bool
 */
function nou_leopard_offload_media_is_plugin_setup( $with_credentials = false ) {

	if(!nou_leopard_offload_media_version_check()){
		return false;
	}
	
	$active 		= get_option('nou_leopard_offload_media_license_active');
	$emailAddress 	= get_option('nou_leopard_offload_media_license_email');
	$purchase_key 	= get_option('nou_leopard_offload_media_license_key');
    if ( empty($purchase_key) || empty($emailAddress) || $active != '1' ){
		return false;
	}
	    	
	$connection = get_option('nou_leopard_offload_media_connection_success');
	if(!$connection){
		return false;
	}

	$settings = get_option('nou_leopard_offload_media');
	$provider = isset($settings['provider']) ? $settings['provider'] : 'aws';
	if($provider == 'DO'){
		$regional = get_option('nou_leopard_offload_media_bucket_regional');
		if(empty($regional)){
			return false;
		}
	}

	$bucket = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
	if(!$bucket){
		return false;
	}

	// All good, let's do this
	return true;
}


/**
 * Check the plugin enable mod rewrite url
 *
 * @return bool
 */
function nou_leopard_offload_media_enable_rewrite_urls() {

	if(!nou_leopard_offload_media_is_plugin_setup()){
		return false;
	}
	    	
	$rewrite_urls = get_option('nou_leopard_offload_media_rewrite_urls_checkbox');
	if(empty($rewrite_urls)){
		return false;
	}

	// All good, let's do this
	return true;
}


/**
 * Get provider
 *
 * @return class
 */
function leopard_offload_media_provider($provider='', $settings=[]) {
	if(empty($provider)){
		$settings = get_option('nou_leopard_offload_media');
		$provider = isset($settings['provider']) ? $settings['provider'] : 'aws';
	}
	$class = '';
	switch ($provider) {
		case 'wasabi':
			$Access_Key = $settings['access_key'];
			$Secret_Access_Key = $settings['secret_access_key'];
			$class = new Leopard_Wordpress_Offload_Media_Wasabi_Client( $Access_Key, $Secret_Access_Key );
			break;
		case 'google':
			if(isset($settings['credentials_key']) && !empty($settings['credentials_key'])){
				$Access_Key = $settings['credentials_key'];
			}else{
				$Access_Key = get_option('nou_leopard_offload_media_google_credentials');
			}
			$Secret_Access_Key = '';
			$class = new Leopard_Wordpress_Offload_Media_Google( $Access_Key, $Secret_Access_Key );
			break;
		case 'aws':
			$Access_Key = $settings['access_key'];
			$Secret_Access_Key = $settings['secret_access_key'];
			$class = new Leopard_Wordpress_Offload_Media_Aws_Client( $Access_Key, $Secret_Access_Key );
			break;
		case 'DO':
			$Access_Key = $settings['access_key'];
			$Secret_Access_Key = $settings['secret_access_key'];
			$region = isset($settings['region']) ? $settings['region'] : null;
			$class = new Leopard_Wordpress_Offload_Media_DO_Client( $Access_Key, $Secret_Access_Key );
			$class->setRegion($region);
			break;
		default:
			wp_die( esc_html__("Provider not found.", 'leopard-wordpress-offload-media') );
			break;
	}

	return $class;
}

/**
 * Get provider info
 *
 *
 * @return class
 */
function leopard_offload_media_provider_info($provider='', $settings=[]) {
	$upload_dir = wp_upload_dir();
	$basedir_absolute = $upload_dir['basedir'];

	if(empty($provider)){
		$settings = get_option('nou_leopard_offload_media');
		$provider = isset($settings['provider']) ? $settings['provider'] : 'aws';
	}

	$aws_s3_client = leopard_offload_media_provider($provider, $settings);

	$Bucket_Selected = ( get_option( 'nou_leopard_offload_media_connection_bucket_selected_select' ) ? get_option( 'nou_leopard_offload_media_connection_bucket_selected_select' ) : '' );

	if($provider == 'google'){
		$Bucket                = $Bucket_Selected;
		$Region                = 'none';
	}else{
		$Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_Selected );

		if ( count( $Array_Bucket_Selected ) == 2 ){
			$Bucket                = $Array_Bucket_Selected[0];
			$Region                = $Array_Bucket_Selected[1];
		}
		else{
			$Bucket                = 'none';
			$Region                = 'none';
		}
	}

	if ( $Bucket == 'none' )
		return false;

	return array( $aws_s3_client, $Bucket, $Region, $basedir_absolute );
}



function leopard_offload_media_text_actions($action){
	$provider = leopard_offload_media_provider();
	$text = '';
	switch ($action) {
		case 'nou_leopard_wom_copy_to_s3':
			$text = sprintf(esc_html__('Copy to %s', 'leopard-wordpress-offload-media'), $provider::name());
			break;
		case 'nou_leopard_wom_remove_from_server':
			$text = esc_html__('Remove from server', 'leopard-wordpress-offload-media');
			break;
		case 'nou_leopard_wom_copy_to_server_from_s3':
			$text = sprintf(esc_html__('Copy to server from %s', 'leopard-wordpress-offload-media'), $provider::name());
			break;
		case 'nou_leopard_wom_remove_from_s3':
			$text = sprintf(esc_html__('Remove from %s', 'leopard-wordpress-offload-media'), $provider::name());
			break;
		case 'nou_leopard_wom_build_webp':
			$text = esc_html__('Rebuild WebP version', 'leopard-wordpress-offload-media');
			break;
		default:
			# code...
			break;
	}
	return $text;
}

/**
 * Checking connection success to amazon s3
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_check_connection_success() {

	if ( ! get_option( 'nou_leopard_offload_media_connection_success' ) ) {

		echo "<div>";

		echo "<p class='nou_leopard_wom_error_accessing_class'>";

		$Path_warning_image = esc_url(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_URI.'admin/images/Warning.png');

		echo "<img class='nou_leopard_wom_error_accessing_class_img' style='width: 35px;' src='$Path_warning_image'/>";
		echo "<span class='nou_leopard_wom_error_accessing_class_span'>";
		esc_html_e( 'You have to configure your Access Key and Secret Access Key correctly in the "Connect to your s3 amazon account" tab',
            'leopard-wordpress-offload-media' );
		echo "</span>";

		echo "</p>";

		echo "<br>";

		echo "</div>";

		return 0;

	}
	else
	{

		$Bucket_Selected = ( get_option( 'nou_leopard_offload_media_connection_bucket_selected_select' ) ? get_option( 'nou_leopard_offload_media_connection_bucket_selected_select' ) : '' );

		$Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_Selected );

		if ( count( $Array_Bucket_Selected ) != 2 ){

			echo "<div>";

			echo "<p class='nou_leopard_wom_error_accessing_class'>";

			$Path_warning_image = esc_url(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_URI.'admin/images/Warning.png');

			echo "<img class='nou_leopard_wom_error_accessing_class_img' style='width: 35px;' src='$Path_warning_image'/>";
			echo "<span class='nou_leopard_wom_error_accessing_class_span'>";
			esc_html_e( 'You have to choose a bucket in the "Setting" tab in the Amazon S3 admin panel', 'leopard-wordpress-offload-media' );
			echo "</span>";

			echo "</p>";

			echo "<br>";

			echo "</div>";

			return 0;

		}
		else
			return 1;
	}

}

/**
 * Return the default object prefix
 *
 * @return string
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */

function leopard_offload_media_get_default_object_prefix() {
	if ( is_multisite() ) {
		return 'wp-content/uploads/';
	}

	$uploads = wp_upload_dir();
	$parts   = parse_url( $uploads['baseurl'] );
	$path    = ltrim( $parts['path'], '/' );

	return trailingslashit( $path );
}


/**
 * @return array
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_aws_array_media_actions_function( $post_id ) {

	$array_aux = explode( '/', get_post_meta( $post_id, '_wp_attached_file', true ) );
	$main_file = array_pop( $array_aux );

	// Creating an array with all the files with different sizes.
	// The first element of the array is the folder content.
	// Second element is the main file with no personal size
	$array_files[] = implode( "/", $array_aux );
	$array_files[] = $main_file;

	// Getting the rest of the sizes of the file to add to the array
	$array_metadata = wp_get_attachment_metadata( $post_id );

	if ( ! empty( $array_metadata ) && isset( $array_metadata['sizes'] ) )
	{
		$array_metadata = $array_metadata['sizes'];
		foreach ( $array_metadata as $metadata ) {
			$array_files[] = $metadata['file'];
		}
	}

	list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();

	return array( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute );

}

/**
 * Copy to AWS S3
 *
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_copy_to_s3_function( $post_id, $private_or_public = 'public', $data = array() ) {

	list( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute ) = leopard_offload_media_aws_array_media_actions_function( $post_id );

	$attachment_key = get_post_meta( $post_id, '_wp_attached_file', true );

	$accepted_filetypes = get_option('nou_leopard_offload_media_accepted_filetypes', '');
	if(!empty($accepted_filetypes)){
		$types = explode( ',', $accepted_filetypes );
		$extension = substr(strrchr($attachment_key, '.'), 1);
		if(!empty($types) && is_array($types)){
			if(!in_array($extension, $types)){
				return false;
			}
		}
	}

	$array_files = [];
	$array_aux = explode( '/', $attachment_key );
	$main_file = array_pop( $array_aux );

	// Creating an array with all the files with different sizes.
	// The first element of the array is the folder content.
	// Second element is the main file with no personal size
	$array_files[] = implode( "/", $array_aux );
	$array_files[] = $main_file;

	// Getting the rest of the sizes of the file to add to the array
	if(empty($data)){
		$data = wp_get_attachment_metadata( $post_id );
	}

	if ( ! empty( $data ) && isset( $data['sizes'] ) )
	{
		foreach ( $data['sizes'] as $metadata ) {
			$array_files[] = $metadata['file'];
		}
	}

	if ( $result = $aws_s3_client->Upload_Media_File( $Bucket, $Region, $array_files, $basedir_absolute, $private_or_public, '' ) ) {

		$url = isset($result['ObjectURL']) ? $result['ObjectURL'] : $result;
		$bucket_base_url = leopard_offload_media_get_bucket_url() .'/';

		$key = str_replace($bucket_base_url, '', $url);
		$key = str_replace($aws_s3_client->getBucketMainFolder(), '', $key);

		$provider_object = array(
			'provider' => 'aws',
			'region'   => $Region,
			'bucket'   => $Bucket,
			'key' 	   => $key,
			'data'     => $data
		);
		update_post_meta( $post_id, '_nou_leopard_wom_amazonS3_info', $provider_object );
		update_post_meta( $post_id, '_wp_nou_leopard_wom_s3_wordpress_path', '1' );
		update_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', $url );

		do_action( 'leopard_offload_media_copy_file_to_provider', $post_id, $provider_object );

		// Delete file here if you want
		$remove_local_files_setting = get_option('nou_leopard_offload_media_remove_from_server_checkbox');
		if ( $remove_local_files_setting ) {
			leopard_offload_media_remove_from_server_function($post_id, $data);
			do_action( 'leopard_offload_media_after_remove_file_from_server', $post_id, $data, $provider_object );
		}
	}

}

/**
 * Remove from S3
 *
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_remove_from_s3_function( $post_id ) {

	try{
		list( $aws_s3_client, $Bucket, $Region, $array_files ) = leopard_offload_media_aws_array_media_actions_function( $post_id );
		$result = $aws_s3_client->deleteObject_nou( $Bucket, $Region, $array_files );
	}catch(Exception $e){
		error_log($e->getMessage());
	}
	update_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', '_wp_nou_leopard_wom_s3_path_not_in_used' );
}

/**
 * Copy to server from S3
 *
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_copy_to_server_from_s3_function( $post_id ) {

	try{
		list( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute ) = leopard_offload_media_aws_array_media_actions_function( $post_id );

		if ( $result = $aws_s3_client->download_file( $Bucket, $Region, $array_files, $basedir_absolute ) ) {

			update_post_meta( $post_id, '_wp_nou_leopard_wom_s3_wordpress_path', '1' );

		}
	}catch(Exception $e){
		wp_die( $e->getMessage() );
	}

}

/**
 * Remove from server
 *
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_remove_from_server_function( $post_id, $data = array() ) {

	$File_Name = get_post_meta( $post_id, '_wp_attached_file', true );

	$upload_dir = wp_upload_dir();

	$basedir = $upload_dir['basedir'];

	$Path_To_File = $basedir . "/" . $File_Name;

	if(file_exists( $Path_To_File )){
		unlink( $Path_To_File );
	}

	$array_aux = explode( '/', $File_Name );
	$File_Name = array_pop( $array_aux );

	$base_folder = implode( "/", $array_aux );

	// Getting the rest of the sizes of the file to add to the array
	if(empty($data)){
		$array_metadata = wp_get_attachment_metadata( $post_id );
	}else{
		$array_metadata = $data;
	}
	
	if ( ! empty( $array_metadata ) ){
		if(isset($array_metadata['sizes'])){
			$array_metadata = $array_metadata['sizes'];
			$files_to_remove = [];
			foreach ( $array_metadata as $metadata ) {
				if ( $base_folder != '' ) {
					$file_path = $basedir . "/" . $base_folder . "/" . $metadata['file'];
				} else {
					$file_path = $basedir . "/" . $metadata['file'];
				}
				$files_to_remove[] = $file_path;
			}

			// Delete the files and record original file's size before removal.
			leopard_offload_media_remove_local_files( array_unique($files_to_remove), $post_id );
		}
	}
	
	update_post_meta( $post_id, '_wp_nou_leopard_wom_s3_wordpress_path', '_wp_nou_leopard_wom_s3_wordpress_path_not_in_used' );

}

function leopard_offload_media_remove_local_files( $files_to_remove, $post_id ){

	foreach ( $files_to_remove as $path ) {
		// Individual files might still be kept local, but we're still going to count them towards total above.
		
		if ( false !== ( $pre = apply_filters( 'leopard_offload_media_preserve_file_from_local_removal', false, $path ) ) ) {
			continue;
		}

		if ( ! @unlink( $path ) ) {
			$message = esc_html__('Error removing local file ', 'leopard-wordpress-offload-media');

			if ( ! file_exists( $path ) ) {
				$message = esc_html__("Error removing local file. Couldn't find the file at ", 'leopard-wordpress-offload-media');
			} else if ( ! is_writable( $path ) ) {
				$message = esc_html__('Error removing local file. Ownership or permissions are mis-configured for ', 'leopard-wordpress-offload-media');
			}
			error_log( $message . $path );
		}
	}
}

/**
 * Build WebP
 *
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/functions
 */
function leopard_offload_media_build_webp_function( $post_id, $data = array() ) {
	$enable_webp = get_option('nou_leopard_offload_media_webp');
	if($enable_webp){
		$is_permission = get_post_meta( $post_id, 'leopard_downloadable_file_permission', true);
		if(Leopard_Wordpress_Offload_Media_Utils::is_image($post_id) && $is_permission != 'yes'){
			try {
				$webp = new Leopard_Wordpress_Offload_Media_Webp($post_id);
				$webp->do_converts();
			} catch (Exception $e) {
				error_log($e);
			}
		}
	}
}

/**
 * Get the url of the file from Amazon provider
 *
 * @param int         $post_id            Post ID of the attachment
 * @param int|null    $expires            Seconds for the link to live
 * @param string|null $size               Size of the image to get
 * @param array|null  $meta               Pre retrieved _wp_attachment_metadata for the attachment
 * @param array       $headers            Header overrides for request
 * @param bool        $skip_rewrite_check Always return the URL regardless of the 'Rewrite File URLs' setting.
 *                                        Useful for the EDD and Woo addons to not break download URLs when the
 *                                        option is disabled.
 *
 * @return bool|mixed|WP_Error
 */
function leopard_offload_media_get_attachment_url( $post_id, $expires = null, $size = null, $meta = null, $headers = array(), $skip_rewrite_check = false ) {
	$provider_object = leopard_offload_media_is_attachment_served_by_provider( $post_id, $skip_rewrite_check );
	if ( !$provider_object ) {
		return false;
	}

	$url = leopard_offload_media_get_attachment_provider_url( $post_id, $provider_object, $expires, $size, $meta, $headers );

	return apply_filters( 'nou_leopard_wom_wp_get_attachment_url', $url, $post_id );
}

/**
 * Get attachment provider info
 *
 * @param int $post_id
 *
 * @return mixed
 */
function leopard_offload_media_get_attachment_provider_info( $post_id ) {
	$provider_object = get_post_meta( $post_id, '_nou_leopard_wom_amazonS3_info', true );

	if(!nou_leopard_offload_media_is_plugin_setup()){
		return $provider_object;
	}

	list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();
	
	if(!is_array($provider_object)){
		$provider_object = [];
	}

	$key = isset($provider_object['key']) ? $provider_object['key'] : '';
	if(filter_var($key, FILTER_VALIDATE_URL) || empty($key)){
	    $key = get_post_meta( $post_id, '_wp_attached_file', true );
	}
	$key = $aws_s3_client->getBucketMainFolder().$key;
	$provider_object['key'] = $key;
	return apply_filters( 'leopard_offload_media_get_attachment_provider_info', $provider_object, $post_id );
}

function leopard_offload_media_get_bucket_url(){
	$base_url = get_option('nou_leopard_offload_media_aws_connection_bucket_base_url');
	if( filter_var($base_url, FILTER_VALIDATE_URL) ){
	    return $base_url;
	}
	update_option('nou_leopard_offload_media_aws_connection_bucket_base_url', '');
	update_option('nou_leopard_offload_media_connection_bucket_selected_select', '');
	wp_die( esc_html__("Bucket URL is invalid. Please, check again.", 'leopard-wordpress-offload-media') );
}
/**
 * Is attachment served by provider.
 *
 * @param int           $attachment_id
 * @param bool          $skip_rewrite_check          Still check if offloaded even if not currently rewriting URLs? Default: false
 * @param bool          $skip_current_provider_check Skip checking if offloaded to current provider. Default: false, negated if $provider supplied
 * @param Provider|null $provider                    Provider where attachment expected to be offloaded to. Default: currently configured provider
 *
 * @return bool|array
 */
function leopard_offload_media_is_attachment_served_by_provider( $attachment_id, $skip_rewrite_check = false, $skip_current_provider_check = false, $provider = 'aws' ) {
	
	if ( ! ( $provider_object = leopard_offload_media_get_attachment_provider_info( $attachment_id ) ) ) {
		// File not uploaded to a provider
		return false;
	}

	return $provider_object;
}

/**
 * Convert dimensions to size
 *
 * @param int   $attachment_id
 * @param array $dimensions
 *
 * @return null|string
 */
function leopard_offload_media_convert_dimensions_to_size_name( $attachment_id, $dimensions ) {
	$w                     = ( isset( $dimensions[0] ) && $dimensions[0] > 0 ) ? $dimensions[0] : 1;
	$h                     = ( isset( $dimensions[1] ) && $dimensions[1] > 0 ) ? $dimensions[1] : 1;
	$original_aspect_ratio = $w / $h;
	$meta                  = wp_get_attachment_metadata( $attachment_id );

	if ( ! isset( $meta['sizes'] ) || empty( $meta['sizes'] ) ) {
		$data = get_post_meta($attachment_id, '_nou_leopard_wom_amazonS3_info', true);
		if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
			if(isset($data['data']['sizes'])){
				$meta = $data['data'];
			}else{
				return null;
			}
		}else{
			return null;
		}
	}
	
	if(empty($meta)){
		return null;
	}

	$sizes = $meta['sizes'];
	uasort( $sizes, function ( $a, $b ) {
		// Order by image area
		return ( $a['width'] * $a['height'] ) - ( $b['width'] * $b['height'] );
	} );

	$nearest_matches = array();

	foreach ( $sizes as $size => $value ) {
		if ( $w > $value['width'] || $h > $value['height'] ) {
			continue;
		}

		$aspect_ratio = $value['width'] / $value['height'];

		if ( $aspect_ratio === $original_aspect_ratio ) {
			return $size;
		}

		$nearest_matches[] = $size;
	}

	// Return nearest match
	if ( ! empty( $nearest_matches ) ) {
		return $nearest_matches[0];
	}

	return null;
}

/**
 * Return the scheme to be used in URLs
 *
 * @param bool $use_ssl
 *
 * @return string
 */
function leopard_offload_media_get_url_scheme( $use_ssl = true ) {
	if ( $use_ssl ) {
		$scheme = 'https';
	} else {
		$scheme = 'http';
	}

	return $scheme;
}

/**
 * Maybe convert size to string
 *
 * @param int   $attachment_id
 * @param mixed $size
 *
 * @return null|string
 */
function leopard_offload_media_maybe_convert_size_to_string( $attachment_id, $size ) {
	if ( is_array( $size ) ) {
		return leopard_offload_media_convert_dimensions_to_size_name( $attachment_id, $size );
	}

	return $size;
}

/**
 * Potentially update path for CloudFront URLs.
 *
 * @param string $path
 *
 * @return string
 */
function leopard_wordpress_offload_media_maybe_update_cloudfront_path( $path ) {
	if(!nou_leopard_offload_media_enable_rewrite_urls()){
		return $path;
	}

	$cloudfront = get_option('nou_leopard_offload_media_cname');
	if ( $cloudfront ) {
		$path_parts = apply_filters( 'nou_leopard_offload_media_cloudfront_path_parts', explode( '/', $path ), $cloudfront );

		if ( ! empty( $path_parts ) ) {
			$path = implode( '/', $path_parts );
		}
	}
	return $path;
}

/**
 * Format S3 to CloudFront URLs.
 *
 * @param string $url
 *
 * @return string
 */
function leopard_wordpress_offload_media_s3_to_cloudfront_url( $url, $bucket_base_url='', $only_rewrite_assets=false ) {
	if(!$only_rewrite_assets){
		if(!nou_leopard_offload_media_enable_rewrite_urls()){
			return $url;
		}
	}
	
	$private_url = 'no';
	$domain = parse_url($url);
	$url_private = isset($domain['path']) ? $domain['path'] : '';
	if(!empty($url_private)){
		$url_private = ltrim($url_private, '/');
		$attachment_id = leopard_wordpress_offload_media_get_post_id($url_private);
		if($attachment_id){
			$private_url = get_post_meta($attachment_id, 'leopard_downloadable_file_permission', true);
		}
	}

	$cloudfront = get_option('nou_leopard_offload_media_cname');
	if ( !empty($cloudfront) && $private_url != 'yes' ) {
		$base_url = empty($bucket_base_url) ? leopard_offload_media_get_bucket_url() : $bucket_base_url;
		$base_domain = str_replace('https://', '', $base_url);
		$url = str_replace($base_domain, $cloudfront, $url);
	}

	$force_https = get_option('nou_leopard_offload_media_force_https_checkbox');
	if($force_https){
		$url = str_replace('http://', 'https://', $url);
	}
	
	return $url;
}

/**
 * Get the provider URL for an attachment
 *
 * @param int               $post_id
 * @param array             $provider_object
 * @param null|int          $expires
 * @param null|string|array $size
 * @param null|array        $meta
 * @param array             $headers
 *
 * @return mixed|WP_Error
 */
function leopard_offload_media_get_attachment_provider_url( $post_id, $provider_object, $expires = null, $size = null, $meta = null, $headers = array() ) {
	// We don't use $this->get_provider_object_region() here because we don't want
	// to make an AWS API call and slow down page loading
	if ( isset( $provider_object['region'] ) ) {
		$region = $provider_object['region'];
	} else {
		$region = '';
	}

	$size = leopard_offload_media_maybe_convert_size_to_string( $post_id, $size );

	// Force use of secured URL when ACL has been set to private
	if ( !is_null( $expires ) ) {
		$expires  = time() + apply_filters( 'nou_leopard_offload_media_expires', $expires );
	}

	if ( ! is_null( $size ) ) {
		if ( is_null( $meta ) ) {
			$meta = get_post_meta( $post_id, '_wp_attachment_metadata', true );
			if ( ! isset( $meta['sizes'] ) || empty( $meta['sizes'] ) ) {
				$data = get_post_meta($post_id, '_nou_leopard_wom_amazonS3_info', true);
				if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
					$meta = $data['data'];
				}
			}
		}

		if ( is_wp_error( $meta ) ) {
			return $meta;
		}

		if ( ! empty( $meta ) && isset( $meta['sizes'][ $size ]['file'] ) ) {
			$size_prefix      = dirname( $provider_object['key'] );
			$size_file_prefix = ( '.' === $size_prefix ) ? '' : $size_prefix . '/';

			$provider_object['key'] = $size_file_prefix.$meta['sizes'][ $size ]['file'];
		}
	}

	if ( !nou_leopard_offload_media_is_plugin_setup() ) {
		return $meta;
	}

	try {
		
		list( $aws_s3_client, $Bucket, $Region, $array_files, $basedir_absolute ) = leopard_offload_media_aws_array_media_actions_function( $post_id );
		$provider_object['key'] = leopard_wordpress_offload_media_maybe_update_cloudfront_path( $provider_object['key'] );
		$secure_url = $aws_s3_client->getObjectUrl( $Bucket, $Region, $provider_object['key']);
		return apply_filters( 'nou_leopard_offload_media_get_attachment_secure_url', $secure_url, $provider_object, $post_id, $headers );
	} catch ( Exception $e ) {
		return new WP_Error( 'exception', $e->getMessage() );
	}

}


/**
 * Maybe remove query string from URL.
 *
 * @param string $url
 *
 * @return string
 */
function nou_leopard_offload_media_maybe_remove_query_string( $url ) {
	$parts = explode( '?', $url );

	return reset( $parts );
}


/**
 * Helper to switch to a Multisite blog
 *  - If the site is MS
 *  - If the blog is not the current blog defined
 *
 * @param int|bool $blog_id
 */
function nou_leopard_offload_media_switch_to_blog( $blog_id = false ) {
	if ( ! is_multisite() ) {
		return;
	}

	if ( ! $blog_id ) {
		$blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 1;
	}

	if ( $blog_id !== get_current_blog_id() ) {
		switch_to_blog( $blog_id );
	}
}

/**
 * Helper to restore to the current Multisite blog
 */
function nou_leopard_offload_media_restore_current_blog() {
	if ( is_multisite() ) {
		restore_current_blog();
	}
}


/**
 * Is the current blog ID that specified in wp-config.php
 *
 * @param int $blog_id
 *
 * @return bool
 */
function nou_leopard_offload_media_is_current_blog( $blog_id ) {
	$default = defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 1;

	if ( $default === $blog_id ) {
		return true;
	}

	return false;
}

/**
 * Encode file names according to RFC 3986 when generating urls
 * As per Amazon https://forums.aws.amazon.com/thread.jspa?threadID=55746#jive-message-244233
 *
 * @param string $file
 *
 * @return string Encoded filename
 */
function nou_leopard_offload_media_encode_filename_in_path( $file ) {
	$url = parse_url( $file );

	if ( ! isset( $url['path'] ) ) {
		// Can't determine path, return original
		return $file;
	}

	if ( isset( $url['query'] ) ) {
		// Manually strip query string, as passing $url['path'] to basename results in corrupt � characters
		$file_name = wp_basename( str_replace( '?' . $url['query'], '', $file ) );
	} else {
		$file_name = wp_basename( $file );
	}

	if ( false !== strpos( $file_name, '%' ) ) {
		// File name already encoded, return original
		return $file;
	}

	$encoded_file_name = rawurlencode( $file_name );

	if ( $file_name === $encoded_file_name ) {
		// File name doesn't need encoding, return original
		return $file;
	}

	return str_replace( $file_name, $encoded_file_name, $file );
}

/**
 * Decode file name.
 *
 * @param string $file
 *
 * @return string
 */
function nou_leopard_offload_media_decode_filename_in_path( $file ) {
	$url = parse_url( $file );

	if ( ! isset( $url['path'] ) ) {
		// Can't determine path, return original
		return $file;
	}

	$file_name = wp_basename( $url['path'] );

	if ( false === strpos( $file_name, '%' ) ) {
		// File name not encoded, return original
		return $file;
	}

	$decoded_file_name = rawurldecode( $file_name );

	return str_replace( $file_name, $decoded_file_name, $file );
}


/**
 * Ensure local URL is correct for multisite's non-primary subsites.
 *
 * @param string $url
 *
 * @return string
 */
function nou_leopard_offload_media_maybe_fix_local_subsite_url( $url ) {
	$siteurl = trailingslashit( get_option( 'siteurl' ) );

	if ( is_multisite() && ! nou_leopard_offload_media_is_current_blog( get_current_blog_id() ) && 0 !== strpos( $url, $siteurl ) ) {
		// Replace network URL with subsite's URL.
		$network_siteurl = trailingslashit( network_site_url() );
		$url             = str_replace( $network_siteurl, $siteurl, $url );
	}

	return $url;
}

/**
 * Get attachment local URL.
 *
 * @param int $post_id
 *
 * @return string|false Attachment URL, otherwise false.
 */
function nou_leopard_offload_media_get_attachment_local_url( $post_id ) {
	$url = '';

	// Get attached file.
	if ( $file = get_post_meta( $post_id, '_wp_attached_file', true ) ) {
		// Get upload directory.
		if ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) {
			// Check that the upload base exists in the file location.
			if ( 0 === strpos( $file, $uploads['basedir'] ) ) {
				// Replace file location with url location.
				$url = str_replace( $uploads['basedir'], $uploads['baseurl'], $file );
			} elseif ( false !== strpos( $file, 'wp-content/uploads' ) ) {
				$url = $uploads['baseurl'] . substr( $file, strpos( $file, 'wp-content/uploads' ) + 18 );
			} else {
				// It's a newly-uploaded file, therefore $file is relative to the basedir.
				$url = $uploads['baseurl'] . "/$file";
			}
		}
	}

	if ( empty( $url ) ) {
		return false;
	}

	$url = nou_leopard_offload_media_maybe_fix_local_subsite_url( $url );

	return $url;
}

/**
 * Get attachment local URL size.
 *
 * @param int         $post_id
 * @param string|null $size
 *
 * @return false|string
 */
function nou_leopard_offload_media_get_attachment_local_url_size( $post_id, $size = null ) {
	$url = nou_leopard_offload_media_get_attachment_local_url( $post_id );

	if ( empty( $size ) ) {
		return $url;
	}

	$meta = get_post_meta( $post_id, '_wp_attachment_metadata', true );

	if ( empty( $meta['sizes'][ $size ]['file'] ) ) {
		// No alternative sizes available, return
		return $url;
	}

	return str_replace( wp_basename( $url ), $meta['sizes'][ $size ]['file'], $url );
}


/**
 * Do Bulk Action.
 *
 * @param int         $post_id
 * @param string|null $size
 *
 * @return false|string
 */
function nou_leopard_offload_media_do_bulk_actions_extra_options_function( $doaction, $post_ids ) {
	
	switch ( $doaction ) {

		case 'nou_leopard_wom_copy_to_s3':
			$radio_private_or_public = get_option('nou_leopard_offload_media_private_public_radio_button', 'public');
			foreach ( $post_ids as $post_id ) {
				$s3_path = get_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', true );
				if ( $s3_path == '_wp_nou_leopard_wom_s3_path_not_in_used' || $s3_path == null ) {
					leopard_offload_media_copy_to_s3_function( $post_id, $radio_private_or_public );
				}
			}

			break;

		case 'nou_leopard_wom_remove_from_s3':
			foreach ( $post_ids as $post_id ) {
				leopard_offload_media_remove_from_s3_function( $post_id );
			}

			break;

		case 'nou_leopard_wom_copy_to_server_from_s3':
			
			foreach ( $post_ids as $post_id ) {
				leopard_offload_media_copy_to_server_from_s3_function( $post_id );
			}

			break;

		case 'nou_leopard_wom_remove_from_server':
			
			foreach ( $post_ids as $post_id ) {
				leopard_offload_media_remove_from_server_function( $post_id );
			}

			break;

		case 'nou_leopard_wom_build_webp':
			
			foreach ( $post_ids as $post_id ) {
				leopard_offload_media_build_webp_function( $post_id );
			}

			break;
	}

}

/**
 * Sign intermediate size.
 *
 * @param string       $url
 * @param int          $attachment_id
 * @param string|array $size
 * @param bool|array   $provider_object
 *
 * @return mixed|WP_Error
 */
function leopard_offload_media_maybe_sign_intermediate_size( $url, $attachment_id, $size, $provider_object = false ) {
	if ( ! $provider_object ) {
		$provider_object = leopard_offload_media_get_attachment_provider_info( $attachment_id );
	}

	$size = leopard_offload_media_maybe_convert_size_to_string( $attachment_id, $size );

	if ( isset( $provider_object['sizes'][ $size ] ) ) {
		// Private file, add AWS signature if required
		return leopard_offload_media_get_attachment_provider_url( $attachment_id, $provider_object, null, $size );
	}

	return $url;
}

function leopard_wordpress_offload_media_get_provider_service_name($key_name){
	switch ($key_name) {
		case 'google':
			$name = esc_html__('Google Cloud Storage', 'leopard-wordpress-offload-media');
			break;
		case 'wasabi':
			$name = esc_html__('Wasabi', 'leopard-wordpress-offload-media');
			break;
		case 'DO':
			$name = esc_html__('DigitalOcean Spaces', 'leopard-wordpress-offload-media');
			break;
		
		default:
			$name = esc_html__('Amazon S3', 'leopard-wordpress-offload-media');
			break;
	}
	return $name;
}

/**
 * Return a formatted S3 info with display friendly defaults
 *
 * @param int        $id
 * @param array|null $provider_object
 *
 * @return array
 */
function leopard_wordpress_offload_media_get_formatted_provider_info( $id, $provider_object = null ) {
	if ( is_null( $provider_object ) ) {
		if ( ! ( $provider_object = leopard_offload_media_get_attachment_provider_info( $id ) ) ) {
			return false;
		}
	}

	$provider_object['url'] = leopard_offload_media_get_attachment_provider_url( $id, $provider_object );

	if ( ! empty( $provider_object['provider'] ) ) {
		$provider_object['provider_name'] = leopard_wordpress_offload_media_get_provider_service_name($provider_object['provider']);
	}

	return $provider_object;
}

function leopard_wordpress_offload_media_row_actions_extra( $actions, $post_id ) {

	if ( nou_leopard_offload_media_is_plugin_setup() ) {

		$wordpress_path = get_post_meta( $post_id, '_wp_nou_leopard_wom_s3_wordpress_path', true );
		$s3_path        = get_post_meta( $post_id, '_wp_nou_leopard_wom_s3_path', true );
		$is_permission = get_post_meta( $post_id, 'leopard_downloadable_file_permission', true);
		// Show the copy to s3 link if the file is not in S3
		if ( $s3_path == '_wp_nou_leopard_wom_s3_path_not_in_used' || $s3_path == null ) {
			$actions['nou_leopard_wom_copy_to_s3'] = '<a href="post.php?post=' . esc_attr($post_id) . '&action=nou_leopard_wom_copy_to_s3">'.leopard_offload_media_text_actions('nou_leopard_wom_copy_to_s3').'</a>';
		}

		// Remove the file from the server if it is in both places (wordpress installation and S3) otherwise user will click in "delete permanently"
		if ( ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) && ( $wordpress_path != '_wp_nou_leopard_wom_s3_wordpress_path_not_in_used' && $wordpress_path != null ) ) {
			$actions['nou_leopard_wom_remove_from_server'] = '<a href="post.php?post=' . esc_attr($post_id) . '&action=nou_leopard_wom_remove_from_server">'.leopard_offload_media_text_actions('nou_leopard_wom_remove_from_server').'</a>';
		}

		// Show the copy to server from S3 link if the file is not in the server and it is in S3
		if ( ( $wordpress_path == '_wp_nou_leopard_wom_s3_wordpress_path_not_in_used' || $wordpress_path == null ) && ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) ) {
			$actions['nou_leopard_wom_copy_to_server_from_s3'] = '<a href="post.php?post=' . esc_attr($post_id) . '&action=nou_leopard_wom_copy_to_server_from_s3">'.leopard_offload_media_text_actions('nou_leopard_wom_copy_to_server_from_s3').'</a>';
		}

		// Remove the file from S3 if it is in both places (wordpress installation and S3) otherwise user will click in "delete permanently"
		if ( ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) && ( $wordpress_path != '_wp_nou_leopard_wom_s3_wordpress_path_not_in_used' && $wordpress_path != null ) ) {
			$actions['nou_leopard_wom_remove_from_s3'] = '<a href="post.php?post=' . esc_attr($post_id) . '&action=nou_leopard_wom_remove_from_s3">'.leopard_offload_media_text_actions('nou_leopard_wom_remove_from_s3').'</a>';
		}

		// Build WebP
		$enable_webp = get_option('nou_leopard_offload_media_webp');
		if($enable_webp){
			if ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null && Leopard_Wordpress_Offload_Media_Utils::is_image($post_id) && $is_permission != 'yes' ) {
				$actions['nou_leopard_wom_build_webp'] = '<a href="post.php?post=' . esc_attr($post_id) . '&action=nou_leopard_wom_build_webp">'.leopard_offload_media_text_actions('nou_leopard_wom_build_webp').'</a>';
			}
		}
	}

	return $actions;

}

function leopard_wordpress_offload_media_bucket_base_url() {

	$Bucket_Selected = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
	
	$aws_s3_client = leopard_offload_media_provider();
	if($aws_s3_client::identifier() == 'google'){
		$Base_url = $aws_s3_client->get_base_url(  $Bucket_Selected, null, null );
	}else{

		$Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_Selected );

        if ( count( $Array_Bucket_Selected ) == 2 ){
            $Bucket                = $Array_Bucket_Selected[0];
            $Region                = $Array_Bucket_Selected[1];
        }
        else{
            $Bucket                = 'none';
            $Region                = 'none';
        }

        if($aws_s3_client::identifier() == 'DO'){
        	$Base_url = $aws_s3_client->get_base_url( $Bucket, $Region, '' );
        }else{
        	$result = $aws_s3_client->delete_Objects_no_base_folder_nou( $Bucket, $Region, array( '5a90320d39a72_nou_wc_as3s_5a90320d39a8a.txt', '5a902e5124a80_nou_wc_as3s_5a902e5124a86.txt', '5a902be279c34_nou_wc_as3s_5a902be279c3btxt' ) );

	        $Keyname = uniqid() . '_nou_wc_as3s_' . uniqid() . '.txt';

	        $Base_url = $aws_s3_client->get_base_url( $Bucket, $Region, $Keyname );

	        $result = $aws_s3_client->delete_Objects_no_base_folder_nou( $Bucket, $Region, array( $Keyname ) );
        }
    }

    update_option( 'nou_leopard_offload_media_aws_connection_bucket_base_url', $Base_url );

}

function leopard_wordpress_offload_media_clone_option($from_option_key, $to_option_key) {
	$from_option = get_option($from_option_key);
	if(!empty($from_option)){
		update_option($to_option_key, $from_option);
	}
}

function leopard_wordpress_offload_media_get_post_id($old_url) {
	
	if(strpos($old_url, '.js') !== false){
		return false;
	}

	if(strpos($old_url, '.css') !== false){
		return false;
	}

	if(strpos($old_url, '.webp') !== false){
		$old_url = str_replace('.webp', '', $old_url);
	}

	$url = Leopard_Wordpress_Offload_Media_Utils::get_key_from_url($old_url);

	$key = 'leopard_get_post_id_'.wp_hash($url, 'nonce');
	if ( false === ( $data = get_transient( $key ) ) ) {
		global $wpdb;
		$meta = $wpdb->get_row("SELECT post_id FROM `".$wpdb->postmeta."` WHERE meta_key='_wp_attached_file' AND meta_value='".esc_sql($url)."'");
		if (is_object($meta)) {
			$data = $meta->post_id;
			set_transient( $key, $data, 60*60*24 );
		}
	}
	
	return $data;
}

function leopard_wordpress_offload_media_calculator_sync_processed() {
	$processed = count(get_option('nou_leopard_offload_media_synced_data', []));
	if($processed == 0){
		return 0;
	}
	$total = count(get_option('nou_leopard_offload_media_scaned_sync_data'));
	if($total == 0){
		return 0;
	}
	return round($processed / $total * 100);
}


function leopard_wordpress_offload_media_has_backup_option() {
	$default = get_option('nou_leopard_offload_media');
	$default_bk = get_option('nou_leopard_offload_media_backup');
	$has_backup = false;
	if(!empty($default_bk) && $default_bk['provider'] != $default['provider']){
		$has_backup = true;
	}

	$bucket = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
	$bucket_bk = get_option('nou_leopard_offload_media_connection_bucket_selected_select_backup');

	if(!empty($bucket_bk) && $bucket != $bucket_bk){
		$has_backup = true;
	}
	return $has_backup;
}

function leopard_wordpress_offload_media_get_real_provider($post_id){
	$provider_object = leopard_offload_media_get_attachment_provider_info($post_id);
	$settings = get_option('nou_leopard_offload_media');
	$provider = isset($settings['provider']) ? $settings['provider'] : 'aws';
	list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();
	$base_url = leopard_offload_media_get_bucket_url();

	if(!is_array($provider_object)){
		$provider_object = [];
	}

	$key = isset($provider_object['key']) ? $provider_object['key'] : '';
	
	return array(
		'provider' 			=> $provider,
		'provider_name' 	=> leopard_wordpress_offload_media_get_provider_service_name($provider),
		'region'   			=> $Region,
		'bucket'   			=> $Bucket,
		'base_url'  		=> $base_url.'/'.$key,
		'key' 	   			=> isset($provider_object['key']) ? $provider_object['key'] : '',
		'data'     			=> isset($provider_object['data']) ? $provider_object['data'] : []
	);
}

function leopard_wordpress_offload_media_get_real_url($current_url){
	$upload_base_urls = Leopard_Wordpress_Offload_Media_Utils::get_bare_upload_base_urls();
	if ( str_replace( $upload_base_urls, '', $current_url ) === $current_url ) {
		// Remote host
		$domain = parse_url($current_url);
		$path = isset($domain['path']) ? $domain['path'] : '';
		if(!empty($path)){
			$base_url = leopard_offload_media_get_bucket_url();
			$Bucket_Selected = get_option('nou_leopard_offload_media_connection_bucket_selected_select');
	
			$aws_s3_client = leopard_offload_media_provider();
			$Bucket = $Bucket_Selected;
			if($aws_s3_client::identifier() != 'google'){
				$Array_Bucket_Selected = explode( "_nou_wc_as3s_separator_", $Bucket_Selected );

		        if ( count( $Array_Bucket_Selected ) == 2 ){
		            $Bucket = $Array_Bucket_Selected[0];
		        }else{
		            $Bucket = 'none';
		        }
		    }

		    if($Bucket !== 'none'){
		    	if(strpos($path, $Bucket.'/') !== false){
		    		$path = str_replace($Bucket.'/', '', $path);
		    	}
		    }

			return $base_url.$path;
		}
	}
	return $current_url;
}

function leopard_wordpress_offload_media_get_url_from_key($key){
	list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();
	$base_url = leopard_offload_media_get_bucket_url();

	$folder_main = get_option('nou_leopard_offload_media_bucket_folder_main', '');
    if(!empty($folder_main)){
    	if(substr($folder_main, -1) == '/') {
            $folder_main = substr($folder_main, 0, -1);
        }
        if(strpos($key, $folder_main) !== false){
        	$key = str_replace($folder_main.'/', '', $key);
        }
    }

	$key = $aws_s3_client->getBucketMainFolder().$key;
	$provider_url = $base_url.'/'.$key;
	return leopard_wordpress_offload_media_s3_to_cloudfront_url($provider_url);
}
?>