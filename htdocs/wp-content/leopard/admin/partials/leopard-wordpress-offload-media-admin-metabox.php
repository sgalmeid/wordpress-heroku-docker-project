<?php 
global $post;
$provider_object = leopard_wordpress_offload_media_get_real_provider( $post->ID );
$provider_name = empty( $provider_object['provider'] ) ? '' : $provider_object['provider'];
$links = leopard_wordpress_offload_media_row_actions_extra(array(), $post->ID);
?>
<div class="s3-details">
	<?php if ( empty($provider_object['key']) ) : ?>
		<div class="misc-pub-section">
			<em class="not-copied"><?php esc_html_e( 'This item has not been offloaded yet.', 'leopard-wordpress-offload-media' ); ?></em>
		</div>
	<?php else : ?>
		<div class="misc-pub-section">
			<div class="s3-key"><?php esc_html_e( 'Storage Provider', 'leopard-wordpress-offload-media' ); ?>:</div>
			<input type="text" id="as3cf-provider" class="widefat" readonly="readonly" value="<?php echo esc_attr(leopard_wordpress_offload_media_get_provider_service_name($provider_name)); ?>">
		</div>
		<div class="misc-pub-section">
			<div class="s3-key"><?php esc_html_e( 'Bucket', 'leopard-wordpress-offload-media' ); ?>:</div>
			<input type="text" id="as3cf-bucket" class="widefat" readonly="readonly" value="<?php echo esc_attr($provider_object['bucket']); ?>">
		</div>
		<?php if ( isset( $provider_object['region'] ) && $provider_object['region'] ) : ?>
			<div class="misc-pub-section">
				<div class="s3-key"><?php esc_html_e( 'Region', 'leopard-wordpress-offload-media' ); ?>:</div>
				<input type="text" id="as3cf-region" class="widefat" readonly="readonly" value="<?php echo esc_attr($provider_object['region']); ?>">
			</div>
		<?php endif; ?>
		<div class="misc-pub-section">
			<div class="s3-key"><?php esc_html_e( 'Path', 'leopard-wordpress-offload-media' ); ?>:</div>
			<input type="text" id="as3cf-key" class="widefat" readonly="readonly" value="<?php echo esc_attr($provider_object['key']); ?>">
		</div>
		
		<div class="misc-pub-section">
			<?php echo join(' | ', $links);?>
		</div>

	<?php endif; ?>
	<div class="clear"></div>
</div>