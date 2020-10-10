<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://themeforest.net/user/nouthemes/portfolio
 * @since             1.0.0
 * @package           Leopard_Wordpress_Offload_Media
 *
 * @wordpress-plugin
 * Plugin Name:       Leopard - WordPress offload media
 * Plugin URI:        https://themeforest.net/user/nouthemes/portfolio
 * Description:       Leopard – WordPress offload media copies files from your WordPress Media Library to Amazon S3, Wasabi, Google cloud storage, DigitalOcean Spaces and rewrites URLs to server the files from that same storage provider, or from the CDN of your choice (CloudFront).
 * Version:           1.0.24
 * Author:            Nouthemes
 * Author URI:        https://themeforest.net/user/nouthemes/portfolio
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       leopard-wordpress-offload-media
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LEOPARD_WORDPRESS_OFFLOAD_MEDIA_VERSION', '1.0.24' );
define( "LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR", plugin_dir_path(__FILE__) );
define( "LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_URI", plugin_dir_url(__FILE__) );
define( "LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DEFAULT_EXPIRES", 900 );
define( "LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DIR_FILE", __FILE__ );
define( "LEOPARD_WORDPRESS_OFFLOAD_MEDIA_MINIMUM_PHP_VERSION", '7.0' );

if ( ! defined( 'FS_CHMOD_FILE' ) ) {
	define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
}

define( 
	"LEOPARD_WORDPRESS_OFFLOAD_MEDIA_CORS_AllOWED_METHODS", 
	array(
		'GET', 
		'HEAD',
		'PUT',
		'POST',
		'DELETE'
	) 
);

define( 
	"LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PROVIDER", 
	array(
		'aws' => esc_html__('Amazon S3', 'leopard-wordpress-offload-media'), 
		'wasabi' => esc_html__('Wasabi', 'leopard-wordpress-offload-media'),
		'google' => esc_html__('Google Cloud Storage', 'leopard-wordpress-offload-media'),
		'DO' => esc_html__('DigitalOcean Spaces', 'leopard-wordpress-offload-media')
	) 
);

define( 
	"LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PROVIDER_SYNC", 
	LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PROVIDER 
);

define( 
	"LEOPARD_WORDPRESS_OFFLOAD_MEDIA_DO_REGIONS", 
	array(
        'nyc3' => 'New York City, United States',
        'sfo2' => 'San Francisco, United States',
        'sgp1' => 'Singapore',
        'fra1' => 'Frankfurt, Germany',
        'ams3' => 'Amsterdam',
	) 
);

require_once( LEOPARD_WORDPRESS_OFFLOAD_MEDIA_PLUGIN_DIR . 'includes/class-leopard-wordpress-offload-media-licenser.php' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-leopard-wordpress-offload-media-activator.php
 */
function activate_leopard_wordpress_offload_media() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-leopard-wordpress-offload-media-activator.php';
	Leopard_Wordpress_Offload_Media_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-leopard-wordpress-offload-media-deactivator.php
 */
function deactivate_leopard_wordpress_offload_media() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-leopard-wordpress-offload-media-deactivator.php';
	Leopard_Wordpress_Offload_Media_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_leopard_wordpress_offload_media' );
register_deactivation_hook( __FILE__, 'deactivate_leopard_wordpress_offload_media' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

require plugin_dir_path( __FILE__ ) . 'functions/global.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-leopard-wordpress-offload-media.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_leopard_wordpress_offload_media() {

	$plugin = new Leopard_Wordpress_Offload_Media();
	$plugin->run();

}
run_leopard_wordpress_offload_media();