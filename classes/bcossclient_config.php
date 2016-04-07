<?php
class BcOssClientConfig {
	public $configs;

	public function __construct(){
		$this->configs = $this->loadConfigs();
	}

	private function loadConfigs() {
		if ( file_exists( plugin_dir_path( __FILE__ ).'../config.php' ) ) {
			return require_once( plugin_dir_path( __FILE__ ).'../config.php' );
		} else {
			return new WP_Error( 'oss_no_config_file', __( "No config file present. Please place a config.php in the plugin directory.", "bcossclient" ) );
		}
	}
}
