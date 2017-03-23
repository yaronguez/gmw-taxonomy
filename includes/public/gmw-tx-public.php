<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_TX_Admin class
 */
class GMW_TX_Public {

    /**
     * Construct function
     */
    public function __construct() {
        $this->init_search_queries();
        $this->init_template_functions();
        $this->init_functions();

        // Load templates for output on front end
        add_filter('gmw_search_forms_folder', array($this, 'search_forms'),1, 1);
        add_filter('gmw_search_results_folder', array($this, 'search_results'),1, 1);
    }

    /***
     * Load Search Query Classes
     */
    public function init_search_queries(){
        include_once GMW_TX_PATH . 'includes/public/gmw-tx-search-query-class.php';
        // The Posts by Taxonomy Search requires the Posts Addon
        if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
            include_once GMW_TX_PATH . 'includes/public/gmw-pt-tx-search-query-class.php';
        }
    }

    /**
     * Load template functions
     */
    public function init_template_functions(){
        include_once GMW_TX_PATH . 'includes/public/gmw-tx-template-functions.php';
    }

	public function init_functions(){
		include_once GMW_TX_PATH . 'includes/public/gmw-tx-functions.php';
	}

    public function search_forms($folders){
        if ( GEO_my_WP::gmw_check_addon( 'taxonomies' ) != false ) {
            $folders['tx'] =  array(
                'url' => GMW_TX_URL .'/search-forms/taxonomies/',
                'path' => GMW_TX_PATH .'/search-forms/taxonomies/',
                'custom'=>'taxonomies/search-forms/taxonomies/'
            );

            // The Posts by Taxonomy Search requires the Posts Addon
            if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
                $folders['pt_tx'] = array(
                    'url' => GMW_TX_URL . '/search-forms/posts_taxonomies/',
                    'path' => GMW_TX_PATH . '/search-forms/posts_taxonomies/',
                    'custom' => 'taxonomies/search-forms/posts_taxonomies/'
                );
            }
        }
        return $folders;
    }

    public function search_results($folders){
        if ( GEO_my_WP::gmw_check_addon( 'taxonomies' ) != false ) {
            $folders['tx'] =  array(
                'url' => GMW_TX_URL .'/search-results/taxonomies/',
                'path' => GMW_TX_PATH .'/search-results/taxonomies/',
                'custom'=>'taxonomies/search-results/taxonomies/'
            );

            // The Posts by Taxonomy Search requires the Posts Addon
            if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
                $folders['pt_tx'] = array(
                    'url' => GMW_TX_URL . '/search-results/posts_taxonomies/',
                    'path' => GMW_TX_PATH . '/search-results/posts_taxonomies/',
                    'custom' => 'taxonomies/search-results/posts_taxonomies/'
                );
            }
        }
        return $folders;
    }
}
new GMW_TX_Public();
