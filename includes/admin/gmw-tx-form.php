<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_TX_Admin class
 */
class GMW_TX_Form {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        // Add new taxonomy settings tab to the main GMW settings page
        add_filter( 'gmw_admin_settings', array( $this, 'settings_init' 		), 1 );

        // Populate fields in the taxonomy settings page
        add_action( 'gmw_main_settings_taxonomies', array( $this, 'main_settings_taxonomies' ), 1, 4 );

        // Shortcode documentation
        add_filter( 'gmw_admin_shortcodes_page', array( $this, 'shortcodes_page' 	),1 , 10 );

        // Add New Form button
        add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_button' 	), 1, 1 );

        // Setup Form Settings page
        add_filter( 'gmw_taxonomies_form_settings', 	 array( $this, 'form_settings_init' ), 1, 1 );

        // Populate fields in the taxonomy form settings tab
        add_action( 'gmw_taxonomies_form_settings_taxonomies', array( $this, 'form_settings_taxonomies' ), 1, 4 );
        add_action( 'gmw_taxonomies_form_settings_address_field',   'GMW_Edit_Form::form_settings_address_field' , 10, 4 );

        // Include search-forms and results-forms locations in the new form settings
        add_filter('gmw_admin_search_forms_folder', array($this, 'search_forms_folder'), 1, 1);
        add_filter('gmw_admin_results_templates_folder', array($this, 'search_results_folder'), 1, 1);
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
     * @param  [type] $tax_id [description]
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

    /**
     * addon settings page function.
     *
     * @access public
     * @return $settings
     */
    public function settings_init( $settings ) {

        $settings['taxonomy_settings'] = array(
            __( 'Taxonomies', 'GMW' ),
            array(
                'edit_taxonomy_zoom_level' => array(
                    'name'    => 'edit_taxonomy_zoom_level',
                    'std'     => '7',
                    'label' 	 => __( "\"Edit Taxonomy Term\" page - map's zoom level", "GMW" ),
                    'desc'  	 => __( "Set the default zoom level of the map being displayed in \"GMW section\" of the \"Edit Taxonomy Term\" page." , "GMW" ),
                    'type'    => 'select',
                    'options' => array(
                        '1'    => '1',
                        '2'    => '2',
                        '3'    => '3',
                        '4'    => '4',
                        '5'    => '5',
                        '6'    => '6',
                        '7'    => '7',
                        '8'    => '8',
                        '9'    => '9',
                        '10'   => '10',
                        '11'   => '11',
                        '12'   => '12',
                        '13'   => '13',
                        '14'   => '14',
                        '15'   => '15',
                        '16'   => '16',
                        '17'   => '17',
                        '18'   => '18',
                    )
                ),
                'edit_taxonomy_latitude' => array(
                    'name'  	 => 'edit_taxonomy_latitude',
                    'std'   	 => '40.7115441',
                    'label' 	 => __( "\"Edit Taxonomy Term\" page - default latitude", "GMW" ),
                    'desc'  	 => __( "Set the latitude of the default location being displayed in \"GMW section\" of the \"Edit Taxonomy Term\" page." , "GMW" ),
                    'type'  	 => 'text',
                    'attributes' => array()
                ),
                'edit_taxonomy_longitude' => array(
                    'name'  	 => 'edit_taxonomy_longitude',
                    'std'   	 => '-74.01348689999998',
                    'label' 	 => __( "\"Edit Taxonomy\" page - default longitude", "GMW" ),
                    'desc'  	 => __( "Set the longitude of the default location being displayed in \"GMW section\" of the \"Edit Taxonomy Term\" page." , "GMW" ),
                    'type'  	 => 'text',
                    'attributes' => array()
                ),
                array(
                    'name'  => 'taxonomies',
                    'std'   => '',
                    'label' => __( 'Taxonomies', 'GMW' ),
                    'desc'  => __( "Check the checkboxes of the taxonomies which you'd like to add locations to. GEO my WP's location section will be displayed in the new/edit taxonomy term screen of the taxonomies you choose here. ", 'GMW' ),
                    'type'  => 'function'
                ),
                array(
                    'name'       => 'mandatory_address',
                    'std'        => '',
                    'label'      => __( 'Mandatory Address fields', 'GMW' ),
                    'cb_label'   => __( 'Yes', 'GMW' ),
                    'desc'       => __( 'Check this box if you want to make sure that users will add location to a taxonomy term they create or update; It will prevent them from saving a taxonomy term that does not have a location. Otherwise, users will be able to save a taxonomy term even without a location. This way the taxonomy term will be published and would show up in Wordpress search results but not in GEO my WP search results.', 'GMW' ),
                    'type'       => 'checkbox',
                    'attributes' => array()
                ),
            ),
        );

        return $settings;
    }

    /**
     * New form button function.
     *
     * @access public
     * @return $buttons
     */
    public function new_form_button( $buttons ) {

        $buttons[] = array(
            'name'       => 'taxonomies',
            'addon'      => 'taxonomies',
            'title'      => __( 'Taxonomy Locator', 'GMW' ),
            'link_title' => __( 'Create new taxonomies form', 'GMW' ),
            'prefix'     => 'tx',
            'color'      => 'C3D5E6'
        );
        return $buttons;

    }

    /**
     * Taxonomy main settings
     */
    public function main_settings_taxonomies( $gmw_options, $section, $option ) {
        $saved_data = ( isset( $gmw_options[$section]['taxonomies'] ) ) ? $gmw_options[$section]['taxonomies'] : array();
        ?>
        <div>
            <?php foreach ( get_taxonomies(array(), 'objects') as $taxonomy ) { ?>
                <?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $taxonomy->name, $saved_data ) ) ? ' checked="checked"' : ''; ?>
                <p><label>
                        <input type="checkbox" name="<?php echo 'gmw_options[' . $section . '][taxonomies][]'; ?>" value="<?php echo $taxonomy->name; ?>" id="<?php echo $taxonomy->name; ?>" class="post-types-tax" <?php echo $checked; ?>>
                        <?php echo  $taxonomy->labels->name . ' ( '. $taxonomy->name .' ) '; ?>
                    </label></p>
            <?php } ?>
        </div>
    <?php
    }

    /**
     * Taxonomy form settings
     */
    public function form_settings_taxonomies( $gmw_forms, $formID, $section, $option ) {
        $saved_data = ( isset( $gmw_forms[$formID][$section]['taxonomies'] ) ) ? $gmw_forms[$formID][$section]['taxonomies'] : array();
        ?>
        <div class="posts-checkboxes-wrapper" id="<?php echo $formID; ?>">
            <?php foreach ( get_taxonomies(array(),'objects') as $taxonomy ) { ?>
                <?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $taxonomy->name, $saved_data ) ) ? ' checked="checked"' : ''; ?>
                <p>
                    <label><input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][taxonomies][]'; ?>" value="<?php echo $taxonomy->name; ?>" id="<?php echo $taxonomy->name; ?>" class="post-types-tax" <?php echo $checked; ?> />
                        <?php echo $taxonomy->labels->name . ' ( '. $taxonomy->name .' ) '; ?></label>
                </p>
            <?php } ?>
        </div>
    <?php
    }

    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    function form_settings_init( $settings ) {

        //page load features
        $newValues = array(

            'taxonomies'     => array(
                'name'    => 'taxonomies',
                'std'     => '',
                'label'   => __( 'Taxonomies', 'GMW' ),
                'cb_label'=> '',
                'desc'    => __( 'Choose the taxonomies you would like to display.', 'GMW' ),
                'type'    => 'function'
            ),
        );

        $afterIndex = 0;
        $settings['page_load_results'][1] = array_merge( array_slice( $settings['page_load_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['page_load_results'][1], $afterIndex + 1 ) );

        //search form features
        $newValues = array(
            'taxonomies' => array(
                'name'     		=> 'taxonomies',
                'std'      		=> '',
                'label'    		=> __( 'Taxonomies', 'GMW' ),
                'cb_label' 		=> '',
                'desc'     		=> __( "Check the checkboxes of the taxonomies you'd like to display in the search form. When selecting multiple taxonomies they will be displayed as a dropdown menu.", 'GMW' ),
                'type'     		=> 'function',
            ),
        );

        $afterIndex = 0;
        $settings['search_form'][1] = array_merge( array_slice( $settings['search_form'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_form'][1], $afterIndex + 1 ) );

        //search results features
        unset( $settings['search_results'][1]['auto_results'], $settings['search_results'][1]['auto_all_results'] );
        $newValues = array(


            'display_taxonomy_terms'    => array(
                'name'     => 'display_taxonomy_terms',
                'std'      => '',
                'label'    => __( 'Display Taxonomy Terms?', 'GMW' ),
                'desc'     => __( 'Display results as list of taxonomy terms', 'GMW' ),
                'type'     => 'checkbox',
                'cb_label' => __( 'Yes', 'GMW' ),
            ),
            'additional_info'  => array(
                'name'    => 'additional_info',
                'std'     => '',
                'label'   => __( 'Contact Information', 'GMW' ),
                'desc'    => __( "Check the checkboxes of the contact information which you'd like to display per location in the search results.", 'GMW' ),
                'type'    => 'multicheckbox',
                'options' => array(
                    'phone'   => __( 'Phone', 'GMW' ),
                    'fax'     => __( 'Fax', 'GMW' ),
                    'email'   => __( 'Email', 'GMW' ),
                    'website' => __( 'Website', 'GMW' ),
                ),
            ),
            'opening_hours'  => array(
                'name'     => 'opening_hours',
                'std'      => '',
                'label'    => __( 'Show opening hours', 'GMW' ),
                'cb_label' => __( 'Yes', 'GMW' ),
                'desc'     => __( 'Display opening days & hours.', 'GMW' ),
                'type'     => 'checkbox'
            ),
            'show_description'     => array(
                'name'     => 'show_description',
                'std'      => '',
                'label'    => __( 'Show description', 'GMW' ),
                'cb_label' => __( 'Yes', 'GMW' ),
                'desc'     => __( 'Display the taxonomy term\'s description in the list of results.', 'GMW' ),
                'type'     => 'checkbox'
            )
        );

        $afterIndex = 3;
        $settings['search_results'][1] = array_merge( array_slice( $settings['search_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_results'][1], $afterIndex + 1 ) );

        // Override the label for the search form and search results templates folder
        $settings['search_form'][1]['form_template']['desc'] = str_replace('plugins folder/geo-my-wp/plugin/taxonomies/search-forms/','plugins folder/gmw-taxonomy/search-forms',$settings['search_form'][1]['form_template']['desc']);
        $settings['search_results'][1]['results_template']['desc'] = str_replace('plugins folder/geo-my-wp/plugin/taxonomies/search-results/','plugins folder/gmw-taxonomy/search-results',$settings['search_results'][1]['results_template']['desc']);


        return $settings;

    }

    public function shortcodes_page( $shortcodes ) {

        $shortcodes['single_taxonomy_term_location'] = array(
            'name'		  	=> __( 'Single Taxonomy Term Location', 'GMW' ),
            'basic_usage' 	=> '[gmw_single_taxonomy_location]',
            'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_single_taxonomy_location]\'); &#63;&#62;',
            'desc'        	=> __( 'Display map and/or location information of specific taxonomy term.', 'GMW' ),
            'attributes'  	=> array(
                array(
                    'attr'	 	=> 'tt_id',
                    'values' 	=> array(
                        'Taxonomy Term ID',
                    ),
                    'default'	=> __( 'By default the shortcode uses the current taxonomy term when displayed on a taxonomy archive page', 'GMW' ),
                    'desc'	 	=> __( 'Use the taxonomy term ID only if you want to display information of a specific taxonomy term. When using the shortcode on a taxonomy archive page you don\'t need to use the tt_id attribute. ', 'GMW')
                ),
                array(
                    'attr'	 	=> 'name',
                    'values' 	=> array(
                        '1',
                        '0',
                    ),
                    'default'	=> '0',
                    'desc'	 	=> __( 'Use the value 1 to display the taxonomy term name above the map.', 'GMW' )
                ),
                array(
                    'attr'	 	=> 'distance',
                    'values' 	=> array(
                        '1',
                        '0',
                    ),
                    'default'	=> '1',
                    'desc'	 	=> __( 'Use the value 1 to display distance of the taxonomy term\'s location from the user\'s current location when exists.', 'GMW' )
                ),
                array(
                    'attr'	 	=> 'distance_units',
                    'values' 	=> array(
                        'm',
                        'k',
                    ),
                    'default'	=> 'm',
                    'desc'	 	=> __( "Distance units - \"m\" for Miles \"k\" for Kilometers", 'GMW' )
                ),
                array(
                    'attr'	 	=> 'map',
                    'values' 	=> array(
                        '1',
                        '0',
                    ),
                    'default'	=> '1',
                    'desc'	 	=> __( 'Use the value 1 if you want to display map of the location.', 'GMW' )
                ),
                array(
                    'attr'	 	=> 'map_height',
                    'values' 	=> array(
                        __( 'Value in pixels or percentage', 'GMW' ),
                    ),
                    'default'	=> '250px',
                    'desc'	 	=> __( 'Map height in px or % ( ex. 250px or 100% ).', 'GMW' )
                ),
                array(
                    'attr'	 	=> 'map_width',
                    'values' 	=> array(
                        __( 'Value in pixels or percentage', 'GMW' ),
                    ),
                    'default'	=> '250px',
                    'desc'	 	=> __( 'Map width in px or % ( ex. 250px or 100% ).', 'GMW' )
                ),
                array(
                    'attr'	 	=> 'map_type',
                    'values' 	=> array(
                        'ROADMAP',
                        'SATELLITE',
                        'HYBRID',
                        'TERRAIN'
                    ),
                    'default'	=> 'ROADMAP',
                    'desc'	 	=> __( 'Choose the map type.', 'GMW' )
                ),
                array(
                    'attr'	 	=> 'zoom_level',
                    'values' 	=> array(
                        __( 'Numeric value between 1 to 18.', 'GMW' ),
                    ),
                    'default'	=> '13',
                    'desc'	 	=> __( 'Choose the map zoom level.', 'GMW')
                ),
                array(
                    'attr'	 	=> 'additional_info',
                    'values' 	=> array(
                        'address',
                        'phone',
                        'fax',
                        'email',
                        'website',
                    ),
                    'default' 	=> 'address,phone,fax,email,website',
                    'desc'	 	=> __( 'Use a single or multiple values comma separated of the contact information which you would like to display. For example use additional_info="address,phone,fax" to display the full address of the location and its phone and fax numbers.', 'GMW')
                ),
                array(
                    'attr'		=> 'directions',
                    'values' 	=> array(
                        '1',
                        '0',
                    ),
                    'default'	=> '1',
                    'desc'	 	=> __( 'Use the value 1 if you want to display "Get Directions" link.', 'GMW' )
                ),
                array(
                    'attr'		=> 'info_window',
                    'values' 	=> array(
                        '1',
                        '0',
                    ),
                    'default'	=> '1',
                    'desc'	 	=> __( 'Use the 0 to disable or the value 1 to enable the info-window of the marker represents the taxonomy term being displayed.', 'GMW' )
                ),
                array(
                    'attr'		=> 'hide_info',
                    'values' 	=> array(
                        '1',
                        '0',
                    ),
                    'default'	=> '0',
                    'desc'	 	=> __( "Use the value 1 to Hide all other information except for the map", "GMW" )
                ),
                array(
                    'attr'		=> 'location_marker',
                    'values' 	=> array(
                        'link to an image',
                    ),
                    'default'	=> 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    'desc'	 	=> __( "Provide a link to an image that will be used as a marker which represents the location of the taxonomy term being displayed.", 'GMW' )
                ),
                array(
                    'attr'		=> 'ul_marker',
                    'values' 	=> array(
                        'link to an image',
                        '0',
                    ),
                    'default'	=> 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                    'desc'	 	=> __( "Provide a link to an image that will be used as a marker which represents the user's location on the map. Use 0 if you do not want to show the user's location.", 'GMW' )
                ),
                array(
                    'attr'		=> 'ul_message',
                    'values' 	=> array(
                        'Any text',
                        '0',
                    ),
                    'default'	=> 'Your location',
                    'desc'	 	=> __( "Any text that will be display within the info-window of the marker represents the user's current location. Use 0 if you want the info-window to be disabled.", 'GMW' )
                ),

            ),
            'examples'  => array(
                array(
                    'example' => "&#60;&#63;php do_shortcode('[gmw_single_taxonomy_location]'); &#63;&#62; ",
                    'desc'	  => __( 'Place this function call in the taxonomy-$taxonomy.php template to display the map, contact information and "get directions" link of the taxonomy term.', 'GMW' )

                ),
                array(
                    'example' => "[gmw_single_location map=\"1\" map_width=\"100%\" map_height=\"450px\" additional_info=\"0\" directions=\"0\"]",
                    'desc'	  => __( 'Display map of the location. Map width set to 100% and map height 450px. No additional information and no "Get directions" link will be displayed.', 'GMW' )

                ),
            ),

        );

        $shortcodes['taxonomy_info'] = array(
            'name'		 	=> __( 'Taxonomy Term Information', 'GMW' ),
            'basic_usage' 	=> '[gmw_taxonomy_info]',
            'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_taxonomy_info]\'); &#63;&#62;',
            'desc'        	=> __( 'Easy way to display any of the location/contact information of a taxonomy term.', 'GMW' ),
            'attributes'  	=> array(
                array(
                    'attr'	 => 'tt_id',
                    'values' => array(
                        'Taxonomy Term ID',
                    ),
                    'desc'	 => __( "Use the Taxonomy Term ID only if you want to display information of a specific taxonomy term.", 'GMW' ).
                        __( " The shortcode will use the taxonomy term ID of the taxonomy term being displayed. ", 'GMW')
                ),
                array(
                    'attr'	 => 'info',
                    'values' => array(
                        'street',
                        'apt',
                        'city',
                        'state -' . __( "state short name (ex FL )", 'GMW' ),
                        'state_long' . __( "state long name (ex Florida )",'GMW' ),
                        'zipcode',
                        'country - ' . __( "country short name (ex IL )",'GMW' ),
                        'country_long - ' . __( "country long name (ex Israel )",'GMW' ),
                        'address',
                        'formatted_address',
                        'lat - ' . 'Latitude',
                        'long - ' . 'Longitude',
                        'phone',
                        'fax',
                        'email',
                        'website',
                    ),
                    'default'	=> 'formatted_address',
                    'desc'	 => __( 'Use a single value or multiple values comma separated of the information you would like to display. For example use info="city,state,country_long" to display "Hollywood FL United States"', 'GMW')
                ),

                array(
                    'attr'	 	=> 'divider',
                    'values' 	=> array(
                        __( 'any character','GMW' ),
                    ),
                    'default'	=> 'space',
                    'desc'	 	=> __( 'Use any character that you would like to display between the fields you choose above"', 'GMW')
                ),
            ),
            'examples'  => array(
                array(
                    'example' => "[gmw_taxonomy_info tt_id=\"3\" info=\"city,state_long,zipcode\" divider=\",\"]",
                    'desc'	  => __( 'This shortcode will display the information of the taxonomy term with taxonomy term ID 3 which is ( for example ) "Hollywood,Florida,33021"', 'GMW' )

                ),
                array(
                    'example' => "[gmw_taxonomy_info info=\"city,state\" divider=\"-\"]",
                    'desc'	  => __( 'Use the shortcode without tt_id when within an archive template to display "Hollywood-FL"', 'GMW' )

                ),
                array(
                    'example' => "Address [gmw_taxonomy_info info=\"formatted_address\"]<br />
    								 Phone: [gmw_taxonomy_info info=\"phone\"]<br />
    								 Email: [gmw_taxonomy_info info=\"email\"]<br />
    								 Website: [gmw_taxonomy_info info=\"website\"]",
                    'desc'	  => __( 'Use this example in the content of a an archive template to display:', 'GMW' ) . "<br />
    								 Address: blah street, Hollywodo Fl 33021, USA <br />
    								 Phone: 123-456-7890 <br />
    								 Email: blah@geomywp.com <br />
    								 Website: www.geomywp.com <br />"
                ),
            ),
        );

        return $shortcodes;
    }

    public function search_forms_folder($folder){
        if ( GEO_my_WP::gmw_check_addon( 'taxonomies' ) != false ) {
            $folder['tx'] =  array(
                GMW_TX_PATH .'/search-forms/taxonomies/'
            );
        }
        return $folder;
    }

    public function search_results_folder($folder){
        if ( GEO_my_WP::gmw_check_addon( 'taxonomies' ) != false ) {
            $folder['tx'] =  array(
                GMW_TX_PATH .'/search-results/taxonomies/'
            );
        }
        return $folder;
    }
}
new GMW_TX_Form();
?>