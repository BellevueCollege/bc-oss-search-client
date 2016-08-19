<?php
/*
Plugin Name: Open Search Server Search Client
Plugin URI: https://github.com/BellevueCollege/
Description: Open Search Server search box for BC Website
Author: Bellevue College Integration Team
Version: 1.0.1
Author URI: http://www.bellevuecollege.edu
GitHub Plugin URI: BellevueCollege/bc-oss-search-client
Text Domain: bcossclient
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Shortcode
function bcossclient_shortcode( $sc_config ) {
	$sc_config = shortcode_atts( array(
		'spelling' => 'false',
	), $sc_config, 'bcossclient_shortcode' );

	// Include OSS Library
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	// Require all files in classes/
	foreach ( glob( plugin_dir_path( __FILE__ ) . "classes/*.php" ) as $file ) {
		require_once $file;
	}

	// Load Configs
	$configs = new BcOssClientConfig();
	if ( is_wp_error( $configs->configs ) ) {
		return $configs->configs->get_error_message();
	}

	// Load model
	$model = new BcOssClientModel( $configs, $sc_config );

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

/*
 * Disable the reserved query var 'search'
 *
 * WordPress reserves the query var 'search' by default
 * https://codex.wordpress.org/WordPress_Query_Vars
 */
function disable_query_var_search($vars){
	unset($vars['search']);
	return $vars;
}
add_filter('request', 'disable_query_var_search');
