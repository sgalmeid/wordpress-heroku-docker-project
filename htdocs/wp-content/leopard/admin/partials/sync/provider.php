<?php
$provider_from = get_option('nou_leopard_offload_media_sync_provider_from');
$settings_from = get_option('nou_leopard_offload_media_sync_settings_from', []);
$provider_to = get_option('nou_leopard_offload_media_sync_provider_to');
$settings_to = get_option('nou_leopard_offload_media_sync_settings_to', []);

$credentials_from = isset($settings_from['credentials_key']) ? $settings_from['credentials_key'] : '';
$access_key_from = isset($settings_from['access_key']) ? $settings_from['access_key'] : '';
$secret_access_from = isset($settings_from['secret_access_key']) ? $settings_from['secret_access_key'] : '';

$credentials_to = isset($settings_to['credentials_key']) ? $settings_to['credentials_key'] : '';
$access_key_to = isset($settings_to['access_key']) ? $settings_to['access_key'] : '';
$secret_access_to = isset($settings_to['secret_access_key']) ? $settings_to['secret_access_key'] : '';

$client_from = null;
$client_to = null;

if(!empty($provider_from) && !empty($settings_from)){
	$client_from = leopard_offload_media_provider($provider_from, $settings_from);
}

if(!empty($provider_to) && !empty($settings_to)){
	$client_to = leopard_offload_media_provider($provider_to, $settings_to);
}

$Bucket_Selected_from = get_option('nou_leopard_offload_media_sync_bucket_from', '');
$Bucket_Selected_to = get_option('nou_leopard_offload_media_sync_bucket_to', '');

$regional_from = get_option('nou_leopard_offload_media_bucket_regional_from', 'nyc3');
$regional_to = get_option('nou_leopard_offload_media_bucket_regional_to', 'nyc3');
?>
<div class="sync-content-provider">
	<div class="sync-content-provider-col sync-content-provider-from">
		<p class="nou_leopard_wom_admin_parent_wrap">
			<label>
				<span class="nou_leopard_wom_title"><?php esc_html_e('From', 'leopard-wordpress-offload-media');?></span>
				<select data-target="from" class="nou_leopard_wom_input_text sync-provider" name="nou_leopard_wom_connection_provider_from">
					<option value="0"><?php esc_html_e('Select a cloud', 'leopard-wordpress-offload-media');?></option>
					<?php foreach(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PROVIDER_SYNC as $key => $cloud ){?>
					<option value="<?php echo esc_attr($key);?>" <?php selected($key, $provider_from);?>><?php echo esc_html($cloud);?></option>
					<?php }?>
				</select>
				<span class="nou_leopard_wom_title <?php if($provider_from != 'DO'){echo 'hidden';}?> conditional_from show_if_DO"><?php esc_html_e('Region', 'leopard-wordpress-offload-media');?></span>
				<select class="<?php if($provider_from != 'DO'){echo 'hidden';}?> nou_leopard_wom_input_text conditional_from show_if_DO sync-region" name="nou_leopard_offload_media_bucket_regional_from">
					<?php foreach(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DO_REGIONS as $key => $cloud ){?>
					<option value="<?php echo esc_attr($key);?>" <?php selected($key, $regional_from);?>><?php echo esc_html($cloud);?></option>
					<?php }?>
				</select>
			</label>
		</p>
		<p class="nou_leopard_wom_admin_parent_wrap conditional_from show_if_google <?php if($provider_from != 'google'){echo 'hidden';}?>">
			<label>
				<span class="nou_leopard_wom_title"><?php esc_html_e('Credentials', 'leopard-wordpress-offload-media');?></span>
				<textarea class="conditional_change nou_leopard_wom_input_text" name="nou_leopard_wom_connection_credentials_from"><?php if(!empty($credentials_from)){echo json_encode($credentials_from);}?></textarea>
			</label>
		</p>

		<p class="nou_leopard_wom_admin_parent_wrap conditional_from  show_if_DO show_if_aws show_if_wasabi <?php if($provider_from == 'google'){echo 'hidden';}?>">
			<label>
				<span class="nou_leopard_wom_title"><?php esc_html_e('Access key', 'leopard-wordpress-offload-media');?></span>
				<input class="conditional_change nou_leopard_wom_input_text" type="password" name="nou_leopard_wom_connection_access_key_text_from" value="<?php echo $access_key_from;?>">
			</label>
		</p>

		<p class="nou_leopard_wom_admin_parent_wrap conditional_from  show_if_DO show_if_aws show_if_wasabi <?php if($provider_from == 'google'){echo 'hidden';}?>">

			<label>

				<span class="nou_leopard_wom_title"><?php esc_html_e('Secret access key', 'leopard-wordpress-offload-media');?></span>

				<input class="conditional_change nou_leopard_wom_input_text" type="password" name="nou_leopard_wom_connection_secret_access_key_text_from" value="<?php echo $secret_access_from;?>">

			</label>

		</p>
		<div class="sync-content-bucket">
			<?php if($client_from != null):?>
				<p class="nou_leopard_wom_admin_parent_wrap">
					<label>
						<span class="nou_leopard_wom_title">
							<?php if($type == 'bucket'){?>
								<?php esc_html_e('From bucket', 'leopard-wordpress-offload-media');?>
							<?php }else{?>
								<?php esc_html_e('Select bucket', 'leopard-wordpress-offload-media');?>
							<?php }?>
						</span>
						<select data-target="from" class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_connection_bucket_from" tabindex="-1" aria-hidden="true"><?php echo $client_from->Show_Buckets($Bucket_Selected_from);?></select>
					</label>
				</p>
				<?php if($type == 'bucket'){?>
				<p class="nou_leopard_wom_admin_parent_wrap">
					<label>
						<span class="nou_leopard_wom_title"><?php esc_html_e('To bucket', 'leopard-wordpress-offload-media');?></span>
						<select data-target="to" class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_connection_bucket_to" tabindex="-1" aria-hidden="true"><?php echo $client_from->Show_Buckets($Bucket_Selected_to);?></select>
					</label>
				</p>
				<?php }?>
			<?php endif;?>
		</div>
	</div>
	<?php if($type == 'cloud'):?>
		<div class="sync-content-provider-col sync-content-provider-to">
			<p class="nou_leopard_wom_admin_parent_wrap">
				<label>
					<span class="nou_leopard_wom_title"><?php esc_html_e('To', 'leopard-wordpress-offload-media');?></span>
					<select data-target="to" class="nou_leopard_wom_input_text sync-provider" name="nou_leopard_wom_connection_provider_to">
						<option value="0"><?php esc_html_e('Select a cloud', 'leopard-wordpress-offload-media');?></option>
						<?php foreach(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PROVIDER_SYNC as $key => $cloud ){?>
						<option value="<?php echo esc_attr($key);?>" <?php selected($key, $provider_to);?>><?php echo esc_html($cloud);?></option>
						<?php }?>
					</select>

					<span class="nou_leopard_wom_title <?php if($provider_to != 'DO'){echo 'hidden';}?> conditional_to show_if_DO"><?php esc_html_e('Region', 'leopard-wordpress-offload-media');?></span>
					<select class="<?php if($provider_to != 'DO'){echo 'hidden';}?> nou_leopard_wom_input_text conditional_to show_if_DO sync-region" name="nou_leopard_offload_media_bucket_regional_to">
						<?php foreach(LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DO_REGIONS as $key => $cloud ){?>
						<option value="<?php echo esc_attr($key);?>" <?php selected($key, $regional_to);?>><?php echo esc_html($cloud);?></option>
						<?php }?>
					</select>
				</label>
			</p>

			<p class="nou_leopard_wom_admin_parent_wrap <?php if($provider_to != 'google'){echo 'hidden';}?> conditional_to show_if_google">
				<label>
					<span class="nou_leopard_wom_title"><?php esc_html_e('Credentials', 'leopard-wordpress-offload-media');?></span>
					<textarea class="conditional_change nou_leopard_wom_input_text" name="nou_leopard_wom_connection_credentials_to"><?php if(!empty($credentials_to)){echo json_encode($credentials_to);}?></textarea>
				</label>
			</p>

			<p class="nou_leopard_wom_admin_parent_wrap <?php if($provider_to == 'google'){echo 'hidden';}?> conditional_to  show_if_DO show_if_aws show_if_wasabi">
				<label>
					<span class="nou_leopard_wom_title"><?php esc_html_e('Access key', 'leopard-wordpress-offload-media');?></span>
					<input class="conditional_change nou_leopard_wom_input_text" type="password" name="nou_leopard_wom_connection_access_key_text_to" value="<?php echo $access_key_to;?>">
				</label>
			</p>

			<p class="nou_leopard_wom_admin_parent_wrap <?php if($provider_to == 'google'){echo 'hidden';}?> conditional_to  show_if_DO show_if_aws show_if_wasabi">

				<label>

					<span class="nou_leopard_wom_title"><?php esc_html_e('Secret access key', 'leopard-wordpress-offload-media');?></span>

					<input class="conditional_change nou_leopard_wom_input_text" type="password" name="nou_leopard_wom_connection_secret_access_key_text_to" value="<?php echo $secret_access_to;?>">

				</label>

			</p>
			<div class="sync-content-bucket">
				<?php if($client_to != null):?>
					<p class="nou_leopard_wom_admin_parent_wrap">
						<label>
							<span class="nou_leopard_wom_title">
								<?php esc_html_e('Select bucket', 'leopard-wordpress-offload-media');?>
							</span>
							<select data-target="to" class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_connection_bucket_to" tabindex="-1" aria-hidden="true"><?php echo $client_to->Show_Buckets($Bucket_Selected_to);?></select>
						</label>
					</p>
				<?php endif;?>
			</div>
		</div>
	<?php endif;?>
</div>