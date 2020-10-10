<div class="wrap" id="leopard-wordpress-offload-media-wrap">
	<h1><?php esc_html_e( 'Leopard Offload Media', 'leopard-wordpress-offload-media' );?></h1>

	<form method="post">
		<?php 
		$active = get_option('nou_leopard_offload_media_license_active');
		if($active != '1'){
			?>

			<?php if(isset($_POST['nou_leopard_wom_settings_nonce'])){?>
				<?php
				$message = get_option('nou_leopard_offload_media_license_active_message', ''); 
				if(!empty($message)){
				?>
					<div class="update-nag"><p><?php echo esc_html($message);?></p></div>
				<?php }?>	
			<?php }?>	

			<input type="hidden" id="nou_leopard_wom_settings_nonce" name="nou_leopard_wom_settings_nonce" value="<?php echo esc_attr(wp_create_nonce('nou_leopard_wom_settings_nonce'));?>">
			<p><?php esc_html_e( 'In order to receive all benefits of Leopard - WordPress Offload Media, you need to activate your plugin.', 'leopard-wordpress-offload-media' );?></p>
			<p class="nou_leopard_wom_admin_parent_wrap">

				<label>

					<span class="nou_leopard_wom_title"><?php esc_html_e('Purchase Code', 'leopard-wordpress-offload-media');?></span>

			        <span>

			            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_license_key" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_license_key'));?>">
			            <span><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><?php esc_html_e('Where Is My Purchase Code?', 'leopard-wordpress-offload-media');?></a>
			        </span>

				</label>

			</p>
			<p class="nou_leopard_wom_admin_parent_wrap">

				<label>

					<span class="nou_leopard_wom_title"><?php esc_html_e('Purchaser Email', 'leopard-wordpress-offload-media');?></span>

			        <span>

			            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_license_email" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_license_email'));?>">
			        </span>

				</label>

			</p>
			<input type="submit" class="button-primary" value="<?php esc_html_e('Activate', 'leopard-wordpress-offload-media');?>">
		<?php 
		}else{
		?>
		<div id="message" class="updated notice">
			<p><?php esc_html_e( 'You have activated Leopard - WordPress Offload Media version which allows you to access all the customer benefits. Thank you for choosing Leopard - WordPress Offload Media.', 'leopard-wordpress-offload-media' );?></p>
			<p><a href="<?php echo esc_url(admin_url('admin.php?page=leopard_offload_media'));?>"><?php esc_html_e('Back to settings.', 'leopard-wordpress-offload-media');?></a></p>
		</div>	
		<form method="post">
			<input type="hidden" id="nou_leopard_wom_settings_nonce" name="nou_leopard_wom_settings_nonce" value="<?php echo esc_attr(wp_create_nonce('nou_leopard_wom_settings_nonce'));?>">
			<input type="hidden" name="nou_leopard_offload_media_deactivate_license" value="ok">
			<input type="submit" class="button-primary" value="<?php esc_html_e('Deactivate license.', 'leopard-wordpress-offload-media');?>" />
		</form>
		<?php }?>
	</form>	
</div>