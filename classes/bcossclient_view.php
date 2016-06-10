<?php
class BcOssClientView {

	// Set up variables
	private $model;
	private $controller;

	/**
	 * Constructor
	 *
	 * Store data to variables
	 */
	public function __construct($controller,$model) {
		$this->controller = $controller;
		$this->model = $model;
	}

	/**
	 * Helper functions
	 *
	 * Process data from the model in various ways
	 */

	/**
	 * Page URL Builder
	 *
	 * Rebuild URL query around new page permameter
	 */
	protected function page_url( $page ) {
		$query = $_GET;
		// replace parameter(s)
		$query[$this->model->config( 'page_peram' )] = $page;
		// rebuild url
		$query_result = http_build_query( $query );
		// new link
		return '?'. htmlentities( $query_result );
	}

	/**
	 * Search Term URL Builder
	 *
	 * Rebuild URL query around new Search/Query permameter
	 */
	protected function searchterm_url( $searchterm ) {
		$query = $_GET;
		$query[$this->model->config( 'query_peram' )] = $searchterm;
		$query_result = http_build_query( $query );
		return '?'. htmlentities( $query_result );
	}


	/**
	 * Modules
	 *
	 * Build HTML for output
	 */

	/**
	 * Results module
	 *
	 * Output results data in HTML format
	 */
	protected function results( $results ) {
		$output = '';

		foreach( $results as $key => $result ) {
			$output .= '<article class="row-padding">';
			if ( $result->getSnippet( 'title' ) != '' ) {
				$output .= '<h3><a href="' . $result->getField('url') . '">'.$result->getSnippet('title').'</a></h3>';
				$output .= '<p><span class="result-url text-success">' . $result->getField('url').'</span></p>';
			} else {
				$output .= '<h3><a href="' . $result->getField('url') . '">' . $result->getField('url') . '</a></h3>';
			}
			$output .= '<p>'.$result->getSnippet('content').'</p>';
			$output .= '</article>';
		}
		return $output;
	}
	
	/**
	 * Promoted Results module
	 *
	 * Output promoted results data in HTML format
	 */
	protected function promoted_results( $promoted_results ) {
		$output = '';

		foreach( $promoted_results as $promoted_result ) {
			$output .= '<article class="row-padding well well-sm">';
			$output .= '<h3><a href="' . $promoted_result['url'] . '">' . $promoted_result['title'] . "</a></h3>";
			$output .= '<p><span class="result-url text-success">' . $promoted_result['url'] . '</span></p>';
			$output .= '<p>'. $promoted_result['content'] . '</p>';
			$output .= '</article>';
		}

		return $output;
	}
	

	/**
	 * Pagination module
	 *
	 * Build HTML for pagination of results
	 */
	protected function pagination( $page, $num_results ) {
		$output = '';

		// Prevent output if there are too few results
		if ( $num_results <= $this->model->config( 'results_per_page' ) ) {
			return; // no output
		}

		// Create var to show number of pages. Round up.
		$numPages = ceil( $num_results / $this->model->config('results_per_page') );

		// Start Bootstrap pagination structure
		$output = '<nav class="text-center"><ul class="pagination">';

		// Check if is first page to disable 'Previous' link
		if ( $page == 1 ) {
			$output .= '<li class="disabled"><span>
				        <span aria-hidden="true">&laquo;</span></span></li>';
		} else {
			$output .= '<li><a href="' . $this->page_url( $page - 1 ) . '" aria-label="Previous Page">
				        <span aria-hidden="true">&laquo;</span></a></li>';
		}

		// Truncate long lists of pages
		if ( $numPages > 5 ) {
			// Set Start Page
			$startPage;
			if ( $page < 3 ) {
				$startPage = 1;
			} elseif ( $page > $numPages - 2 ) {
				$output .= '<li><a href="' . $this->page_url( 1 ) . '">1 ... </a></li>';
				$startPage = $numPages - 2;
			} else {
				$output .= '<li><a href="' . $this->page_url( 1 ) . '">1 ... </a></li>';
				$startPage = $page - 1;
			}

			// Output pages
			for ( $i = $startPage; $i <= $startPage + 2; $i++ ) {
				if ( $i == $page ) {
					$output .= '<li class="active"><a href="' . $this->page_url( $i ) . '">'.$i.'</a></li>';
				} else {
					$output .= '<li><a href="' . $this->page_url( $i ) . '">'.$i.'</a></li>';
				}
			}

			if ( $page < $numPages - 2  ) {
				$output .= '<li><a href="' . $this->page_url( $numPages ) . '"> ... ' . $numPages . '</a></li>';
				$startPage = $page - 1;
			}

		} else { // If pages are less than max
			// Loop through number of pages
			for ( $i = 1; $i <= $numPages; $i++ ) {
				if ( $i == $page ) {
					$output .= '<li class="active"><a href="' . $this->page_url( $i ) . '">'.$i.'</a></li>';
				} else {
					$output .= '<li><a href="' . $this->page_url( $i ) . '">'.$i.'</a></li>';
				}
			}
		}

		// Output end arrow
		if ( $page == $numPages ) {
			$output .= '<li class="disabled"><span>
				<span aria-hidden="true">&raquo;</span>
				</span></li>';
		} else {
			$output .= '<li><a href="' . $this->page_url( $page + 1 ) . '" aria-label="Next">
				<span aria-hidden="true">&raquo;</span>
				</a></li>';
		}
		$output .= '</ul></nav>';

		return $output;
	}

	/**
	 * Search Box module
	 *
	 * Display HTML search box
	 */
	protected function searchbox ( $query, $filters ) {
		$output = '';
		$output .= '<form action="" method="get">
		<div class="row row-padding">
		<div class="col-md-12" role="search">
		<label class="sr-only" for="txtQuery">Search Bellevue College:</label>
		<div class="input-group input-group-lg">';
		if ( $filters ) {
			foreach( $filters as $filter ) {
				$output .= '<input type="hidden" class="form-control" name="filter[]" value="' . htmlentities( stripslashes( $filter ) ) . '">';
			}
		}
		$output .= '<input type="text" class="form-control" name="txtQuery" id="txtQuery" value="' . htmlentities( stripslashes( $query ) ) . '">
		<span class="input-group-btn">
		<button class="btn btn-primary" type="submit">Search</button>
		</span>
		</div><!-- /input-group -->
		</div><!-- /.col-md-12 -->
		</div><!-- /.row -->
		</form>';
		return $output;
	}


	/**
	 * Final output
	 *
	 * Process data from the model in various ways
	 */
	public function output() {
		$output = '';

		// Output searchbox
		$output .= $this->searchbox( $this->model->query, $this->model->filters );

		// Load results for query
		$results = $this->model->search();

		// Check for filters
		if ( $this->model->filters ) {
			$output .= '<p>Results filtered to';
			foreach( $this->model->filters as $filter ) {
				$output .= ' <span class="label label-default">'. htmlspecialchars( stripslashes( $filter ) ).'</span> ';
			}
			$output .= '</p>';
		}

		// If there are results to display
		if ( $results && ( $results->getTotalNumberFound() > 0 ) ) {

			$output .= '<h2>' . __( 'Search results:', 'bcossclient' ) . '</h2>';
			$output .= '<p>' . __( 'Found ', 'bcossclient' ) . $results->getTotalNumberFound() . __( ' results for ', 'bcossclient' ) .
				        '"'. htmlentities( stripslashes( $this->model->query ) ).'" (' . $results->getTime()/1000 . __( ' seconds) ', 'bcossclient' );
			
			/* Check if spelling suggestion should be made. Resource intensive. */
			if ( $this->model->sc_config[ 'spelling' ] && $results->getTotalNumberFound() <= $this->model->config( 'results_per_page' ) ) {
				// Get spelling suggestion
				$spelling = $this->model->best_spelling();

				// Check if suggestion is the same as the query itself
				if ( strcasecmp ( $spelling, $this->model->query ) !== 0  &&  !empty( $spelling ) )  {
					$output .= '<p class="alert alert-warning"><strong>Suggestion:</strong> Did you mean <strong><a href=' . $this->searchterm_url( $spelling ) . '>' . $spelling . '</a></strong>?</p>';
				}
			}
			
			// Load Promoted Search Results
			if ( !$this->model->filters ) {
				
				/* Convert query to tag format.
				 *
				 * Must be exact match to return (case insensitive)
				 */
				$tag = sanitize_title_with_dashes( $this->model->query );
				
				// Fetch results
				$promoted_results = $this->model->promoted_results( $tag );
				
				// Output results
				$output .= $this->promoted_results( $promoted_results );
				
			}


			// Output results
			$output .= $this->results( $results );

			// Output pagination
			$output .= $this->pagination( $this->model->page, $results->getTotalNumberFound() );

		// If no results to display, but query present
		} elseif ( $this->model->query ) {

			// Output error
			$output .= '<p>No Results to Display. Please revise your search terms!</p>';

			if ( $this->model->sc_config[ 'spelling' ] ) {
				// Get spelling suggestion
				$spelling = $this->model->best_spelling();

				// Check if suggestion is the same as the query itself
				if ( strcasecmp ( $spelling, $this->model->query ) !== 0 &&  !empty( $spelling ) ) {
					$output .= '<p>Try searching for <a href=' . $this->searchterm_url( $spelling ) . '>' . $spelling . '</a> instead!</p>';
				}
			}
		// No query provided
		} else {
			// Error
			$output .= '<p>Please enter a search term. For example, you could search for \'bookstore\'.</p>';
		}
		return $output;
	}

}
