<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/admin/partials
 */
$tab = 'connectS3';
if(isset($_GET['tab'])){
	$tab = $_GET['tab'];
}
$remove_file_server = get_option('nou_leopard_offload_media_remove_from_server_checkbox');
?>	
<div class="wrap" id="leopard-wordpress-offload-media-wrap">
	<h1><?php esc_html_e( 'Leopard Offload Media', 'leopard-wordpress-offload-media' );?></h1>
	<?php if(isset($_POST['nou_leopard_wom_settings_nonce'])){?>
	<div class="updated settings-error notice is-dismissible">
		<p><strong><?php esc_html_e( 'Settings saved.', 'leopard-wordpress-offload-media' ); ?></strong></p>
	</div>
	<?php }?>

	<div class="nou_leopard_wom_loading"><?php esc_html_e('Loading', 'leopard-wordpress-offload-media');?>&#8230;</div>

	<div class="col-left">
		<h2 class="nav-tab-wrapper">
		    <a class="nav-tab <?php if($tab == 'connectS3'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=connectS3'));?>"><?php esc_html_e('Storage Settings', 'leopard-wordpress-offload-media');?></a>
		    <?php $status = get_option('nou_leopard_offload_media_connection_success', 0);?>
		    <?php if($status == 1):?>

		    	<?php $bucket_selected = get_option('nou_leopard_offload_media_connection_bucket_selected_select', '');?>
			    <a class="<?php if(empty($bucket_selected)){echo 'red';}?> nav-tab <?php if($tab == 'generalsettings'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=generalsettings'));?>">
			    	<?php esc_html_e('Bucket Settings', 'leopard-wordpress-offload-media');?>
			    	<?php 
			    	if(empty($bucket_selected)){
			    		esc_html_e('(Bucket does not exist)', 'leopard-wordpress-offload-media');
			    	}
			    	?>	
			    </a>
			    <a class="nav-tab <?php if($tab == 'assets'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=assets'));?>"><?php esc_html_e('Assets', 'leopard-wordpress-offload-media');?></a>          
			    <a class="nav-tab <?php if($tab == 'RewriteUrl'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=RewriteUrl'));?>"><?php esc_html_e('URL Rewriting', 'leopard-wordpress-offload-media');?></a>          
			    <a class="nav-tab <?php if($tab == 'cors'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=cors'));?>"><?php esc_html_e('CORS', 'leopard-wordpress-offload-media');?></a>         
			    <a class="nav-tab <?php if($tab == 'advanced'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=advanced'));?>"><?php esc_html_e('Advanced', 'leopard-wordpress-offload-media');?></a>
			    <a class="nav-tab <?php if($tab == 'sync'){echo 'nav-tab-active';}?>" href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media&tab=sync'));?>"><?php esc_html_e('Sync Data', 'leopard-wordpress-offload-media');?></a>
			<?php endif;?>     

			<a class="nav-tab" target="_blank" href="<?php echo esc_url('//nouthemes.com/docs/leopard/');?>"><?php esc_html_e('Documentation', 'leopard-wordpress-offload-media');?></a>     
		</h2>
		<form method="post">
			<input type="hidden" id="nou_leopard_wom_settings_nonce" name="nou_leopard_wom_settings_nonce" value="<?php echo esc_attr(wp_create_nonce('nou_leopard_wom_settings_nonce'));?>">
			
			<?php 
			if($tab == 'connectS3'){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-connect.php';
			}

			if($tab == 'generalsettings' && $status == 1){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-general.php';
			}

			if($tab == 'RewriteUrl' && $status == 1){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-url.php';
			}

			if($tab == 'assets' && $status == 1){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-assets.php';
			}

			if($tab == 'cors' && $status == 1){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-cors.php';
			}

			if($tab == 'advanced' && $status == 1){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-advanced.php';
			}

			if($tab == 'sync' && $status == 1){
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leopard-wordpress-offload-media-admin-settings-sync.php';
			}else{
			?>
			<input data-tab="<?php echo esc_attr($tab);?>" type="submit" id="nou_leopard_wom_settings_submit" class="button-primary" value="<?php esc_html_e('Save Changes', 'leopard-wordpress-offload-media');?>">
			<?php }?>

		</form>
	</div>
	<div class="col-right">
        <div class="card">
			<h2 class="title"><?php esc_html_e('Download all files from bucket to server.', 'leopard-wordpress-offload-media');?></h2>
			<button type="button" class="button-secondary" id="nou_leopard_wom_settings_download_files_from_bucket"><?php esc_html_e('Download files', 'leopard-wordpress-offload-media');?></button>
		</div>

        <div class="card">
			<h2 class="title"><?php esc_html_e('Remove all files from bucket.', 'leopard-wordpress-offload-media');?></h2>
			<button type="button" class="button-secondary" id="nou_leopard_wom_settings_remove_files_from_bucket"><?php esc_html_e('Remove files', 'leopard-wordpress-offload-media');?></button>
		</div>
		
		<?php if($remove_file_server):?>
	        <div class="card">
				<h2 class="title"><?php esc_html_e('Remove all files from server.', 'leopard-wordpress-offload-media');?></h2>
				<button type="button" class="button-secondary" id="nou_leopard_wom_settings_remove_files_from_server"><?php esc_html_e('Remove files', 'leopard-wordpress-offload-media');?></button>
			</div>
		<?php endif;?>

        <div id="copy_files_to_bucket_card" class="card">
			<?php 
			$copy_status = get_option('nou_leopard_offload_media_copyed_to_cloud_status', 0);
			if($copy_status == 1):
			?>
				<h2 class="title"><?php esc_html_e('Offloading media library items to bucket.', 'leopard-wordpress-offload-media');?></h2>
				<div class="copy-process">
					<div class="iziToastloading spin_loading"></div>
					<div class="progress-bar">
						<span id="percent" style="line-height: 15px;height: 15px;">0%</span>
						<span class="bar" style="height: 15px;"><span class="progress"></span></span>
					</div>
					<div class="current-sync-process progress_count"></div>
				</div>
				<button type="button" class="button-secondary" id="nou_leopard_wom_settings_copy_files_to_bucket_kill"><?php esc_html_e('Kill process.', 'leopard-wordpress-offload-media');?></button>
			<?php else:?>
				<h2 class="title"><?php esc_html_e('Copy all files from server to bucket.', 'leopard-wordpress-offload-media');?></h2>
				<button type="button" class="button-secondary" id="nou_leopard_wom_settings_copy_files_to_bucket"><?php esc_html_e('Copy files', 'leopard-wordpress-offload-media');?></button>
			<?php endif;?>
		</div>
	</div>
</div>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
