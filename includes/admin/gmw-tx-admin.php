<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_TX_Admin class
 */
class GMW_TX_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->init_db();
        $this->init_metaboxes();
        $this->init_address_column();
        $this->init_taxonomies_form();
        $this->init_posts_taxonomies_form();
    }

    public function init_db(){
        include_once GMW_TX_PATH . 'includes/admin/gmw-tx-db.php';
    }

    /**
     * Add Address meta boxes to taxonomy pages
     */
    public function init_metaboxes(){
        //check if we are in new/edit taxonomy page
        if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'edit-tags.php','term.php') ) ) {
            include_once GMW_TX_PATH . 'includes/admin/gmw-tx-metaboxes.php';
        }
    }

    /**
     * Add Address column to taxonomy listing page
     */
    public function init_address_column(){
        $taxonomies = gmw_get_option( 'taxonomy_settings', 'taxonomies', array() );
        foreach ($taxonomies  as $taxonomy ) {
            add_filter( "manage_edit-{$taxonomy}_columns" , array( $this, 'add_address_column' ) );
            add_filter( "manage_{$taxonomy}_custom_column" , array( $this, 'address_column_content' ), 10, 3 );
        }
    }

    public function init_taxonomies_form(){
        include_once GMW_TX_PATH . 'includes/admin/gmw-tx-form.php';
    }

    public function init_posts_taxonomies_form(){
        // The Posts search form requires the posts add on
        if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
            include_once GMW_TX_PATH . 'includes/admin/gmw-pt-tx-form.php';
        }

    }

    /**
     * Add "address" column to taxonomy listings
     * @param  array $columns columns
     * @return columns
     */
    public function add_address_column( $columns ) {
        $columns['gmw_address'] = __( 'Location', 'GMW' );
        return $columns;
    }

    /**
     * Add content to custom column
     * @param  [type] $column  [description]
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function address_column_content( $out, $column, $tax_id ) {

        if ( $column != 'gmw_address' )
            return;

        global $wpdb;

        $address_ok = false;

        $location = $wpdb->get_row(
            $wpdb->prepare("
                        SELECT formatted_address, address FROM {$wpdb->prefix}taxonomy_locator
                        WHERE `term_taxonomy_id` = %d", array( $tax_id )
            ) );

        if ( empty( $location ) ) {
            echo '<i class="fa fa-times-circle" style="color:red;margin-right:5px;"></i>'.__( 'No location found', "GMW" );
            return;
        }

        if ( !empty( $location->formatted_address ) ) {
            $address_ok = true;
            $address = $location->formatted_address;
        } elseif ( !empty( $location->address ) ) {
            $address_ok = true;
            $address = $location->address;
        } else {
            $address =  __( 'Location found but the address is missing', "GMW" );
        }

        $address = ( $address_ok == true ) ? '<a href="http://maps.google.com/?q='.$address.'" target="_blank" title="location">'.$address.'</a>' : '<span style="color:red">'.$address.'</span>';
        echo '<i class="fa fa-check-circle" style="color:green;margin-right:5px;" style="color:green"></i>'. $address;

    }
}
new GMW_TX_Admin();
?>