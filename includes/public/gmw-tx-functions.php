<?php
/**
 * GMW TX function - get taxonomy location from database or cache
 * @param $tt_id
 */
function gmw_get_taxonomy_location_from_db( $tt_id ) {

    global $wpdb;

    $location = wp_cache_get( 'gmw_taxonomy_location', $group = $tt_id );

    if ( false === $location ) {

    	$location = $wpdb->get_row(
    			$wpdb->prepare("
    					SELECT * FROM {$wpdb->prefix}taxonomy_locator
    					WHERE `term_taxonomy_id` = %d", array( $tt_id )
    			) );

        wp_cache_set( 'gmw_taxonomy_location', $location, $tt_id );
    }

    return ( isset( $location ) ) ? $location : false;
}

/**
 * GMW Function - get taxonomy location information
 */
function gmw_get_taxonomy_info( $args ) {

	//default shortcode attributes
	extract(
			shortcode_atts(
					array(
							'info'    => 'formatted_address',
							'tt_id' => 0,
							'divider' => ' '
					), $args )
	);
	/**
	 * @var $info string
	 * @var $tt_id int
	 * @var $divider string
	 */

    /*
     * check if user entered taxonomy term id
     */
    if ( $tt_id == 0 ) {

	    $tt_id = get_queried_object()->term_id;

    }

    $post_info = gmw_get_taxonomy_location_from_db( $tt_id);

    $info = explode( ',', str_replace( ' ', '', $info ) );

    $output = '';
    $count  = 1;

    foreach ( $info as $rr ) {
        if ( isset( $post_info->$rr ) ) {
            $output .= $post_info->$rr;

            if ( $count < count( $info ) )
                $output .= $divider;
            $count++;
        }
    }
    return $output;

}
add_shortcode( 'gmw_taxonomy_info', 'gmw_get_taxonomy_info' );

function gmw_taxonomy_info( $args ) {
	echo gmw_get_taxonomy_info( $args );
}


/**
 *  delete info from our database after taxonomy was deleted
 */
function gmw_tx_delete_location($term, $tt_id, $taxonomy) {
	global $wpdb;
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}taxonomy_locator WHERE `tt_id` = %d", array( $tt_id) ) );
}
add_action( 'delete_term', 'gmw_tx_delete_location', 10, 3 );