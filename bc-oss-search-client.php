<?php
/*
Plugin Name: Open Search Server Search Client
Plugin URI: https://github.com/BellevueCollege/
Description: Open Search Server search box for BC Website
Author: Bellevue College Integration Team
Version: 0.0.0.1
Author URI: http://www.bellevuecollege.edu
GitHub Plugin URI: BellevueCollege/bc-oss-search-client
Text Domain: bcossclient
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Include OSS Library
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// Require all files in classes/
foreach ( glob( plugin_dir_path( __FILE__ ) . "classes/*.php" ) as $file ) {
	require_once $file;
}

// Shortcode
function bcossclient_shortcode() {
	/* $attr = shortcode_atts( array(
		'foo' => 'something',
		'bar' => 'something else',
	), $atts );*/

	// Load Configs
	$configs = new BcOssClientConfig();
	if ( is_wp_error( $configs->configs ) ) {
		return $configs->configs->get_error_message();
	}

	// Load model
	$model = new BcOssClientModel( $configs );

	// Load controller
	$controller = new BcOssClientController( $model );

	// Load view
	$view = new BcOssClientView( $controller, $model );

	// Send url perameters to controller
	$controller->load_perameter( $model->config( 'query_peram' ) );
	$controller->load_perameter( $model->config( 'page_peram' ) );
	$controller->load_perameter( $model->config( 'filter_peram' ) );

	// Output
	return $view->output();
}
add_shortcode( 'bc-oss-search', 'bcossclient_shortcode' );
