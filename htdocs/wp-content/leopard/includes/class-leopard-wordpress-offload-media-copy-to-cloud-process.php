<?php

/**
 * Copy file to cloud Background Process
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.22
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/includes
 */

class Leopard_Wordpress_Offload_Media_Copy_To_Cloud_Process extends Leopard_Wordpress_Offload_Media_Background_Process {

	/**
	 * Initiate new background process.
	 */
	public function __construct() {
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'wp_' . get_current_blog_id();
		$this->action = 'leopard_wom_copy_to_cloud';

		parent::__construct();
	}
	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		$total_synced = get_option('nou_leopard_offload_media_copyed_to_cloud_data', []);
		if(!is_array($total_synced)){
			$total_synced = [];
		}

		try{
			leopard_offload_media_copy_to_s3_function($item);
		}catch(Exception $e){
			error_log($e->getMessage());
		}

		try{
			leopard_offload_media_build_webp_function($item);
		}catch(Exception $e){
			error_log($e->getMessage());
		}

		$total_synced[] = $item;
		update_option('nou_leopard_offload_media_copyed_to_cloud_data', array_unique($total_synced));
		
		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();
		update_option('nou_leopard_offload_media_copyed_to_cloud_status', 2);
		try{
			wp_mail( 
				get_option('admin_email'), 
				esc_html__('Leopard Offload Media Synchronize', 'leopard-wordpress-offload-media'), 
				esc_html__('Leopard Offload Media Copy all files from server to bucket: process has been completed.', 'leopard-wordpress-offload-media') 
			);
		} catch (Exception $e){
			error_log('wp_mail send failed.');
		}
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		update_option('nou_leopard_offload_media_copyed_to_cloud_data', []);
		update_option('nou_leopard_offload_media_copyed_to_cloud_status', 0);
		
		parent::kill_process();
	}

	/**
	 * Is job running?
	 *
	 * @return boolean
	 */
	public function is_running() {
		return $this->is_queue_empty();
	}
}
?>