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
        if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'edit-tags.php') ) ) {
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
        include_once GMW_TX_PATH . 'includes/admin/gmw-pt-tx-form.php';
    }
}
new GMW_TX_Admin();
?>