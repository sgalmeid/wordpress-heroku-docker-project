
<input type="hidden" name="nou_leopard_offload_media_advanced_tab" value="1">

<p class="nou_leopard_wom_admin_parent_wrap">

	<label>

		<span class="nou_leopard_wom_title"><?php esc_html_e('Removing the emoji.', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_emoji" <?php checked( get_option('nou_leopard_offload_media_emoji'), 'on', true ); ?>>
        </span>

	</label>

</p>

<p class="nou_leopard_wom_admin_parent_wrap">

	<label>

		<span class="nou_leopard_wom_title"><?php esc_html_e('Minify HTML', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_minify_html" <?php checked( get_option('nou_leopard_offload_media_minify_html'), 'on', true ); ?>>
        </span>

	</label>

</p>


<p class="nou_leopard_wom_admin_parent_wrap">

	<label>

		<span class="nou_leopard_wom_title"><?php esc_html_e('WebP versions', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="checkbox" name="nou_leopard_offload_media_webp" <?php checked( get_option('nou_leopard_offload_media_webp'), 'on', true ); ?>>
            <span><?php esc_html_e('Create also WebP version of the images.', 'leopard-wordpress-offload-media');?></span>
        </span>
        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('WebP image can be up to three times smaller than PNGs and 25% smaller than JPGs.', 'leopard-wordpress-offload-media');?></span>

	</label>

</p>