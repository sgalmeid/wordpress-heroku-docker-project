<?php $checkbox = get_option('nou_leopard_offload_media_assets_rewrite_urls_checkbox', '');?>
<input type="hidden" name="nou_leopard_offload_media_url_tab_assets" value="1">
<p class="nou_leopard_wom_admin_parent_wrap">

	<label>

		<span class="nou_leopard_wom_title"><?php esc_html_e('Rewrite Asset URLs', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_assets_rewrite_urls_checkbox" <?php checked( $checkbox, 'on', true ); ?>>
        </span>

        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('For any enqueued files that have been copied to your bucket, rewrite the URLs so that they are served from the bucket or CDN instead of your server. Rewrites local URLs to be served from your Amazon S3 bucket, CloudFront or another CDN, or a custom domain.', 'leopard-wordpress-offload-media');?></span>

        <span class="nou_leopard_wom_description_checkbox show_if_assets_rewrite_urls <?php if($checkbox != 'on'){echo 'hidden';}?>"><input type="button" class="button-secondary" value="<?php esc_html_e('Scan assets', 'leopard-wordpress-offload-media');?>" id="nou_leopard_wom_scan_assets"></span>

	</label>

</p>

<p class="nou_leopard_wom_admin_parent_wrap">

	<label>

		<span class="nou_leopard_wom_title"><?php esc_html_e('Custom path', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_pull_assets_path" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_pull_assets_path', 'pull-assets/'));?>">
            <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('EX: pull-assets/', 'leopard-wordpress-offload-media');?></span>
        </span>

	</label>

</p>


<p class="nou_leopard_wom_admin_parent_wrap">

    <label>

        <span class="nou_leopard_wom_title"><?php esc_html_e('Minify CSS', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_minify_css" <?php checked( get_option('nou_leopard_offload_media_minify_css'), 'on', true ); ?>>
        </span>

    </label>

</p>
<p class="nou_leopard_wom_admin_parent_wrap">

    <label>

        <span class="nou_leopard_wom_title"><?php esc_html_e('Minify JS', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_minify_js" <?php checked( get_option('nou_leopard_offload_media_minify_js'), 'on', true ); ?>>
        </span>

    </label>

</p>