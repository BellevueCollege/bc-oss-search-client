<?php
class BcOssClientController {
	protected $model;

	public function __construct($model) {
		$this->model   = $model;
	}

	/**
	 * Load parameters from URL
	 *
	 * Call to load URL perameters and store in model
	 */
	public function load_perameter( $key ) {
		$value;

		// Check if URL permeter is set
		if ( isset( $_GET[ $key ] ) && $_GET[ $key ] != "" ) {
			$value = $_GET[ $key ];

			// Send values to Model
			switch ( $key ) {
				case $this->model->config('query_peram'):
					$this->model->query = $value;
					//echo $this->configs['query_peram']." is $value . ";
					break;
				case $this->model->config('page_peram'):
					$this->model->page = $value;
					$this->model->resultsOffset = $this->generate_results_offset( $value );
					break;
				case $this->model->config('filter_peram'):
					$this->model->filters = $value;
					break;
			}
		}
	}
	/**
	 * Generate Results Offset
	 *
	 * Use the number of results and results per page to
	 * generate offset to paginate.
	 */
	public function generate_results_offset( $page ) {
		if ( $page > 1 ) {
			return $this->model->config('results_per_page') * $page - $this->model->config('results_per_page');
		} else {
			return 0;
		}
	}
}
