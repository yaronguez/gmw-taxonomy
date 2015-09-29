<?php
if ( !class_exists( 'GMW' ) )
    return;

/**
 * GMW_TX_Search_Query class
 *
 */
class GMW_TX_Extend_PT{
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
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct(){

        add_filter('enable_non_located_posts', array($this, 'show_posts_without_location'), 10, 2);
        add_filter('gmw_pt_location_query_clauses', array($this, 'include_taxonomy_address'), 10, 2);

    }

    /**
     * If a Posts form uses the address_taxonomy parameter in its shortcode, include posts that don't have a location result
     * @param $result
     * @param $form
     * @return bool
     */
    public function show_posts_without_location($result, $form){
        if($form['form_type'] == 'posts' && isset($form['params']['address_taxonomy'])){
            $result = true;
        }
        return $result;
    }

    public function include_taxonomy_address($clauses, $form){
        if($form['form_type'] != 'posts' || !isset($form['params']['address_taxonomy'])){
            return $clauses;
        }

        global $wpdb;

        $db_fields = array();
        foreach($this->db_fields as $field){
            $db_fields[] = "FIRST(gmw_tax_locations.$field) as tax_$field";
        }
        $db_fields_string = implode( ',', $db_fields);

        // If address is specified
        // add the radius calculation and add the locations fields into the results
        if ( !empty( $form['org_address'] ) ) {
            $clauses['join'] .= " INNER JOIN $wpdb->term_relationships gmwtr ON $wpdb->posts.ID = gmwtr.object_id";
            $clauses['join'] .= " INNER JOIN $wpdb->term_taxonomy gmwtt ON $wpdb->term_relationships.term_taxonomy_id = gmwtt.term_taxonomy_id";
            $clauses['join']   .= " INNER JOIN {$wpdb->prefix}taxonomy_locator gmw_tax_locations ON $wpdb->term_taxonomy.term_taxonomy_id = gmw_tax_locations.term_taxonomy_id ";
            $clauses['fields'] .= $wpdb->prepare( "$db_fields_string,
        			FIRST(ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmw_tax_locations.lat ) ) * cos( radians( gmw_tax_locations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmw_tax_locations.lat) ) ),1 )) AS tax_distance",
                array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );

            $clauses['where'] .= " AND ( gmw_tax_locations.lat != 0.000000 && gmw_tax_locations.long != 0.000000 ) ";
            $clauses['where'] .= $wpdb->prepare(" AND $wpdb->term_taxonomy.taxonomy = %s ", $form['params']['address_taxonomy']);
            $clauses['having'] = $wpdb->prepare( "HAVING tax_distance <= %d OR tax_distance IS NULL", $this->form['radius'] );
            $clauses['orderby'] = 'tax_distance';
            $clauses['group_by'] = "$wpdb->posts.ID";
        } /*else {
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
        }*/
        d($clauses);
        die();
        return $clauses;

    }
}
new GMW_TX_Extend_PT();
    