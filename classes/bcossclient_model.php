<?php
class BcOssClientModel {
	// Configuration
	protected $configs;
	protected $template;

	// API Object
	protected $oss_api;

	// GET Data
	public $query;
	public $page;
	public $filters;

	// Result Data
	public $results; // Raw OSS object
	public $bestSpelling;

	// Pagination
	public $resultsOffset;


	public function __construct( $configs ){
		$this->configs = $configs->configs;

		// Set up access to API
		$this->oss_api = $this->set_up_api_connection(
			$this->config('url'),
			$this->config('key'),
			$this->config('login')
		);

		// Default Page Value
		$this->page = 1;

	}
	/**
	 * Configuration
	 *
	 * Get config variable
	 */
	public function config( $config ) {
		return $this->configs[ $config ];
	}

	/**
	 * Error Handling Functions for API
	 *
	 * Check if api is available and functioning during the connection process
	 */

	/**
	 * Check if URL exists
	 *
	 * Returns TRUE if URL does not 404
	 */
	protected function url_exists( $url ) {
		$headers = get_headers( $url );
		if ( substr( $headers[0], 9, 3) !== "404" ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * API Connection
	 *
	 * Functions to get data from the OSS API
	 */
	/**
	 * Set up connection to OSS API
	 *
	 * Returns API object if URL available, otherwise returns WP Error
	 */
	protected function set_up_api_connection ( $url, $key, $login ) {
		if ( $this->url_exists( $url ) ) {
			return new OpenSearchServer\Handler(array(
				'url'   => $url,
				'key'   => $key,
				'login' => $login,
			));
		} else {
			return new WP_Error( 'oss_url_404', __( "Can not connect to search server with configured URL (404 Error)", "bcossclient" ) );
		}
	}

	/**
	 * Get Search Results
	 *
	 * Generic function to load from OSS API
	 */
	protected function get_search_results( $index, $query, $template, $resultsOffset, $resultNumber, $filters ) {
		if ( !empty( $query ) ) {
			$request = new OpenSearchServer\Search\Field\Search();
			$request->index( $index )
				    ->query( $query )
				    ->template( $template )
				    ->start( $resultsOffset )
				    ->rows( $resultNumber );

			// Check for filters (slashes replaced with spaces)
			if ( $this->filters ) {
				$request->filterField('urlSplit', $filters, 'OR', true);
			}

			return $this->oss_api->submit( $request );

		} else {
			// Return false if there is no query
			return false;
		}
	}

	/**
	 * Get Spelling Suggestion Results
	 *
	 * Generic function to load from OSS API
	 */
	protected function get_spelling( $index, $query, $template ) {
		if ( $query ) {
			$request = new OpenSearchServer\SpellCheck\Search();
			$request->index( $index )
				    ->query( $query )
				    ->template( $template );
			return $this->oss_api->submit( $request );
		} else {
			return false;
		}
	}

	/**
	 * Search
	 *
	 * Run search based on configured settings
	 */
	public function search() {
		// Get Search Results
		$this->results = $this->get_search_results(
			$this->config('default_index'),
			$this->query,
			$this->config('default_template'),
			$this->resultsOffset,
			$this->config('results_per_page'),
			$this->filters
		);

		return $this->results;
	}

	/**
	 * Best Spelling Suggestion
	 *
	 * Get 'best' spelling suggestion based on config'd settings
	 */
	public function best_spelling() {
		$result = $this->get_spelling(
			$this->config( 'default_index' ),
			$this->query,
			$this->config( 'spelling_template' )
		);
		$this->bestSpelling = $result->getBestSpellSuggestion('full');
		return $this->bestSpelling;
	}

}
