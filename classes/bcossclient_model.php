<?php
class BcOssClientModel {
	// Configuration
	protected $configs;
	public $sc_configs;
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


	public function __construct( $configs, $sc_config ){
		$this->configs = $configs->configs;
		$this->sc_config = $sc_config;

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
		$this->bestSpelling = $result->getBestSpellSuggestion('fullExact');
		return $this->bestSpelling;
	}
	
	/**
	 * Promoted Results
	 *
	 * Get promoted from custom post type and return as an array
	 */
	public function promoted_results( $tag ) {
		/* Load Configs */
		$custom_post_type = $this->config( 'promoted_cpt' );
		$url_field = $this->config( 'promoted_url' );
		
		/* Return */
		$output = array();
		
		
		/* Set up Query */
		$promoted_query = new WP_Query(
			array( "post_type" => $custom_post_type,
				   "tag" => $tag
			)
		);
		
		while ( $promoted_query->have_posts( ) ) {
			$promoted_query->the_post();
			$result = array(
				'title' =>   the_title( null, null, false ),
				'url'   =>   get_post_meta( get_the_ID(), $url_field , true ),
				'content' => get_the_content(),
			
			);
			$output[] = $result;
		}
		
		wp_reset_query();
		return $output;
	}
}