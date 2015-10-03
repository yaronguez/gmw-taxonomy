<?php
/*
Plugin Name: GMW Add-on - Taxonomy Locator
Plugin URI: http://www.trestian.com
Description: Add geo-location to a taxonomy, search taxonomies by location and filter results by post
Version: 1.0
Author URI: http://www.trestian.com
Requires at least: 4.0
Tested up to: 4.3
GEO my WP: 2.6.1+
Text Domain: GMW-TX
Domain Path: /languages/
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: https://github.com/yaronguez/gmw-taxonomy
GitHub Branch: master
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GMW_Taxonomy class.
 */
class GMW_Taxonomy {
 
    /**
     * __construct function.
     */
    public function __construct() { 	
        	
        //load Plugin Text domain
        add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
        
    	//the title of the download in geomywp.com
        define( 'GMW_TX_ITEM_NAME', 'Taxonomy Locator' );
        define( 'GMW_TX_TITLE', __( 'Taxonomy Locator', 'GMW-TX' ) );
        define( 'GMW_TX_LICENSE_NAME', 'taxonomies' );
        define( 'GMW_TX_VERSION', '1.0.0' );
        define( 'GMW_TX_DB_VERSION', '1.0.0' );
        define( 'GMW_TX_FILE', __FILE__ );
        define( 'GMW_TX_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( GMW_TX_FILE ) ), basename( GMW_TX_FILE ) ) ) );
        define( 'GMW_TX_PATH',  plugin_dir_path( GMW_TX_FILE ) );
    	
        // init add-on
        add_filter( 'gmw_admin_addons_page', array( $this, 'addon_init' ), 14 );
        
        //make sure GEO my WP is activated and compare version, otherwise abort.
        if ( !class_exists( 'GEO_my_WP' ) || version_compare( GMW_VERSION, '2.6.1', '<' ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice' ) );      
            return;
        }

        //check if addon is activeted via GEO my WP
        if ( !GEO_my_WP::gmw_check_addon( 'taxonomies' ) ) {
            return;
        }

        include(GMW_TX_PATH.'includes/kint/Kint.class.php');

    	//include files
    	//include_once( 'includes/gmw-ps-template-functions.php' );
    	
    	//include global maps functions if add-on exists
    	/*if ( GEO_my_WP::gmw_check_addon( 'global_maps' ) == true ) {
    		include_once( 'includes/gmw-ps-gmaps-functions.php' );
    	}*/
    	
    	if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include_once GMW_TX_PATH . 'includes/admin/gmw-tx-admin.php';
    	} else {
            include_once GMW_TX_PATH . 'includes/public/gmw-tx-public.php';
        }

        //registter scripts
        //add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

    	//info window ajax
    	//add_action( 'wp_ajax_gmw_ps_display_info_window', 		 array( $this, 'info_window_ajax' ) );
    	//add_action( 'wp_ajax_nopriv_gmw_ps_display_info_window', array( $this, 'info_window_ajax' ) );
    }
    
    /**
     * Load plugin textdomain
     * @since 1.6
     */
    public function textdomain() {
        load_plugin_textdomain( 'GMW-TX', FALSE, dirname(plugin_basename(__FILE__)).'/languages/' );
    }
    
    /**
     * Load plugin textdomain
     * @since 1.0
     */
    public function admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'Taxonomy Locator add-on version 1.0 requires GEO my WP plugin version 2.6.1 or higher.', 'GMW-TX' ); ?></p>
        </div>  
        <?php
    }

    /**
     * Initiate add-on
     *
     * @access public
     * @return $addons
     */
    public function addon_init( $addons ) {
    	
    	$addons[GMW_TX_LICENSE_NAME] = array(
    			'name'    	   => GMW_TX_LICENSE_NAME,
                'item'         => GMW_TX_ITEM_NAME,
                'item_id'      => null,
    			'title'   	   => GMW_TX_TITLE,
    			'version' 	   => GMW_TX_VERSION,
    			'file' 	  	   => GMW_TX_FILE,
                'basename'     => plugin_basename( GMW_TX_FILE ),
    			'author'  	   => 'Yaron Guez',
    			'desc'    	   => __( "Add geo location to a taxonomy, search taxonomies by location and filter results by post", "GMW-TX" ),
    			'image'   	   => false,
    			'require' 	   => array(),
    			'license' 	   => false,
                'auto_trigger' => true,
                'min_version'  => false,
                'stand_alone'  => false,
                'core'         => false,
                'gmw_version'  => '2.5'
    	);
    	return $addons;
    }

    /**
     * Register scripts
     */
    public function register_scripts() {
    	    	
    	//register get directions script
        if ( !wp_script_is( 'gmw-get-directions', 'registered' ) ) {
      		//wp_register_script( 'gmw-get-directions', GMW_PS_URL . '/assets/js/get-directions.min.js', array( 'jquery' ), GMW_PS_VERSION, true );
      	}

        //deregister map.js of gmw core
        //wp_deregister_script( 'gmw-map' );
     
        //register map of premium settings
        //wp_register_script( 'gmw-map', GMW_PS_URL . '/assets/js/map.min.js', array( 'jquery', 'google-maps', 'gmw-js' ), GMW_PS_VERSION, true );
    }



    /**
     * Info window ajax function
     */
    function info_window_ajax() {
        /*

        $location = $_POST['location_info'];
        $gmw      = $_POST['form'];

        $location = apply_filters( 'gmw_ps_location_before_info_window', $location, $gmw );

        if ( $gmw['addon'] == 'posts' ) {
            include_once( 'posts/includes/gmw-ps-pt-template-functions.php' );
            include_once( 'posts/includes/gmw-ps-pt-info-window-functions.php' );
        } elseif ( $gmw['addon'] == 'friends' ) {
            include_once( 'friends/includes/gmw-ps-fl-template-functions.php' );
            include_once( 'friends/includes/gmw-ps-fl-info-window-functions.php' );
        }
		
        //hook your info window queries here
        do_action( 'gmw_ps_'.$gmw['prefix'].'_info_window_display', $gmw, $location );
        do_action( 'gmw_ps_info_window_display'                   , $gmw, $location );

        die();
        */
    }
}

new GMW_Taxonomy();
?>