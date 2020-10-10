<?php 
$bucket_from = get_option('nou_leopard_offload_media_sync_bucket_from');
$bucket_to = get_option('nou_leopard_offload_media_sync_bucket_to');
$type = get_option('nou_leopard_offload_media_sync_type');
$text_offload = esc_html__('Sync data now', 'leopard-wordpress-offload-media');
?>
<div class="nou_leopard_wom_loading"></div>
<div class="sync-tab">
	<p class="sync-target nou_leopard_wom_admin_parent_wrap">

		<span class="nou_leopard_wom_title"><?php esc_html_e('Target', 'leopard-wordpress-offload-media');?></span>

		<label>

			<input type="radio" name="nou_leopard_offload_media_sync_target" <?php checked(get_option('nou_leopard_offload_media_sync_type'), 'cloud');?> value="cloud">

	        <span class="nou_leopard_wom_margin_right"><?php esc_html_e('Cloud to Cloud', 'leopard-wordpress-offload-media');?></span>

		</label>

		<label>
			<input type="radio" name="nou_leopard_offload_media_sync_target" <?php checked(get_option('nou_leopard_offload_media_sync_type'), 'bucket');?> value="bucket">

	        <span class="nou_leopard_wom_margin_right"><?php esc_html_e('Bucket to Bucket', 'leopard-wordpress-offload-media');?></span>
		</label>

	</p>
	<div class="sync-content">
		<?php if(!empty($type)):?>
			<?php 
			nou_leopard_offload_media_load_template(
				'admin/partials/sync/provider.php', 
				array('type' => $type), 
				true
			);
			?>
		<?php endif;?>
	</div>
	<div id="nou_leopard_wom_sync_data" class="sync-action <?php if(!empty($bucket_from) && !empty($bucket_to)){echo '';}else{echo 'hidden';}?>">
		<input type="button" class="button-primary" value="<?php echo esc_html($text_offload);?>">
	</div>
</div>