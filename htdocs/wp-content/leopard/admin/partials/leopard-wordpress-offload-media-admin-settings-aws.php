<?php $aws_s3_client = leopard_offload_media_provider();?>
<input type="hidden" name="nou_leopard_offload_media_general_tab" value="1">
<input type="hidden" name="nou_leopard_wom_update_cache_control" value="0">
	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Set a bucket name', 'leopard-wordpress-offload-media');?></span>

	        <span id="as3s_Buckets_List_select">
	        	<select class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_connection_bucket_selected_select" tabindex="-1" aria-hidden="true"><?php echo $aws_s3_client->Show_Buckets();?></select> <?php esc_html_e('Or', 'leopard-wordpress-offload-media');?> 
	        	<input type="button" class="button-secondary" value="<?php esc_html_e('Create bucket', 'leopard-wordpress-offload-media');?>" id="nou_leopard_wom_create_bucket">
	        </span>
	        <span class="nou_leopard_wom_description" style="margin-top: 10px;"><?php echo sprintf(esc_html__('Select the %s bucket name where to upload all the files. If you don\'t know how to create a bucket', 'leopard-wordpress-offload-media'), $aws_s3_client::name());?>, <a href="<?php echo esc_url(leopard_offload_media_provider()::docs_link_create_bucket());?>" target="_blank"><?php esc_html_e('you can click here.', 'leopard-wordpress-offload-media');?></a>

	        </span>

		</label>

	</p>

	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Path', 'leopard-wordpress-offload-media');?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_bucket_folder_main" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_bucket_folder_main', ''));?>">
	            <span class="nou_leopard_wom_margin_right"><?php esc_html_e('EX: my-folder/my-sub-folder', 'leopard-wordpress-offload-media');?></span>
	        </span>
	        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('Set a path for attachments to be offloaded to in the bucket. This is helpful if you are using the same bucket to store files for different sites. This does not affect the local path of the file on the server.', 'leopard-wordpress-offload-media');?></span>
		</label>

	</p>

	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php echo sprintf(esc_html__('Copy file to %s', 'leopard-wordpress-offload-media'), $aws_s3_client::name());?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_copy_file_s3_checkbox" <?php checked( get_option('nou_leopard_offload_media_copy_file_s3_checkbox'), 'on', true ); ?>>

	            <?php echo sprintf(esc_html__('Copy files also to %s', 'leopard-wordpress-offload-media'), $aws_s3_client::name());?>
	        </span>

	        <span class="nou_leopard_wom_description_checkbox">
	        	<?php echo sprintf(esc_html__('The files uploaded to the media library will be added automatically to %s. The files that are already in the media library will NOT be uploaded to %s', 'leopard-wordpress-offload-media'), $aws_s3_client::name(), $aws_s3_client::name());?></span>

		</label>

	</p>

	<p class="nou_leopard_wom_admin_parent_wrap">

		<span class="nou_leopard_wom_title"><?php esc_html_e('Permissions', 'leopard-wordpress-offload-media');?></span>

		<label>

			<input class="nou_leopard_wom_input_text" type="radio" name="nou_leopard_offload_media_private_public_radio_button" <?php checked( get_option('nou_leopard_offload_media_private_public_radio_button', 'public'), 'private', true ); ?> value="private">

            <span class="nou_leopard_wom_margin_right"><?php esc_html_e('Private', 'leopard-wordpress-offload-media');?></span>

		</label>

		<label>
			<input class="nou_leopard_wom_input_text" type="radio" name="nou_leopard_offload_media_private_public_radio_button" <?php checked( get_option('nou_leopard_offload_media_private_public_radio_button', 'public'), 'public', true ); ?> value="public">

            <span class="nou_leopard_wom_margin_right"><?php esc_html_e('Public', 'leopard-wordpress-offload-media');?></span>
		</label>

		<span class="nou_leopard_wom_description"><?php echo sprintf(esc_html__('By setting the files as public, anyone who knows the %s URL will have complete access to it.', 'leopard-wordpress-offload-media'), $aws_s3_client::name());?></span>

	</p>



	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Cache-Control', 'leopard-wordpress-offload-media');?></span>

	        <span>
	            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_cache_control" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_cache_control', 'public, max-age=31536000'));?>">
	        </span>

	        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('Sets the Cache-Control metadata for uploads. Default: public, max-age=31536000 (one year)', 'leopard-wordpress-offload-media');?></span>

		</label>

	</p>



	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Allow File Upload Types', 'leopard-wordpress-offload-media');?></span>

	        <span>
	            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_accepted_filetypes" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_accepted_filetypes', ''));?>">
	            <span class="nou_leopard_wom_margin_right"><?php esc_html_e('The blank means that will allow all types.', 'leopard-wordpress-offload-media');?></span>
	        </span>

	        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('If you want to set only .mp3 and .mp4 files will upload and all of the rest of the files will not move to cloud.', 'leopard-wordpress-offload-media');?> <strong><?php esc_html_e('Separated by commas. EX: mp3,mp4', 'leopard-wordpress-offload-media');?></strong></span>

		</label>

	</p>

	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Compress Objects Automatically', 'leopard-wordpress-offload-media');?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_gzip" <?php checked( get_option('nou_leopard_offload_media_gzip'), 'on', true ); ?>>
	        </span>
	        <?php esc_html_e('Enable GZIP', 'leopard-wordpress-offload-media');?>

		</label>

	</p>

	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Remove from server', 'leopard-wordpress-offload-media');?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_remove_from_server_checkbox" <?php checked( get_option('nou_leopard_offload_media_remove_from_server_checkbox'), 'on', true ); ?>>

	            <?php esc_html_e('Remove from the server', 'leopard-wordpress-offload-media');?>
	        </span>

	        <span class="nou_leopard_wom_description_checkbox"><?php echo sprintf(esc_html__('The files uploaded to %s will be deleted from your server.', 'leopard-wordpress-offload-media'), $aws_s3_client::name());?></span>

		</label>

	</p>