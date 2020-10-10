<?php 
$allow_methods = get_option('nou_leopard_offload_media_cors_allow_methods', array('GET', 'HEAD'));
?>
<input type="hidden" name="nou_leopard_offload_media_cors_tab" value="1">
<p class="nou_leopard_wom_admin_parent_wrap">
    <span><?php esc_html_e('Cross Origin Resource Sharing (CORS) is a mechanism for allowing interactions between resources from different origins, something that is normally prohibited in order to prevent malicious behavior.', 'leopard-wordpress-offload-media');?></span>
</p>


<p class="nou_leopard_wom_admin_parent_wrap">

    <label>

        <span class="nou_leopard_wom_title"><?php esc_html_e('Origins', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_cors_origin" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_cors_origin', '*'));?>">
            <span><?php esc_html_e('EX: assets.example.com,cdn.example.com or *', 'leopard-wordpress-offload-media');?></span>
        </span>
        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('The list of Origins eligible to receive CORS response headers, separated by commas. Note: "*" is permitted in the list of origins, and means "any Origin".', 'leopard-wordpress-offload-media');?></span>

    </label>

</p>

<p class="nou_leopard_wom_admin_parent_wrap">

    <label>

        <span class="nou_leopard_wom_title"><?php esc_html_e('HTTP methods', 'leopard-wordpress-offload-media');?></span>

        <span>

            <select class="nou_leopard_wom_input_text" name="nou_leopard_offload_media_cors_allow_methods[]" multiple="">
                <?php 
                foreach (LEOPARD_WORDPRESS_OFFLOAD_MEDIA_CORS_AllOWED_METHODS as $method) {
                    ?>
                    <option value="<?php echo esc_attr($method);?>" <?php if(in_array($method, $allow_methods)){echo 'selected="selected"';}?>><?php echo esc_html($method);?></option>
                    <?php
                }
                ?>
            </select>
        </span>

    </label>

</p>

<p class="nou_leopard_wom_admin_parent_wrap">

    <label>

        <span class="nou_leopard_wom_title"><?php esc_html_e('Max Age Seconds', 'leopard-wordpress-offload-media');?></span>

        <span>

            <input class="nou_leopard_wom_input_text" type="text" name="nou_leopard_offload_media_cors_maxageseconds" value="<?php echo esc_attr(get_option('nou_leopard_offload_media_cors_maxageseconds', '3600'));?>">
            
        </span>
        <span class="nou_leopard_wom_description_checkbox"><?php esc_html_e('For preflighted requests, allow the browser to make requests for 3600 seconds (1 hour) before it must repeat the preflight request.', 'leopard-wordpress-offload-media');?></span>

    </label>

</p>