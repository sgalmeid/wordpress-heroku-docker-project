<?php

/**
 * Config S3
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://themeforest.net/user/nouthemes/portfolio
 * @since      1.0.0
 *
 * @package    Leopard_Wordpress_Offload_Media
 * @subpackage Leopard_Wordpress_Offload_Media/admin/partials
 */
?>
<?php $status = get_option('nou_leopard_offload_media_connection_success', 0);?>
<?php if($status == 1):?>
	<?php require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'admin/partials/leopard-wordpress-offload-media-admin-settings-aws.php' );?>
<?php else:?>
	<p class="nou_leopard_wom_error_accessing_class">
		<img class="nou_leopard_wom_error_accessing_class_img" style="width: 35px;" src="<?php echo esc_url(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_URI.'admin/images/access-error-logs.png');?>">
		<span class="nou_leopard_wom_error_accessing_class_span"><?php esc_html_e('An error occurred while accessing, the credentials (access key or secret key) are NOT correct', 'leopard-wordpress-offload-media');?></span>
	</p>
<?php endif;?>