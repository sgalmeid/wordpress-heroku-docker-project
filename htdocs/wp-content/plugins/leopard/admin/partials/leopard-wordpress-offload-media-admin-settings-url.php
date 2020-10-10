<input type="hidden" name="nou_leopard_offload_media_url_tab" value="1">
	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Rewrite Media URLs', 'leopard-wordpress-offload-media');?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_rewrite_urls_checkbox" <?php checked( get_option('nou_leopard_offload_media_rewrite_urls_checkbox', 'on'), 'on', true ); ?>>
	        </span>

	        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('For Media Library files that have been copied to your bucket, rewrite the URLs so that they are served from the bucket or CDN instead of your server. Rewrites local URLs to be served from your Amazon S3 bucket, CloudFront or another CDN, or a custom domain.', 'leopard-wordpress-offload-media');?></span>

		</label>

	</p>

	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Custom Domain (CNAME)', 'leopard-wordpress-offload-media');?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_cname" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_cname'));?>">
	            <span><?php esc_html_e('Ex: assets.example.com', 'leopard-wordpress-offload-media');?></span>
	        </span>

	        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('We strongly recommend you configure a CDN to point at your bucket and configure a subdomain of localhost to point at your CDN. If you don\'t enter a subdomain of your site\'s domain in the field above it will negatively impact your site\'s SEO. By default rewritten URLs use the raw bucket URL format, e.g. https://s3.amazonaws.com.... If you have enabled CloudFront, another CDN, or are using a CNAME, you can set that domain with this setting. ', 'leopard-wordpress-offload-media');?></span>

		</label>

	</p>
	<p class="nou_leopard_wom_admin_parent_wrap">

		<label>

			<span class="nou_leopard_wom_title"><?php esc_html_e('Force HTTPS', 'leopard-wordpress-offload-media');?></span>

	        <span>

	            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_force_https_checkbox" <?php checked( get_option('nou_leopard_offload_media_force_https_checkbox', ''), 'on', true ); ?>>
	        </span>

	        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('By default we use HTTPS when the request is HTTPS and regular HTTP when the request is HTTP, but you may want to force the use of HTTPS always, regardless of the request.', 'leopard-wordpress-offload-media');?></span>

		</label>

	</p>