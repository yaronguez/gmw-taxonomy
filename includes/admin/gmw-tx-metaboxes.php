<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * MW_PT_Meta_Boxes class.
 */

class GMW_TX_Meta_Boxes {

    /**
     * Constructor
     */
    public function __construct() {

        $this->settings   = get_option( 'gmw_options' );
        // TODO: store taxonomies in admin
        $this->taxonomies = ( isset( $this->settings[ 'taxonomy_settings' ][ 'taxonomies' ] ) ) ? $this->settings[ 'taxonomy_settings' ][ 'taxonomies' ] : array('');

        $this->meta_boxes = $this->create_meta_boxes();

        foreach($this->taxonomies as $taxonomy){
            // display form fields when adding taxonomy term
            add_action("{$taxonomy}_add_form_fields" , array( $this , 'add_location_fields' ) , 1 , 1 );

            // display form fields when editing taxonomy term
            add_action("{$taxonomy}_edit_form_fields" , array( $this , 'edit_location_fields' ) , 1 , 1 );

            // save form fields when adding taxonomy term
            add_action( "created_{$taxonomy}", array( $this , 'save_location_fields' ) , 10 , 2 );

            // save form fields when editing taxonomy term
            add_action( "edited_{$taxonomy}", array( $this , 'save_location_fields' ) , 10 , 2 );

        }

        add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'register_admin_location_scripts' ), 11 ); // WP < 4.5
        add_action( 'admin_print_scripts-term.php', array( $this, 'register_admin_location_scripts' ), 11 );  // WP >= 4.5
    }

    /**
     * admin_enqueue_scripts function.
     *
     * @access public
     * @return void
     */
    public function register_admin_location_scripts($hook) {
        // Ensure we're on a taxonomy screen for the selected taxonomies
        $screen = get_current_screen();
        if ( !in_array( $screen->taxonomy, $this->taxonomies ) ) {
            return;
        }

        wp_register_style( 'gmw-tx-admin-style', 		GMW_TX_URL . '/assets/css/style-admin.css' );
        wp_register_script( 'gmw-admin-address-picker', GMW_TX_URL . '/assets/js/addresspicker.min.js', array( 'jquery' ), GMW_VERSION, true );


        //add default values if not exist to prevent JavaScript error
        if ( !isset( $this->settings['taxonomy_settings']['edit_taxonomy_zoom_level'] ) ) $this->settings['taxonomy_settings']['edit_taxonomy_zoom_level'] = 7;
        if ( !isset( $this->settings['taxonomy_settings']['edit_taxonomy_latitude'] ) )   $this->settings['taxonomy_settings']['edit_taxonomy_latitude']   = '40.7115441';
        if ( !isset( $this->settings['taxonomy_settings']['edit_taxonomy_longitude'] ) )  $this->settings['taxonomy_settings']['edit_taxonomy_longitude']  = '-74.01348689999998';

        wp_localize_script( 'gmw-admin-address-picker', 'gmwSettings', $this->settings );
    }

    /**
     * Create meta boxes
     */
    public function create_meta_boxes() {


        $prefix     	= '_wppl_';
        $meta_boxes 	= array(
            'id'       => 'wppl-meta-box',
            'title'    => apply_filters( 'gmw_tx_mb_title', __( 'Location', 'GMW' ) ),
            'pages'    => $this->taxonomies,
            'context'  => 'normal',
            'priority' => 'high',
            'fields'   => array(
                array(
                    'name' => __( 'Street', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'street',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Apt/Suit', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'apt',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'City', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'city',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'State', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'state',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Zipcode', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'zipcode',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Country', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'country',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Phone Number', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'phone',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Fax Number', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'fax',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Email Address', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'email',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Website', 'GMW' ),
                    'desc' => 'Ex: www.website.com',
                    'id'   => $prefix . 'website',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => 'ex: http://www.mywebsite.com',
                ),
                array(
                    'name' => __( 'Latitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'enter_lat',
                    'type' => 'text-right',
                    'std'  		=> '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Longitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'enter_long',
                    'type' => 'text-right',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Latitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'lat',
                    'type' => 'text-disable',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Longitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'long',
                    'type' => 'text-disable',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Full Address', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'address',
                    'type' => 'text-disable',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Days & Hours', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'days_hours',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'State Long', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'state_long',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Country Long', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'country_long',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'Formatted address', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'formatted_address',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'street_number', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'street_number',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                ),
                array(
                    'name' => __( 'street_name', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'street_name',
                    'type' => 'text',
                    'std'  => '',
                    'placeholder' => '',
                )
            )
        );

        return $meta_boxes;
    }


    public function edit_location_fields($term) {
        global $wpdb;

        $tax_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "taxonomy_locator WHERE term_taxonomy_id = %d", array( $term->term_taxonomy_id ) ) );

        ?>
        <tr class="form-fields"><td colspan="2">
        <?php

        $this->display_location_fields($tax_info);
        ?>
        </td></tr>
        <?php
    }

    /**
     * Display meta boxes
     */

    public function add_location_fields($taxonomy){
        $this->display_location_fields(array());
    }

    public function display_location_fields($tax_info){
        if ( empty( $tax_info ) ) {
            $post_info = ( object ) array(
                'term_taxonomy_id'  => '',
                'name'        => '',
                'description'        => '',
                'lat'               => '',
                'long'              => '',
                'street_number'     => '',
                'street_name'       => '',
                'street'            => '',
                'apt'               => '',
                'city'              => '',
                'state'             => '',
                'state_long'        => '',
                'zipcode'           => '',
                'country'           => '',
                'country_long'      => '',
                'address'           => '',
                'formatted_address' => '',
                'phone'             => '',
                'fax'               => '',
                'email'             => '',
                'website'           => '',
                'map_icon'          => ''
            );
        }


        if ( isset( $this->settings[ 'general_settings' ][ 'mandatory_address' ] ) )
            wp_localize_script( 'wppl-address-picker', 'addressMandatory', $this->settings[ 'general_settings' ][ 'mandatory_address' ] );
        ?>

        <?php do_action( 'gmw_admin_location_section_tax_start', $tax_info ); ?>

        <!--  <input type="text" name="gmw_post_level" id="gmw-post-level"> -->

        <input type="hidden" name="this->meta_boxes_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>" />

        <div class="gmw-location-section map">
            <h3>
                <?php _e( 'Use the map to drag and drop the marker to the desired location.','GMW' );?>
            </h3>
            <div id="map"></div>
        </div>

        <div class="gmw-location-section current-location">
            <h3>
                <?php _e('Get your current location','GMW'); ?>
            </h3>
            <div class="current-location-inner">
                <input type="button" id="gmw-admin-locator-btn" class="button-primary"
                       value="<?php _e('Locate Me','GMW'); ?>" />
            </div>
        </div>

        <div class="gmw-location-section autocomplete">
            <h3>
                <?php _e('Type an address to autocomplete','GMW'); ?>
            </h3>
            <div class="autocomplete-location-inner">
                <input type="text" id="wppl-addresspicker"
                       value="<?php echo $tax_info->address; ?>" />
            </div>
        </div>

        <div class="clear"></div>

        <div class="gmw-location-section gmw-location-manually-wrapper">
            <h3><?php _e('Enter Location Manually','GMW'); ?></h3>

            <div class="gmw-location-section-inner">

                <div class="address">

                    <h3><?php _e('Address','GMW'); ?></h3>

                    <div class="gmw-location-section-description">
                        <p><?php _e('Fill out the address fields and click "Get Lat/Long" to retrieve the latitude and longitude of the location.','GMW'); ?></p>
                    </div>

                    <table class="gmw-admin-location-table">
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][0]['id']; ?>"><?php echo $this->meta_boxes['fields'][0]['name']; ?></label></th>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][0]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][0]['id']; ?>" value="<?php echo $tax_info->street; ?>"   /><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][1]['id']; ?>"><?php echo $this->meta_boxes['fields'][1]['name']; ?></label></th>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][1]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][1]['id']; ?>" value="<?php echo $tax_info->apt; ?>"   /><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][2]['id']; ?>"><?php echo $this->meta_boxes['fields'][2]['name']; ?></label></th>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][2]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][2]['id']; ?>" value="<?php echo $tax_info->city; ?>"   /><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][3]['id']; ?>"><?php echo $this->meta_boxes['fields'][3]['name']; ?></label></th>
                            <td><input type="text" id="<?php echo $this->meta_boxes['fields'][3]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][3]['id']; ?>" value="<?php echo $tax_info->state; ?>"/><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][4]['id']; ?>"><?php echo $this->meta_boxes['fields'][4]['name']; ?></label></th>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][4]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][4]['id']; ?>" value="<?php echo $tax_info->zipcode; ?>"   /><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][5]['id']; ?>"><?php echo $this->meta_boxes['fields'][5]['name']; ?></label></th>
                            <td><input type="text" id="<?php echo $this->meta_boxes['fields'][5]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][5]['id']; ?>" value="<?php echo $tax_info->country; ?>"/><br /></td>
                        </tr>
                        <tr>
                            <th></th>
                            <td><input type="button"id="gmw-admin-getlatlong-btn" class="button-primary" value="Get Lat/Long" style="margin: 10px 0px;"></td>
                        </tr>
                    </table>
                    <table style="display:none;">

                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][19]['id']; ?>"><?php echo $this->meta_boxes['fields'][19]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" id="<?php echo $this->meta_boxes['fields'][19]['id']; ?>" name="<?php echo $this->meta_boxes['fields'][19]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][19]['id']; ?>" value="<?php echo $tax_info->street_number; ?>"   /><br /></td>
                        </tr>

                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][20]['id']; ?>"><?php echo $this->meta_boxes['fields'][20]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" id="<?php echo $this->meta_boxes['fields'][20]['id']; ?>" name="<?php echo $this->meta_boxes['fields'][20]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][20]['id']; ?>" value="<?php echo $tax_info->street_name; ?>"   /><br /></td>
                        </tr>

                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][3]['id']; ?>"><?php echo $this->meta_boxes['fields'][3]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][3]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][3]['id']; ?>" value="<?php echo $tax_info->state; ?>"   /><br /></td>
                        </tr>

                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][16]['id']; ?>"><?php echo $this->meta_boxes['fields'][16]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][16]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][16]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][16]['id']; ?>" value="<?php echo $tax_info->state_long; ?>" style="width: 100%;"/><br /></td>
                        </tr>

                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][5]['id']; ?>"><?php echo $this->meta_boxes['fields'][5]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][5]['id']; ?>"  class="<?php echo $this->meta_boxes['fields'][5]['id']; ?>" value="<?php echo $tax_info->country; ?>"   /><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][17]['id']; ?>"><?php echo $this->meta_boxes['fields'][17]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][17]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][17]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][17]['id']; ?>" value="<?php echo $tax_info->country_long; ?>" style="width: 100%;" /><br /></td>
                        </tr
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][14]['id']; ?>"><?php echo $this->meta_boxes['fields'][14]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][14]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][14]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][14]['id']; ?>" value="<?php echo $tax_info->address; ?>" style="width: 100%;"/><br /></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $this->meta_boxes['fields'][18]['id']; ?>"><?php echo $this->meta_boxes['fields'][18]['name']; ?></label></th>
                        </tr>
                        <tr>
                            <td><input type="text" name="<?php echo $this->meta_boxes['fields'][18]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][18]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][18]['id']; ?>" value="<?php echo $tax_info->formatted_address; ?>" style="width: 100%;" /><br /></td>
                        </tr>
                    </table>
                </div>
                <div class="right-side">
                    <div class="coords">
                        <h3><?php _e('Latitude / Longitude','GMW'); ?></h3>
                        <div class="gmw-location-section-description">
                            <p><?php _e('Fill out the Latitude and Longitude fields and click on "Get Address" to retrive the address of the location.','GMW'); ?></p>
                        </div>
                        <table class="gmw-admin-location-table">
                            <tr>
                                <th><label for="<?php echo $this->meta_boxes['fields'][12]['id']; ?>"><?php echo $this->meta_boxes['fields'][12]['name'];?></label></th>
                                <td>
                                    <input type="text" name="<?php echo $this->meta_boxes['fields'][12]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][12]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][12]['id']; ?>" value="<?php echo $tax_info->lat; ?>"  />
                                    <input type="hidden" name="gmw_check_lat" id="gmw_check_lat" value"">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="<?php echo $this->meta_boxes['fields'][13]['id']; ?>"><?php echo $this->meta_boxes['fields'][13]['name']; ?></label></th>
                                <td>
                                    <input type="text" name="<?php echo $this->meta_boxes['fields'][13]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][13]['id']; ?>" class="<?php echo $this->meta_boxes['fields'][13]['id']; ?>" value="<?php echo $tax_info->long; ?>"  />
                                    <input type="hidden" name="gmw_check_long" id="gmw_check_long" value"">
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td><input style="margin: 10px 0px;" type="button" id="gmw-admin-getaddress-btn" class="button-primary" value="Get Address" /></td>
                            </tr>
                        </table>
                    </div>

                    <div class="delete-location-wrapper">
                        <h3><?php _e('Delete Location','GMW'); ?></h3>
                        <div class="delete-locaiton-inner">
                            <input type="button" style="float:none;" id="gmw-admin-delete-btn" class="button-primary" value="<?php _e('Delete address','GMW'); ?>" />
                        </div>
                    </div>

                    <div id="gmw-getting-info" class="location-status-wrapper">
                        <h3 ><?php _e('Location status','GMW'); ?></h3>
                        <div class="location-status-inner">
                            <div id="gmw-location-loader" style="display:none;background:none; border:0px;height: 23px;"><img style="width:15px;margin-right: 5px"src="<?php echo GMW_IMAGES; ?>/gmw-loader.gif" id="ajax-loader-image" alt="" "><?php _e('Loading...','GMW'); ?></div>
                            <div id="gmw-good-location-message" class="" style="display:none;height: 23px;"><p><?php _e( 'Location is ready', 'GMW'); ?></p></div>
                            <div id="gmw-bad-location-message" class="gmw-location-message" style="height: 23px;"><p style="color:red"><?php _e( 'A valid address, latitude and longitude are required to save the Location','GMW'); ?></p></div>
                        </div>
                    </div>

                    <div class="clear"></div>

                </div>
            </div>
        </div>

        <div class="gmw-location-section additional-information-wrapper">
            <h3 style="font-size:13px;"><?php _e( 'Additional Information','GMW' ); ?></h3>

            <div style="padding:5px;">

                <div class="metabox-tabs-div">
                    <ul class="metabox-tabs" id="metabox-tabs">
                        <li class="active extra-info-tab"><a class="active" href="javascript:void(null);"><?php _e('Contact Information','GMW'); ?></a></li>
                        <li class="days-hours-tab"><a href="javascript:void(null);"><?php _e('Days & Hours','GMW'); ?></a></li>
                    </ul>

                    <div class="extra-info-tab">
                        <h4 class="heading"><?php _e('Additional Information','GMW'); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="<?php echo $this->meta_boxes['fields'][6]['id']; ?>"><?php echo $this->meta_boxes['fields'][6]['name']; ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][6]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][6]['id']; ?>" value="<?php echo $tax_info->phone; ?>" /></td>
                            </tr>
                            <tr>
                                <th><label for="<?php echo $this->meta_boxes['fields'][7]['id']; ?>"><?php echo $this->meta_boxes['fields'][7]['name']; ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][7]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][7]['id']; ?>" value="<?php echo $tax_info->fax; ?>"/></td>
                            </tr>
                            <tr>
                                <th><label for="<?php echo $this->meta_boxes['fields'][8]['id']; ?>"><?php echo $this->meta_boxes['fields'][8]['name']; ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][8]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][8]['id']; ?>" value="<?php echo $tax_info->email; ?>" placeholder="<?php echo $this->meta_boxes['fields'][8]['placeholder']; ?>" /></td>
                            </tr>
                            <tr>
                                <th><label for="<?php echo $this->meta_boxes['fields'][9]['id']; ?>"><?php echo $this->meta_boxes['fields'][9]['name']; ?></label></th>
                                <td>
                                    <div style="position: relative;">
                                        <!-- <span style="position: absolute; padding:8px 3px 8px 8px; font-size: 13px; color:#888 ">http://</span> -->
                                        <input type="text" name="<?php echo $this->meta_boxes['fields'][9]['id']; ?>" id="<?php echo $this->meta_boxes['fields'][9]['id']; ?>" value="<?php echo $tax_info->website; ?>" placeholder="<?php echo $this->meta_boxes['fields'][9]['placeholder']; ?>" />
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    $days_hours = get_option( $this->meta_boxes['fields'][15]['id'] . '_taxonomy_' . $tax_info->term_taxonomy_id);
                    $days_hours = ( isset( $days_hours ) && is_array( $days_hours ) && array_filter( $days_hours ) )  ? $days_hours : false;

                    //$days_hours = get_post_meta( $post->ID, $this->meta_boxes['fields'][15]['id'], true );
                    //$days_hours = ( isset( $days_hours ) && is_array( $days_hours ) && array_filter( $days_hours ) )  ? get_post_meta( $post->ID, $this->meta_boxes['fields'][15]['id'], true ) : false;
                    ?>
                    <div class="days-hours-tab">
                        <h4 class="heading"><?php _e( 'Days & Hours', 'GMW' ); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[0][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[0]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e( 'Hours','GMW' ); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id']. '[0][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[0]['hours']; ?>" style="width:150px" /><br /></td>
                            </tr>
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id']. '[1][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[1]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Hours','GMW'); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id']. '[1][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[1]['hours']; ?>" style="width:150px" /><br /></td>
                            </tr>
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[2][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[2]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Hours','GMW'); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[2][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[2]['hours'];?>" style="width:150px" /><br /></td>
                            </tr>
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[3][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[3]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Hours','GMW'); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[3][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[3]['hours']; ?>" style="width:150px" /><br /></td>
                            </tr>
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[4][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[4]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Hours','GMW'); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[4][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[4]['hours']; ?>" style="width:150px" /><br /></td>
                            </tr>
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[5][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[5]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Hours','GMW'); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[5][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[5]['hours']; ?>" style="width:150px" /><br /></td>
                            </tr>
                            <tr>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Days','GMW'); ?></label></th>
                                <td style="width:150px"><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[6][days]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[6]['days']; ?>" style="width:150px" /><br /></td>
                                <th style="width:30px"><label for="<?php echo $this->meta_boxes['fields'][15]['id']; ?>"><?php _e('Hours','GMW'); ?></label></th>
                                <td><input type="text" name="<?php echo $this->meta_boxes['fields'][15]['id'].'[6][hours]'; ?>" id="<?php echo $this->meta_boxes['fields'][15]['id']; ?>" value="<?php echo $days_hours[6]['hours']; ?>" style="width:150px" /><br /></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php do_action( 'gmw_admin_location_section_tax_end', $tax_info ); ?>

        <div class="clear"></div>
        <?php

        wp_enqueue_style( 'gmw-tx-admin-style' );
        wp_enqueue_script( 'google-maps' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'gmw-admin-address-picker' );

        //make sure address_mandatory is not undefined
        $this->settings['taxonomy_settings']['mandatory_address'] = gmw_get_option( 'taxonomy_settings', 'mandatory_address', false );

        wp_localize_script( 'gmw-admin-address-picker','gmwOptions', $this->settings );
    }
    /* EVERY NEW POST OR WHEN POST IS BEING UPDATED
     * CREATE MAP, LATITUDE, LONGITUDE AND SAVE DATA INTO OUR LOCATIONS TABLE 
     * DATA SAVED - POST ID, POST TYPE, POST STATUS , POST TITLE , LATITUDE, LONGITUDE AND ADDRESS
     */

    function save_location_fields( $term_id, $tt_id ) {
        global $wpdb;

        // verify nonce //
        if ( !isset( $_POST[ 'this->meta_boxes_nonce' ] ) || !wp_verify_nonce( $_POST[ 'this->meta_boxes_nonce' ], basename( __FILE__ ) ) ) {
            return;
        }

        foreach ( $this->meta_boxes[ 'fields' ] as $field ) :

            if ( $field[ 'id' ] == '_wppl_days_hours' ) {

                if ( isset( $_POST[ $field[ 'id' ] ] ) ) :

                    $old = get_option( $field['id']. '_taxonomy_' . $tt_id);
                    //$old = get_post_meta( $post->ID, $field[ 'id' ], true );
                    $new = $_POST[ $field[ 'id' ] ];

                    if ( $new && $new != $old ) {
                        update_option($field['id']. '_taxonomy_' . $tt_id, $new);
                        //update_post_meta( $post->ID, $field[ 'id' ], $new );
                    } elseif ( '' == $new && $old ) {
                        //delete_post_meta( $post->ID, $field[ 'id' ], $old );
                        delete_option($field['id']. '_taxonomy_' . $tt_id);
                    }

                endif;
            }

        endforeach;

        //do_action( 'gmw_pt_admin_update_location_post_meta', $post->ID, $_POST, $wppl_options );
        //delete locaiton if there are no address or lat/long
        if ( !isset( $_POST[ '_wppl_formatted_address' ] ) || empty( $_POST[ '_wppl_formatted_address' ] ) || !isset( $_POST[ '_wppl_lat' ] ) || empty( $_POST[ '_wppl_lat' ] ) ) {

            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM " . $wpdb->prefix . "taxonomy_locator WHERE term_taxonomy_id=%d", $tt_id
                )
            );
        } else {

            $_POST['gmw_map_icon']  = ( isset( $_POST['gmw_map_icon'] ) && !empty( $_POST['gmw_map_icon'] ) ) ? $_POST['gmw_map_icon'] : '_default.png';
            $_POST 					= apply_filters( 'gmw_tx_before_location_updated', $_POST, $tt_id );

            // Fetch term from DB
            $term = get_term($term_id);

            //update DB
            $result = $wpdb->replace($wpdb->prefix . 'taxonomy_locator', array(
                'term_taxonomy_id'           => $tt_id,
                'name'        => $term->name,
                'description'       => $term->description,
                'street_number'     => $_POST['_wppl_street_name'],
                'street_name'       => $_POST['_wppl_street_number'],
                'street'            => $_POST['_wppl_street'],
                'apt'               => $_POST['_wppl_apt'],
                'city'              => $_POST['_wppl_city'],
                'state'             => $_POST['_wppl_state'],
                'state_long'        => $_POST['_wppl_state_long'],
                'zipcode'           => $_POST['_wppl_zipcode'],
                'country'           => $_POST['_wppl_country'],
                'country_long'      => $_POST['_wppl_country_long'],
                'address'           => $_POST['_wppl_address'],
                'formatted_address' => $_POST['_wppl_formatted_address'],
                'phone'             => $_POST['_wppl_phone'],
                'fax'               => $_POST['_wppl_fax'],
                'email'             => $_POST['_wppl_email'],
                'website'           => $_POST['_wppl_website'],
                'lat'               => $_POST['_wppl_lat'],
                'long'              => $_POST['_wppl_long'],
                'map_icon'          => $_POST['gmw_map_icon'],
            ));

        }
        do_action( 'gmw_tx_after_location_updated', $tt_id, $_POST );
    }
}
new GMW_TX_Meta_Boxes;
?>