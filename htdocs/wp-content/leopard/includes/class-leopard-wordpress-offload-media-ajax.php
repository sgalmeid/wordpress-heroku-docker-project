<?php

/**
 * AJAX
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 * @author     Nouthemes <nguyenvanqui89@gmail.com>
 */

class Leopard_Wordpress_Offload_Media_Ajax {

	public $file_css = '';

	public function __construct() {
		add_action( 'wp_ajax_nou_leopard_offload_media_storage_input_hidden_path_file_to_uploaded', array($this, 'nou_leopard_offload_media_storage_input_hidden_path_file_to_uploaded') );

		add_action( 'wp_ajax_nou_leopard_offload_media_library_button_action_mode_grid', array($this, 'media_library_button_action_mode_grid') );

		add_action( 'wp_ajax_nou_leopard_offload_media_get_attachment_provider_details', array($this, 'get_attachment_provider_details') );

		add_action( 'wp_ajax_nou_leopard_offload_media_scan_assets', array($this, 'scan_assets') );
		add_action( 'wp_ajax_nou_leopard_offload_media_upload_assets', array($this, 'upload_assets') );
		
		add_action( 'wp_ajax_nou_leopard_offload_media_form_create_bucket', array($this, 'form_create_bucket') );
		add_action( 'wp_ajax_nou_leopard_offload_media_create_bucket', array($this, 'create_bucket') );

		add_action( 'wp_ajax_nou_leopard_offload_media_render_cloud_files', array($this, 'render_cloud_files') );

		add_action( 'wp_ajax_nou_leopard_offload_media_set_permission_object', array($this, 'set_permission_object') );

		add_action( 'wp_ajax_nou_leopard_offload_media_scaned_sync_data', array($this, 'scaned_sync_data') );

		add_action( 'wp_ajax_nou_leopard_offload_media_scan_attachments', array($this, 'scan_attachments') );
		add_action( 'wp_ajax_nou_leopard_offload_media_download_all_files', array($this, 'download_all_files') );
		add_action( 'wp_ajax_nou_leopard_offload_media_remove_all_files_from_bucket', array($this, 'remove_all_files_from_bucket') );
		add_action( 'wp_ajax_nou_leopard_offload_media_remove_all_files_from_server', array($this, 'remove_all_files_from_server') );
		add_action( 'wp_ajax_nou_leopard_offload_media_copy_all_files_to_bucket', array($this, 'copy_all_files_to_bucket') );
		add_action( 'wp_ajax_nou_leopard_offload_media_copy_all_files_to_bucket_kill_process', array($this, 'copy_all_files_to_bucket_kill_process') );
		
		add_action( 'wp_ajax_nou_leopard_offload_media_sync_render_form', array($this, 'sync_render_form') );
		add_action( 'wp_ajax_nou_leopard_offload_media_sync_render_bucket_form', array($this, 'sync_render_bucket_form') );
		add_action( 'wp_ajax_nou_leopard_offload_media_sync_update_bucket_selected', array($this, 'sync_update_bucket_selected') );

		add_action( 'wp_ajax_nou_leopard_offload_media_report_sync_data', array($this, 'report_sync_data') );
		add_action( 'wp_ajax_nou_leopard_offload_media_copy_all_files_to_bucket_check_process', array($this, 'copy_all_files_to_bucket_check_process') );

	}

	public function report_sync_data(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			$percen = leopard_wordpress_offload_media_calculator_sync_processed();
			$message = $percen;
			if($percen > 100){
				$message = '100';
			}
			$sync_done = get_option('nou_leopard_offload_media_sync_data', '0');
			wp_send_json_success( 
		    	array(
		    		'status' => 'success',
		    		'sync' => $sync_done,
		    		'message' => $message
		    	) 
		    );
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function scaned_sync_data(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			
			update_option('nou_leopard_offload_media_sync_data', 0);
			update_option('nou_leopard_offload_media_synced_data', []);

			try{
				$sync = new Leopard_Wordpress_Offload_Media_Sync();
				$sync->setBackupData();
				$sync->setObjects();
				$objects = $sync->getObjects();
				$data = [];
				
				$background_process = new Leopard_Wordpress_Offload_Media_Sync_Process();
				// First lets cancel existing running queue to avoid running it more than once.
				$background_process->kill_process();
					
				if(!empty($objects)){
					update_option('nou_leopard_offload_media_synced_status', 1);
					foreach ($objects as $key => $value) {
						$item = [
							'source' 	=> $key,
							'key' 		=> $value
						];
						$data[] = $item;
						$background_process->push_to_queue($item);
					}

					// Lets dispatch the queue to start processing.
					$background_process->save()->dispatch();
				}

				update_option('nou_leopard_offload_media_scaned_sync_data', $data);

				$message = '';
				if(count($data) == 0){
					$message = esc_html__('Files not found!', 'leopard-wordpress-offload-media');
				}
				wp_send_json_success( 
			    	array(
			    		'status' => 'success',
			    		'message' => $message,
			    		'count' => count($data)
			    	) 
			    );
			} catch (Exception $e){
				update_option('nou_leopard_offload_media_scaned_sync_data', []);
				update_option('nou_leopard_offload_media_sync_data', 0);
				update_option('nou_leopard_offload_media_synced_data', []);
				$message = (string) $e->getMessage();
				wp_send_json_error( array( 'status' => 'fail', 'message' => $message ));
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function sync_update_bucket_selected(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				$type  = ( isset( $_POST['type'] ) ? $_POST['type'] : '' );
				$Bucket_Selected = ( isset( $_POST['bucket'] ) ? $_POST['bucket'] : '' );

				if($type == 'from'){
					$provider = get_option('nou_leopard_offload_media_sync_provider_from');
					$settings = get_option('nou_leopard_offload_media_sync_settings_from');
					update_option('nou_leopard_offload_media_sync_bucket_from', $Bucket_Selected);
				}else{
					$provider = get_option('nou_leopard_offload_media_sync_provider_to');
					$settings = get_option('nou_leopard_offload_media_sync_settings_to');
					update_option('nou_leopard_offload_media_sync_bucket_to', $Bucket_Selected);
				}

				$aws_s3_client = leopard_offload_media_provider($provider, $settings);

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

					$result = $aws_s3_client->delete_Objects_no_base_folder_nou( $Bucket, $Region, array( '5a90320d39a72_nou_wc_as3s_5a90320d39a8a.txt', '5a902e5124a80_nou_wc_as3s_5a902e5124a86.txt', '5a902be279c34_nou_wc_as3s_5a902be279c3btxt' ) );

			        $Keyname = uniqid() . '_nou_wc_as3s_' . uniqid() . '.txt';

			        $Base_url = $aws_s3_client->get_base_url( $Bucket, $Region, $Keyname );

			        $result = $aws_s3_client->delete_Objects_no_base_folder_nou( $Bucket, $Region, array( $Keyname ) );
			    }

			    if($type == 'from'){
					update_option('nou_leopard_offload_media_sync_bucket_base_url_from', $Base_url);
				}else{
					update_option('nou_leopard_offload_media_sync_bucket_base_url_to', $Base_url);
				}

				$bucket_from = get_option('nou_leopard_offload_media_sync_bucket_from');
				$bucket_to = get_option('nou_leopard_offload_media_sync_bucket_to');
				if(!empty($bucket_from) && !empty($bucket_to)){
					update_option('nou_leopard_offload_media_scaned_sync_data', []);
					wp_send_json_success( 
				    	array(
				    		'status' => 'done',
				    		'message' => ''
				    	) 
				    );
				}

				wp_send_json_success( 
			    	array(
			    		'status' => 'continue',
			    		'message' => ''
			    	) 
			    );

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function sync_render_bucket_form(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				$type  = ( isset( $_POST['type'] ) ? $_POST['type'] : '' );
				$provider  = ( isset( $_POST['provider'] ) ? $_POST['provider'] : '' );
				$region  = ( isset( $_POST['region'] ) ? $_POST['region'] : 'nyc3' );
				$access_key  = ( isset( $_POST['access_key'] ) ? $_POST['access_key'] : '' );
				$secret_access_key  = ( isset( $_POST['secret_access_key'] ) ? $_POST['secret_access_key'] : '' );
				$credentials  = ( isset( $_POST['credentials_key'] ) ? $_POST['credentials_key'] : '' );

				$settings = [
					'access_key' => $access_key,
					'secret_access_key' => $secret_access_key,
					'credentials_key' => json_decode(stripslashes($credentials), true),
					'region' => $region
				];
				
				if($type == 'from'){
					update_option('nou_leopard_offload_media_sync_provider_from', $provider);
					update_option('nou_leopard_offload_media_sync_settings_from', $settings);
					update_option('nou_leopard_offload_media_bucket_regional_from', $region);
				}else{
					update_option('nou_leopard_offload_media_sync_provider_to', $provider);
					update_option('nou_leopard_offload_media_sync_settings_to', $settings);
					update_option('nou_leopard_offload_media_bucket_regional_to', $region);
				}

				$client = leopard_offload_media_provider($provider, $settings);
				$sync_type = get_option('nou_leopard_offload_media_sync_type');
				ob_start();
					?>
					<p class="nou_leopard_wom_admin_parent_wrap">
						<label>
							<span class="nou_leopard_wom_title">
								<?php if($sync_type == 'bucket'){?>
									<?php esc_html_e('From bucket', 'leopard-wordpress-offload-media');?>
								<?php }else{?>
									<?php esc_html_e('Select bucket', 'leopard-wordpress-offload-media');?>
								<?php }?>
							</span>
							<select data-target="<?php echo esc_attr($type);?>" class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_connection_bucket_<?php echo esc_attr($type);?>" tabindex="-1" aria-hidden="true"><?php echo $client->Show_Buckets('no');?></select>
						</label>
					</p>
					<?php if($sync_type == 'bucket'){?>
					<p class="nou_leopard_wom_admin_parent_wrap">
						<label>
							<span class="nou_leopard_wom_title"><?php esc_html_e('To bucket', 'leopard-wordpress-offload-media');?></span>
							<select data-target="to" class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_connection_bucket_to" tabindex="-1" aria-hidden="true"><?php echo $client->Show_Buckets('no');?></select>
						</label>
					</p>
					<?php }?>
					<?php
				$html = ob_get_clean();
				update_option('nou_leopard_offload_media_scaned_sync_data', []);
				wp_send_json_success( 
			    	array(
			    		'status' => 'success', 
			    		'html' => $html,
			    		'message' => ''
			    	) 
			    );

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function sync_render_form(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				$type  = ( isset( $_POST['type'] ) ? $_POST['type'] : 'cloud' );
				
				update_option('nou_leopard_offload_media_sync_type', $type);

				update_option('nou_leopard_offload_media_sync_provider_from', '');
				update_option('nou_leopard_offload_media_sync_settings_from', []);
				update_option('nou_leopard_offload_media_sync_bucket_from', '');
				update_option('nou_leopard_offload_media_sync_bucket_base_url_from', '');

				update_option('nou_leopard_offload_media_sync_provider_to', '');
				update_option('nou_leopard_offload_media_sync_settings_to', []);
				update_option('nou_leopard_offload_media_sync_bucket_to', '');
				update_option('nou_leopard_offload_media_sync_bucket_base_url_to', '');

				update_option('nou_leopard_offload_media_scaned_sync_data', []);

				ob_start();
					nou_leopard_offload_media_load_template('admin/partials/sync/provider.php', array('type' => $type));
				$html = ob_get_clean();
				wp_send_json_success( 
			    	array(
			    		'status' => 'success', 
			    		'html' => $html,
			    		'message' => ''
			    	) 
			    );

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function copy_all_files_to_bucket_check_process(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			$all = count(get_option('nou_leopard_offload_media_scanned_attachments', []));
			$copyed = count(get_option('nou_leopard_offload_media_copyed_to_cloud_data', []));
			wp_send_json_success( 
			    	array(
			    		'status' => 'success',
			    		'message' => round($copyed / $all * 100),
			    		'count' => $copyed.'/'.$all,
			    		'sync' => get_option('nou_leopard_offload_media_copyed_to_cloud_status', '1')
			    	) 
			    );
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function copy_all_files_to_bucket_kill_process(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			$background_process = new Leopard_Wordpress_Offload_Media_Copy_To_Cloud_Process();
			$background_process->kill_process();
			wp_send_json_success( 
			    	array(
			    		'status' => 'success'
			    	) 
			    );
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function copy_all_files_to_bucket(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				
				$scripts = get_option('nou_leopard_offload_media_scanned_attachments');
				if(empty($scripts)){
					wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
				}

				$background_process = new Leopard_Wordpress_Offload_Media_Copy_To_Cloud_Process();
				// First lets cancel existing running queue to avoid running it more than once.
				$background_process->kill_process();

				update_option('nou_leopard_offload_media_copyed_to_cloud_status', 1);
					
				if(!empty($scripts)){
					foreach ($scripts as $item) {
						$background_process->push_to_queue($item);
					}

					// Lets dispatch the queue to start processing.
					$background_process->save()->dispatch();
				}

				wp_send_json_success( 
			    	array(
			    		'status' => 'success'
			    	) 
			    );

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function remove_all_files_from_server(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				
				$scripts = get_option('nou_leopard_offload_media_scanned_attachments');
				if(empty($scripts)){
					wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
				}

				$recordsToProcess = count($scripts);
				$processed  = ( isset( $_POST['processed'] ) ? $_POST['processed'] : 0 );
				$percent = 0;
				$last = 0;

				while (1) {

					if(isset($scripts[$processed])){
						// do sync data	
						leopard_offload_media_remove_from_server_function($scripts[$processed]);
						update_option('nou_leopard_offload_media_processed_remove_all_files_from_server', $processed);
					}

					$processed++;
					$last = $processed;

					if ($processed >= $recordsToProcess ) {
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'success', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Done', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $recordsToProcess
					    	) 
					    );
				        break;
				    }else{
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'continue', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Continue', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $processed
					    	) 
					    );
				    }
				}

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function remove_all_files_from_bucket(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				
				$scripts = get_option('nou_leopard_offload_media_scanned_attachments');
				if(empty($scripts)){
					wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Empty files.', 'leopard-wordpress-offload-media')));
				}

				$recordsToProcess = count($scripts);
				$processed  = ( isset( $_POST['processed'] ) ? $_POST['processed'] : 0 );
				$percent = 0;
				$last = 0;

				while (1) {

					if(isset($scripts[$processed])){
						// do sync data	
						leopard_offload_media_remove_from_s3_function($scripts[$processed]);
						update_option('nou_leopard_offload_media_processed_remove_all_files_from_bucket', $processed);
					}

					$processed++;
					$last = $processed;

					if ($processed >= $recordsToProcess ) {
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'success', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Done', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $recordsToProcess
					    	) 
					    );
				        break;
				    }else{
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'continue', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Continue', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $processed
					    	) 
					    );
				    }
				}

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function download_all_files(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				
				$scripts = get_option('nou_leopard_offload_media_scanned_attachments');
				if(empty($scripts)){
					wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
				}

				$recordsToProcess = count($scripts);
				$processed  = ( isset( $_POST['processed'] ) ? $_POST['processed'] : 0 );
				$percent = 0;
				$last = 0;

				while (1) {

					if(isset($scripts[$processed])){
						// do sync data	
						leopard_offload_media_copy_to_server_from_s3_function($scripts[$processed]);
						update_option('nou_leopard_offload_media_processed_download_all_files', $processed);
					}

					$processed++;
					$last = $processed;

					if ($processed >= $recordsToProcess ) {
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'success', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Done', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $recordsToProcess
					    	) 
					    );
				        break;
				    }else{
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'continue', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Continue', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $processed
					    	) 
					    );
				    }
				}

			} catch (Exception $e){
				//
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function scan_attachments(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try{
				$scanned = get_option('nou_leopard_offload_media_scanned_attachments', []);
				$query = new WP_Query( 
						array( 
							'post_type' => 'attachment',
							'post_status' => 'inherit',
							'posts_per_page' => -1 
						) 
					);
				if($query->have_posts()):
					while($query->have_posts()): $query->the_post();
						$id = get_the_ID();
						if(!in_array($id, $scanned)){
							$scanned[] = $id;
						}
					endwhile;
					update_option('nou_leopard_offload_media_scanned_attachments', $scanned);
				endif;

				if(isset($_REQUEST['do_action']) && $_REQUEST['do_action'] == 'copy'){
					update_option('nou_leopard_offload_media_copyed_to_cloud_status', 0);
				}

				wp_send_json_success( 
			    	array(
			    		'status' => 'success',
			    		'message' => ''
			    	) 
			    );

			} catch (Exception $e){
				update_option('nou_leopard_offload_media_scanned_attachments', []);
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function set_permission_object(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) && !empty($_POST['key']) ) {
			list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();

			$key = isset($_POST['key']) ? $_POST['key'] : '';
			try{
				$array_aux = explode( '/', $key );
				$main_file = array_pop( $array_aux );
				$array_files[] = implode( "/", $array_aux );
				$array_files[] = $main_file;

				$aws_s3_client->set_object_permission( $Bucket, $Region, $array_files, 'private' );
				wp_send_json_success( 
			    	array(
			    		'status' => 'success',
			    		'message' => ''
			    	) 
			    );
			}catch(Exception $e){
				wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Failed', 'leopard-wordpress-offload-media')));
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function render_cloud_files(){
		try {
			$nonce = $_REQUEST['_wpnonce'];
			if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
				list( $aws_s3_client, $Bucket, $Region, $basedir_absolute ) = leopard_offload_media_provider_info();

				if(isset($_SESSION['leopard_wordpress_offload_media_view_type'])){
					$type = isset($_POST['type']) ? $_POST['type'] : $_SESSION['leopard_wordpress_offload_media_view_type'];
				}else{
					$type = isset($_POST['type']) ? $_POST['type'] : 'list';
				}
				
				if(isset($_POST['type'])){
					$_SESSION['leopard_wordpress_offload_media_view_type'] = $type;
				}

				$current_folder = isset($_POST['current_folder']) ? $_POST['current_folder'] : $Bucket;
				$current_region = isset($_POST['current_region']) ? $_POST['current_region'] : $Region;
				ob_start();
				?>
					<div class="attachments-browser">
						<div id="leopard_wordpress_offload_media_Show_Keys_of_a_Folder_Bucket_Result_ID">
							<?php echo $aws_s3_client->Show_Keys_of_a_Folder_Bucket($current_folder, $current_region);?>
						</div>
					</div>
				<?php
				$html = ob_get_clean();

				wp_send_json_success( 
			    	array(
			    		'status' => 'success', 
			    		'html' => $html,
			    		'message' => ''
			    	) 
			    );
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
		
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function create_bucket(){

		if ( ! isset( $_POST['bucket'] ) ) {
			wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Please, enter your bucket name.', 'leopard-wordpress-offload-media')));
		}

		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			$client = leopard_offload_media_provider();
			$bucket  = $_POST['bucket'];
			
			if($client::identifier() != 'DO'){
				$regional = $_POST['regional'];
			}else{
				$regional = get_option('nou_leopard_offload_media_bucket_regional', 'nyc3');
			}

			try{
				
				$result = $client->create_Bucket($bucket, $regional);
				if(is_object($result)){
					$result = (array) $result;
				}
				if(isset($result['code']) && $result['code'] == '400'){
					wp_send_json_error(
						array(
							'status' => 'fail', 
							'message' => $result['message']
						)
					);
				}
				wp_send_json_success( 
			    	array(
			    		'status' => 'success',
			    		'message' => ''
			    	) 
			    );
			} catch(Exception $e){
				$error = $client->handler_response($e->getMessage());
				wp_send_json_error(
					array(
						'status' => 'fail', 
						'message' => $error['message']
					)
				);
			}

		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));	
	}

	public function form_create_bucket(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			$client = leopard_offload_media_provider();

			ob_start();
			?>
				<p class="nou_leopard_wom_admin_parent_wrap">
					<label>
						<span class="nou_leopard_wom_title"><?php esc_html_e('Bucket name', 'leopard-wordpress-offload');?></span>
				        <span>
				            <input placeholder="<?php esc_html_e('EX: my-bucket', 'leopard-wordpress-offload');?> "class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_bucket">
				        </span>
					</label>
				</p>
				<?php if($client::identifier() != 'DO'):?>
				<p class="nou_leopard_wom_admin_parent_wrap">
					<label>
						<span class="nou_leopard_wom_title"><?php esc_html_e('Regional', 'leopard-wordpress-offload');?></span>
				        <span>
				            <select class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_bucket_regional">
				                <?php 
				                foreach ($client->_array_regions as $region) {

				                    ?>
				                    <option value="<?php echo esc_attr($region[0]);?>"><?php echo esc_html($region[1]);?></option>
				                    <?php
				                }
				                ?>
				            </select>
				        </span>
					</label>
				</p>
				<?php endif;?>
			<?php
			$html = ob_get_clean();

			wp_send_json_success( 
		    	array(
		    		'status' => 'success', 
		    		'form' => $html,
		    		'message' => ''
		    	) 
		    );
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));	
	}

	public function upload_assets(){
		$enable_assets = get_option('nou_leopard_offload_media_assets_rewrite_urls_checkbox', '');
		if ($enable_assets) {
			$nonce = $_REQUEST['_wpnonce'];
			if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
				$assets = new Leopard_Wordpress_Offload_Media_Assets();	
				
				$scripts = get_option('nou_leopard_offload_media_scanned_assets');
		        if(!is_array($scripts)){
		        	$scripts = array();
		        }

		        if(count($scripts) == 0){
		        	wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('No files scanned.', 'leopard-wordpress-offload-media')));
		        }

				$uploaded = get_option('nou_leopard_offload_media_uploaded_assets');
		        if(!is_array($uploaded)){
		        	$uploaded = array();
		        }

				$recordsToProcess = count($scripts);
				$processed  = ( isset( $_POST['processed'] ) ? $_POST['processed'] : 0 );
				$percent = 0;
				$last = 0;

				while (1) {

					if(isset($scripts[$processed])){
						$file_path = $scripts[$processed];
						if (file_exists(ABSPATH. $file_path)) {
							$url = '';
							if(strpos($file_path, '.css') === false){
								$url = $assets->upload_script($file_path);
							}else{
								$url = $assets->upload_css($file_path);
							}

							if($url){
								if (($key = array_search($url, $uploaded)) !== false) {
								    unset($uploaded[$key]);
								}
								$uploaded[] = $url;
							}
							update_option('nou_leopard_offload_media_uploaded_assets', $uploaded);
						}	
					}

					$processed++;
					$last = $processed;

					if ($processed >= $recordsToProcess ) {
						
						try{
							$upload_dir = wp_upload_dir();
							$files = $assets->get_dir_contents($upload_dir['basedir'] . '/leopard-wordpress-offload', '/\.css$/');

							foreach ($files as $file) {
								if(is_file($file)){
	    							unlink($file);
								}
							}
						} catch ( Exception $e ) {
				            //
				        }

				    	wp_send_json_success( 
					    	array(
					    		'status' => 'success', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Done', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $recordsToProcess
					    	) 
					    );
				        break;
				    }else{
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'continue', 
					    		'count' => $recordsToProcess,
					    		'message' => esc_html__('Continue', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $processed
					    	) 
					    );
				    }
				}
			}
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function scan_assets(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			update_option('nou_leopard_offload_media_assets_rewrite_urls_checkbox', 'on');
			$assets = new Leopard_Wordpress_Offload_Media_Assets();	
			$assets->scan_assets();

			$data = array(
				    		'status' => 'success', 
				    		'total' => esc_html__('Scan complete.', 'leopard-wordpress-offload-media')
				    	);
			wp_send_json_success( $data );
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function get_attachment_provider_details(){
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {


			if ( ! isset( $_POST['id'] ) ) {
				return;
			}

			$id = intval( $_POST['id'] );

			$s3_path = get_post_meta( $id, '_wp_nou_leopard_wom_s3_path', true);
			if ( $s3_path != '_wp_nou_leopard_wom_s3_path_not_in_used' && $s3_path != null ) {
				// get the actions available for the attachment
				$data = array(
					'links'           => leopard_wordpress_offload_media_row_actions_extra(array(), $id),
					'provider_object' => leopard_wordpress_offload_media_get_real_provider( $id ),
				);
			}else{
				$data = array(
					'links' => [],
					'provider_object' => []
					);
			}

			wp_send_json_success( $data );
		}
		wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function media_library_button_action_mode_grid() {

		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'leopard_wordpress_offload_media_nonce' ) ) {
			try {
				$doaction = ( isset( $_POST['doaction'] ) ? $_POST['doaction'] : null );
				$post_ids = ( isset( $_POST['post_ids'] ) ? $_POST['post_ids'] : null );

				$recordsToProcess = count($post_ids);
				$processed  = ( isset( $_POST['processed'] ) ? $_POST['processed'] : 0 );
				$percent = 0;
				$last = 0;

				while (1) {

					if(isset($post_ids[$processed])){
						nou_leopard_offload_media_do_bulk_actions_extra_options_function( $doaction, array($post_ids[$processed]) );
					}

					$processed++;
					$last = $processed;

				    if ($processed >= $recordsToProcess ) {
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'success', 
					    		'message' => esc_html__('Done', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $processed
					    	) 
					    );
				        break;
				    }else{
				    	wp_send_json_success( 
					    	array(
					    		'status' => 'continue', 
					    		'message' => esc_html__('Continue', 'leopard-wordpress-offload-media'),
					    		'percent' => round(($last > 0) ? ($last / $recordsToProcess * 100) : 0),
					    		'processed' => $processed
					    	) 
					    );
				    }

				}

			} catch ( Exception $e ) {
				wp_send_json_error(array('status' => 'fail', 'message' => $e->getMessage()));
			}
		}
        wp_send_json_error(array('status' => 'fail', 'message' => esc_html__('Security check', 'leopard-wordpress-offload-media')));
	}

	public function s3_storage_input_hidden_path_file_to_uploaded() {

		if ( nou_leopard_offload_media_check_connection_success() ) {

			$File_Uploaded = ( isset( $_SESSION['nou_leopard_offload_media_aws_storage_uploading_file'] ) ? $_SESSION['nou_leopard_offload_media_aws_storage_uploading_file'] : 'none' );

			if ( $File_Uploaded == 'done' ){

				$_SESSION['nou_leopard_offload_media_aws_storage_uploading_file'] = 'none';

				$object_id = ( isset( $_SESSION['nou_leopard_offload_media_aws_storage_file_copied_to_S3'] ) ? $_SESSION['nou_leopard_offload_media_aws_storage_file_copied_to_S3'] : 'none' );

				$Path_to_File = get_post_meta( $object_id, '_wp_attached_file', true );

				?>

				<script>

					document.getElementById( "nou_leopard_offload_media_storage_input_hidden_path_file_to_uploaded" ).value = '<?php echo esc_attr($Path_to_File); ?>';
					document.getElementById( "nou_leopard_offload_media_storage_input_hidden_path_file_to_uploaded" ).click();

				</script>

				<?php

				//== We only remove from the server if previously we copy the file to S3
				$remove_from_server_checkbox = get_option('nou_leopard_offload_media_remove_from_server_checkbox', '');

				if ( $remove_from_server_checkbox ){

					leopard_offload_media_remove_from_server_function( $object_id );

					//== In case we are uploading a file from a downloadable product we read this flag not to remove from S3
					//== when we delete the post from the database of wordpress
					$wp_delete_post_protecting_S3 = ( isset( $_SESSION['nou_leopard_offload_media_storage_wp_delete_post_protecting_S3'] ) ? $_SESSION['nou_leopard_offload_media_storage_wp_delete_post_protecting_S3'] : false );

					if ( $wp_delete_post_protecting_S3 ){
						// == Setting back protection file session to false for next time ==
						$_SESSION['nou_leopard_offload_media_storage_wp_delete_post_protecting_S3'] = false;
						// == We set the session with the post_id not to be deleted from S3 ==
						$_SESSION['nou_leopard_offload_media_storage_remain_file_in_S3'] = $object_id;

						wp_delete_post( $object_id, true );

					}

				}

			}
			else{

				?>

				<script>

					document.getElementById( "nou_leopard_offload_media_storage_input_hidden_searching_path_file_to_uploaded" ).click();

				</script>

				<?php

			}

		}

        wp_die();

	}

}
