<?php 
if ( !class_exists( 'GMW' ) )
	return;

/**
 * GMW_TX_Search_Query class
 * 
 */
class GMW_TX_Search_Query extends GMW {
    
    /**
     * Taxonomy_locator database fields used in the query
     * @var array
     */
    public $db_fields = array( 
        '',
        'name',
        'lat', 
        'long',
        'street', 
        'city', 
        'state', 
        'zipcode', 
        'country', 
        'address', 
        'formatted_address',
        'phone', 
        'fax', 
        'email', 
        'website', 
        'map_icon' 
    );

    /**
     * Modify wp_query clauses to search by distance
     * @param $clauses
     * @return $clauses
     */
    public function query_clauses( $clauses ) {

    	global $wpdb;
		        
        $this->enable_non_located_terms = apply_filters( 'enable_non_located_terms', false, $this->form );
        
        $this->db_fields = implode( ', gmwlocations.', apply_filters( 'gmw_tx_database_fields', $this->db_fields, $this->form ) );

        // If address is specified
        // add the radius calculation and add the locations fields into the results
        if ( !empty( $this->form['org_address'] ) ) {

        	$clauses['join']   .= " INNER JOIN {$wpdb->prefix}taxonomy_locator gmwlocations ON $wpdb->term_taxonomy.term_taxonomy_id = gmwlocations.term_taxonomy_id ";
        	$clauses['fields'] .= $wpdb->prepare( "{$this->db_fields},
        			ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
        			array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );
        	
        	$clauses['where'] .= " AND ( gmwlocations.lat != 0.000000 && gmwlocations.long != 0.000000 ) ";
        	$clauses['having'] = $wpdb->prepare( "HAVING distance <= %d OR distance IS NULL", $this->form['radius'] );
            $clauses['orderby'] = 'distance';


        } else {
        	//if showing posts without location
        	if ( $this->enable_non_located_posts ) {
        		// left join the location table into the query to display posts with no location as well
        		$clauses['join']  .= " LEFT JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->term_taxonomy.term_taxonomy_id = gmwlocations.term_taxonomy_id ";
        		$clauses['where'] .= " ";
        	} else {
        		$clauses['join']  .= " INNER JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->term_taxonomy.term_taxonomy_id = gmwlocations.term_taxonomy_id ";
        		$clauses['where'] .= " AND ( gmwlocations.lat != 0.000000 && gmwlocations.long != 0.000000 ) ";
        	}
        	
        	$clauses['fields'] .= $this->db_fields;       
        }
        
        if ( $this->form['page_load_results_trigger'] ) {
        	
	        //if filtering by city
	        if ( !empty( $this->form['page_load_results']['city_filter'] ) ) {
	        	$clauses['where'] .= " AND gmwlocations.city = '{$this->form['page_load_results']['city_filter']}' ";
	        }
	        
	        //if filtering by state
	        if ( !empty( $this->form['page_load_results']['state_filter'] ) ) {
	        	$clauses['where'] .= " AND ( gmwlocations.state = '{$this->form['page_load_results']['state_filter']}' OR gmwlocations.state_long = '{$this->form['page_load_results']['state_filter']}' ) ";
	        }
	        
	        //if filtering by zipcode
	        if ( !empty( $this->form['page_load_results']['zipcode_filter'] ) ) {
	        	$clauses['where'] .= " AND gmwlocations.zipcode = '{$this->form['page_load_results']['zipcode_filter']}' ";
	        }
	        
	        //if filtering by country
	        if ( !empty( $this->form['page_load_results']['country_filter'] ) ) {
	        	$clauses['where'] .= " AND ( gmwlocations.country = '{$this->form['page_load_results']['country_filter']}' OR gmwlocations.country_long = '{$this->form['page_load_results']['country_filter']}' ) ";
	        }	        
        }

        if(isset($this->form['params']['filter_post_id'])){
            $post_id = $this->form['params']['filter_post_id'];
            if(is_numeric($post_id)) {
                $clauses['join'] .= " INNER JOIN $wpdb->term_relationships ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id ";
                $clauses['join'] .= " INNER JOIN $wpdb->posts ON $wpdb->term_relationships.object_id = $wpdb->posts.ID";
                $clauses['where'] .= " AND $wpdb->posts.ID = " . $post_id;
            }
        }
             
        $clauses = apply_filters( 'gmw_tx_location_query_clauses', $clauses, $this->form );
        $clauses = apply_filters( "gmw_tx_location_query_clauses_{$this->form['ID']}", $clauses, $this->form );

        if ( !empty( $clauses['having'] ) ) {

            if ( empty( $clauses['groupby'] ) ) {
                $clauses['groupby'] = $wpdb->prefix.'term_taxonomy.term_taxonomy_id';
            }
            $clauses['groupby'] .= ' '.$clauses['having'];
            unset( $clauses['having'] );
        } 

        d($clauses);
        return $clauses; 
    }


	/**
	 * Include/exclude taxonomies on page load
	 * @param unknown_type $gmw
	 */
	function include_exclude_tax_custom_query( $clauses ) {

		//on form submission without taxonomies
		if ( !$this->form['submitted'] )
			return $clauses;

		if ( count( $this->form['search_form']['post_types'] ) != 1 )
			return $clauses;

		$postType = $this->form['search_form']['post_types'][0];

		if ( empty( $this->form['search_form']['taxonomies'][$postType] ) )
			return;

		$terms_array = array();
		$tax_array 	 = array();

		foreach ( $this->form['search_form']['taxonomies'][$postType] as $tax => $values ) {

			if ( !empty( $_GET['tax_'.$tax] ) )  {
				$get_tax = sanitize_text_field( $_GET['tax_'.$tax] );

				if ( $get_tax != 0 ) {
					$children    = get_term_children( $get_tax, $tax );
					$terms_array = array_merge( $terms_array, array( $get_tax ), $children );
					$tax_array[] = $tax;
				}
			}
		}

		if ( empty( $terms_array ) )
			return $clauses;

		$posts_id = get_objects_in_term( $terms_array, $tax_array );

		if ( count( $tax_array ) > 1 )
			$posts_id = array_unique( array_diff_assoc( $posts_id, array_unique( $posts_id ) ) );

		if ( empty( $posts_id ) ) {
			$clauses['where'] .= " AND 1 = 2 ";
			return $clauses;
		}

		global $wpdb;

		$clauses['where'] .= $wpdb->prepare( " AND ( {$wpdb->prefix}posts.ID IN (".str_repeat( "%d,", count( $posts_id ) - 1 ) . "%d ) )", $posts_id );

		return $clauses;
	}
	
    /**
     * Display Results
     * @access public
     */
    public function results() {
        d($this->form);

    	//get the taxonomies
        if ( $this->form['page_load_results_trigger'] ) {
        	$taxonomies = ( !empty( $this->form['page_load_results']['taxonomies'] ) ) ? $this->form['page_load_results']['taxonomies'] : array( '' );
        } elseif ( !empty( $_GET[$this->form['url_px'].'taxonomy'] ) ) {
        	$taxonomies = gmw_multiexplode( array( ' ', '+' ), $_GET[$this->form['url_px'].'taxonomy'] );
        } else {
        	$taxonomies = ( !empty( $this->form['search_form']['taxonomies'] ) ) ? $this->form['search_form']['taxonomies'] : array( );
        }

        /*

        //some results caching for the future

        $url_string = $_GET;
        $url_string['paged'] = $this->form['paged'];

        $query_args_hash = 'gmw-' . md5( json_encode( $url_string ) . GEO_my_WP_Cache_Helper::get_transient_version( 'gmw-pt-search-results' ) );

        echo $query_args_hash;
        */
        //if ( false === ( $gmw_query = get_transient( $query_args_hash ) ) ) {

        global $wpdb;

        $clauses['select'] 	 = "SELECT SQL_CALC_FOUND_ROWS";
        $clauses['distinct'] = "";
        $clauses['fields'] 	 = "$wpdb->term_taxonomy.*";
        $clauses['from']	 = $wpdb->term_taxonomy;
        $clauses['join']	 = "";
        $clauses['where']    = $wpdb->prepare( "AND $wpdb->term_taxonomy.taxonomy IN (".str_repeat( "%s,", count( $taxonomies ) - 1 ) . "%s )", $taxonomies );
        $clauses['groupby']	 = "";
        $clauses['having']   = "";
        $clauses['orderby']  = "$wpdb->posts.post_date DESC";
        $clauses['limits']   = "";

        if ( !empty( $this->form['get_per_page'] ) ) {

            $stating_page     = ( $this->form['paged'] == 1 ) ? 0 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) );
            $clauses['limits'] = "LIMIT {$stating_page},{$this->form['get_per_page']}";
        }

        add_filter( 'gmw_tx_filter_custom_query_clauses', array( $this, 'query_clauses' 				   ) );
        //add_filter( 'gmw_tx_filter_custom_query_clauses', array( $this, 'include_exclude_tax_custom_query' ) );

        //Hooks before query
        $this->form  = apply_filters( 'gmw_tx_form_before_custom_posts_query', $this->form 		  );
        $clauses 	 = apply_filters( 'gmw_tx_filter_custom_query_clauses', $clauses, $this->form );

        remove_filter( 'gmw_tx_filter_custom_query_clauses', array( $this, 'query_clauses' 				   ) );
        //remove_filter( 'gmw_tx_filter_custom_query_clauses', array( $this, 'include_exclude_tax_custom_query' ) );

        if ( !empty( $clauses['groupby'] ) ) {
            $clauses['groupby'] = 'GROUP BY ' . $clauses['groupby'];
        }
        if ( !empty( $clauses['orderby'] ) ) {
            $clauses['orderby'] = 'ORDER BY ' . $clauses['orderby'];
        }

        $request = "{$clauses['select']} DISTINCT {$clauses['fields']} FROM {$clauses['from']} {$clauses['join']} WHERE 1=1 {$clauses['where']} {$clauses['groupby']} {$clauses['orderby']} {$clauses['limits']}";

        d($request);
        $this->form['results']       = $wpdb->get_results( $request );
        $foundRows 					 = $wpdb->get_row( "SELECT FOUND_ROWS()", ARRAY_A );
        $this->form['results_count'] = count( $this->form['results'] );
        $this->form['total_results'] = $foundRows['FOUND_ROWS()'];
        $this->form['max_pages']     = ( empty( $this->form['get_per_page'] ) || $this->form['get_per_page'] == 1 ) ? 1 : $this->form['total_results']/$this->form['get_per_page'];

        //Modify the form values before the loop
        $this->form = apply_filters( 'gmw_tx_form_before_posts_loop', $this->form, $this->settings );
        $this->form = apply_filters( "gmw_tx_form_before_posts_loop_{$this->form['ID']}", $this->form, $this->settings );

        //enqueue stylesheet and get results template file
        $results_template = $this->results_template();

        //check if we got results and if so run the loop
        if ( !empty( $this->form['results'] ) ) {

            $this->form['post_count'] = ( $this->form['paged'] == 1 ) ? 1 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) ) + 1;

            add_action( 'gmw_search_results_loop_item_start', array( $this, 'modify_taxonomy_term' ), 1 );

            do_action( 'gmw_tx_have_posts_start', $this->form, $this->settings );
            do_action( "gmw_tx_have_posts_start_{$this->form['ID']}", $this->form, $this->settings );

            //call results template file
            if ( isset( $this->form['search_results']['display_taxonomy_terms'] ) ) {
            	$gmw = $this->form;
                include( $results_template );
            /*
             * in case that we do not display posts we still run the loop on "empty" in order
             * to add element to the info windows of the map
             */
            } elseif ( $this->form['search_results']['display_map'] != 'na' ) {
                foreach ( $this->form['results'] as $key => $taxonomy_term ) {
                    $this->form['results'][$key] = self::modify_taxonomy_term( $taxonomy_term );
                }
            }

            do_action( 'gmw_tx_have_posts_end', $this->form, $this->settings );
            do_action( "gmw_tx_have_posts_end_{$this->form['ID']}", $this->form, $this->settings );

            remove_action( 'gmw_search_results_loop_item_start', array( $this, 'modify_taxonomy_term' ), 1 );
        }
    }
    
    /**
     * GMW Function - append location details to permalink
     * @param $url
     * @since 2.5
     */
    public function append_address_to_term_link( $url ) {

    	if ( empty( $this->form['org_address'] ) )
    		return $url;

    	$url_args = array(
    			'address' 	=> str_replace( ' ', '+', $this->form['org_address'] ),
    			'lat'	  	=> $this->form['your_lat'],
    			'lng'	  	=> $this->form['your_lng'],
    			//'distance'	=> $this->form['results'],
    			'units'	    => $this->form['units_array']['name']
    	);

    	return apply_filters( 'gmw_tx_post_permalink', $url. '?'.http_build_query( $url_args ), $url, $url_args );
    }
   
    /**
     * Modify each taxonomy term within the loop
     */
    public function modify_taxonomy_term( $taxonomy_term ) {

    	add_filter( 'term_link', array( $this, 'append_address_to_term_link') );

        // add permalink and thumbnail into each post in the loop
        // we are doing it here to be able to display it in the info window of the map
        $taxonomy_term->post_count     	   = $this->form['post_count'];
        $taxonomy_term->mapIcon        	   = apply_filters( 'gmw_tx_map_icon', 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='.$taxonomy_term->post_count.'|FF776B|000000', $taxonomy_term, $this->form );
        $taxonomy_term->info_window_content = self::info_window_content( $taxonomy_term );

        $this->form['post_count']++;

        do_action( 'gmw_tx_loop_taxonomy', $taxonomy_term, $this->form, $this->settings );

        $taxonomy_term = apply_filters( "gmw_tx_loop_modify_taxonomy_term", $taxonomy_term, $this->form, $this->settings );
        $taxonomy_term = apply_filters( "gmw_tx_loop_modify_the_post_{$this->form['ID']}", $taxonomy_term, $this->form, $this->settings );

        return $taxonomy_term;

    }
    
    /**
     * Create the content of the info window
     * @since 2.5
     * @param unknown_type $taxonomy_term
     */
    public function info_window_content( $taxonomy_term ) {
    	$address   = ( !empty( $taxonomy_term->formatted_address ) ) ? $taxonomy_term->formatted_address : $taxonomy_term->address;
    	$permalink = get_term_link( (int)$taxonomy_term->term_taxonomy_id, $taxonomy_term->taxonomy );

    	$output  			     = array();
    	$output['start']		 = "<div class=\"gmw-pt-info-window-wrapper wppl-pt-info-window\">";
    	$output['content_start'] = "<div class=\"content wppl-info-window-info\"><table>";
    	$output['title'] 		 = "<tr><td><div class=\"title wppl-info-window-permalink\"><a href=\"{$permalink}\">{$taxonomy_term->name}</a></div></td></tr>";
    	$output['address'] 		 = "<tr><td><span class=\"address\">{$this->form['labels']['info_window']['address']}</span>{$address}</td></tr>";

    	if ( isset( $taxonomy_term->distance ) ) {
    		$output['distance'] = "<tr><td><span class=\"distance\">{$this->form['labels']['info_window']['distance']}</span>{$taxonomy_term->distance} {$this->form['units_array']['name']}</td></tr>";
    	}

    	if ( !empty( $this->form['search_results']['additional_info'] ) ) {

    		foreach ( $this->form['search_results']['additional_info'] as $field ) {
	    		if ( isset( $taxonomy_term->$field ) ) {
	    			$output[$this->form['labels']['info_window'][$field]] = "<tr><td><span class=\"{$this->form['labels']['info_window'][$field]}\">{$this->form['labels']['info_window'][$field]}</span>{$taxonomy_term->$field}</td></tr>";
	    		}
    		}
    	}

    	$output['content_end'] = "</table></div>";
    	$output['end'] 		   = "</div>";

    	$output = apply_filters( 'gmw_tx_info_window_content', $output, $taxonomy_term, $this->form );
    	$output = apply_filters( "gmw_tx_info_window_content_{$this->form['ID']}", $output, $taxonomy_term, $this->form );

    	return implode( ' ', $output );
    }
}
?>