<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_PT_TX_Form class
 */

class GMW_PT_TX_Form {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        // Set up form button
        add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_button' 	), 1, 1 );

        // Setup form settings page
        add_filter( 'gmw_posts_taxonomies_form_settings', 	 array( $this, 'form_settings_init' ), 1, 1 );

        // Populate individual form settings
        add_action( 'gmw_posts_taxonomies_form_settings_post_types', 	   	   array( $this, 'form_settings_post_types' ), 1, 4 );
        add_action( 'gmw_posts_taxonomies_form_settings_featured_image',      array( $this, 'featured_image' 			), 1, 4 );
        add_action( 'gmw_posts_taxonomies_form_settings_show_excerpt',    	   array( $this, 'show_excerpt' 			), 1, 4 );
        add_action( 'gmw_posts_taxonomies_form_settings_form_taxonomies', 	   array( $this, 'form_taxonomies' 			), 1, 4 );

        // Include search-forms and results-forms locations in the new form settings
        add_filter('gmw_admin_search_forms_folder', array($this, 'search_forms_folder'), 1, 1);
        add_filter('gmw_admin_results_templates_folder', array($this, 'search_results_folder'), 1, 1);

    }

    /**
     * New form button function.
     *
     * @access public
     * @return $buttons
     */
    public function new_form_button( $buttons ) {
    	$buttons[1] = array(
    			'name'       => 'posts_taxonomies',
    			'addon'      => 'taxonomies',
    			'title'      => __( 'Posts by Taxonomy Locator', 'GMW' ),
    			'link_title' => __( 'Create new Posts by Taxonomy Locator form', 'GMW' ),
    			'prefix'     => 'pt_tx',
    			'color'      => 'C3D5E6'
    	);
    	return $buttons;
    }


    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    function form_settings_init( $settings ) {

        //page laod features
        $newValues = array(

            'post_types'     => array(
                'name'    => 'post_types',
                'std'     => '',
                'label'   => __( 'Post Types', 'GMW' ),
                'desc'    => __( 'Choose the post types you would like to display.', 'GMW' ),
                'type'    => 'multicheckboxvalues',
                'options' => get_post_types()
            ),
        );

        $afterIndex = 0;
        $settings['page_load_results'][1] = array_merge( array_slice( $settings['page_load_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['page_load_results'][1], $afterIndex + 1 ) );

        //search form features
        $newValues = array(
            'post_types' => array(
                'name'     		=> 'post_types',
                'std'      		=> '',
                'label'    		=> __( 'Post Types', 'GMW' ),
                'cb_label' 		=> '',
                'desc'     		=> __( "Check the checkboxes of the post types you'd like to display in the search form. When selecting multiple post types they will be displayed as a dropdown menu.", 'GMW' ),
                'type'     		=> 'function',
            ),

            'form_taxonomies' => array(
                'name'  => 'form_taxonomies',
                'std'   => '',
                'label' => __( 'Taxonomies', 'GMW' ),
                'desc'  => __( "Choose the taxonomies that you'd like to display in the search form. The taxonomies will be displayed as a dropdown menues.", 'GMW' ),
                'type'  => 'function'
            )
        );

        $afterIndex = 0;
        $settings['search_form'][1] = array_merge( array_slice( $settings['search_form'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_form'][1], $afterIndex + 1 ) );

        //search results features
        unset( $settings['search_results'][1]['auto_results'], $settings['search_results'][1]['auto_all_results'] );
        $newValues = array(

            'display_posts'    => array(
                'name'     => 'display_posts',
                'std'      => '',
                'label'    => __( 'Display Posts?', 'GMW' ),
                'desc'     => __( 'Display results as list of posts', 'GMW' ),
                'type'     => 'checkbox',
                'cb_label' => __( 'Yes', 'GMW' ),
            ),
            'featured_image'   => array(
                'name'     => 'featured_image',
                'std'      => '',
                'label'    => __( 'Featured Image', 'GMW' ),
                'cb_label' => '',
                'desc'     => __( 'Display featured image and define its width and height in pixels.', 'GMW' ),
                'type'     => 'function',
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
            'show_excerpt'     => array(
                'name'     => 'show_excerpt',
                'std'      => '',
                'label'    => __( 'Excerpt', 'GMW' ),
                'cb_label' => '',
                'desc'     => __( 'Display the number of words that you choose from the post content and display it per location in the list of results.', 'GMW' ),
                'type'     => 'function'
            ),
            'custom_taxes'     => array(
                'name'     => 'custom_taxes',
                'std'      => '',
                'label'    => __( 'Taxonomies', 'GMW' ),
                'cb_label' => __( 'Yes', 'GMW' ),
                'desc'     => __( 'Display a list of taxonomies attached to each post in the list of results.', 'GMW' ),
                'type'     => 'checkbox'
            ),

        );

        $afterIndex = 3;
        $settings['search_results'][1] = array_merge( array_slice( $settings['search_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_results'][1], $afterIndex + 1 ) );

        return $settings;
    }

    /**
     * Post types form settings
     */
    public function form_settings_post_types( $gmw_forms, $formID, $section, $option ) {
        $saved_data = ( isset( $gmw_forms[$formID][$section]['post_types'] ) ) ? $gmw_forms[$formID][$section]['post_types'] : array();
        ?>
        <div class="posts-checkboxes-wrapper" id="<?php echo $formID; ?>">
        	<?php foreach ( get_post_types() as $post ) { ?>
            	<?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $post, $saved_data ) ) ? ' checked="checked"' : ''; ?>
                <p>
                	<input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][post_types][]'; ?>" value="<?php echo $post; ?>" id="<?php echo $post; ?>" class="post-types-tax" <?php echo $checked; ?> />
                	<label><?php echo get_post_type_object( $post )->labels->name . ' ( '. $post .' ) '; ?></label>
                </p>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Taxonomies
     */
    public function form_taxonomies( $gmw_forms, $formID, $section, $option ) {
        $posts = get_post_types();
        ?>
        <div>
            <div id="taxonomies-wrapper" style=" padding: 8px;">
                <?php
                foreach ( $posts as $post ) :

                    $taxes = get_object_taxonomies( $post );

                    echo '<div id="' . $post . '_cat' . '" class="taxes-wrapper" ';
                    echo ( isset( $gmw_forms[$formID][$section]['post_types'] ) && (count( $gmw_forms[$formID][$section]['post_types'] ) == 1) && ( in_array( $post, $gmw_forms[$formID][$section]['post_types'] ) ) ) ? 'style="display: block; " ' : 'style="display: none;"';
                    echo '>';

                    foreach ( $taxes as $tax ) :

                        echo '<div style="border-bottom:1px solid #eee;padding-bottom: 10px;margin-bottom: 10px;" class="gmw-single-taxonomie">';
                        echo '<strong>' . get_taxonomy( $tax )->labels->singular_name . ': </strong>';
                        echo '<span id="gmw-st-wrapper">';
                        echo '<input type="radio" class="gmw-st-btns radio-na" name="gmw_forms[' . $formID . '][' . $section . '][taxonomies]['.$post.'][' . $tax . '][style]" value="na" checked="checked" />' . __( 'Exclude', 'GMW' );
                        echo '<input type="radio" class="gmw-st-btns" name="gmw_forms[' . $formID . '][' . $section . '][taxonomies]['.$post.'][' . $tax . '][style]" value="drop" ';
                        if ( isset( $gmw_forms[$formID][$section]['taxonomies'][$post][$tax]['style'] ) && $gmw_forms[$formID][$section]['taxonomies'][$post][$tax]['style'] == 'drop' )
                            echo "checked=checked"; echo ' style="margin-left: 10px; " />' . __( 'Dropdown', 'GMW' );
                        echo '</span>';

                        echo '</div>';

                    endforeach;

                    echo '</div>';

                endforeach;
                ?>
            </div>
        </div>
        <script>

            jQuery(document).ready(function($) {

                $(".post-types-tax").click(function() {

                    var cCount = $(this).closest(".posts-checkboxes-wrapper").find(":checkbox:checked").length;
                    var scId = $(this).closest(".posts-checkboxes-wrapper").attr('id');
                    var pChecked = $(this).attr('id');

                    if (cCount == 1) {
                        var n = $(this).closest(".posts-checkboxes-wrapper").find(":checkbox:checked").attr('id');
                        $("#taxonomies-wrapper #" + n + "_cat").css('display', 'block');
                        if ($(this).is(':checked')) {
                            $("#taxonomies-wrapper .taxes-wrapper").css('display', 'none').find(".radio-na").attr('checked', true);
                            $("#taxonomies-wrapper #" + pChecked + "_cat").css('display', 'block');
                        } else {
                            $("#taxes-" + scId + " #" + pChecked + "_cat").css('display', 'none').find(".radio-na").attr('checked', true);
                        }
                    } else {
                        $("#taxonomies-wrapper .taxes-wrapper").css('display', 'none').find(".radio-na").attr('checked', true);
                    }
                });

            });
        </script>
        <?php

    }

    /**
     * Featured Image
     */
    public function featured_image( $gmw_forms, $formID, $section, $option ) {
    ?>
        <div>
            <p>
                <input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][featured_image][use]'; ?>" value="1" <?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Width', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'].']['.$section.'][featured_image][width]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['width'] ) && !empty( $gmw_forms[$formID][$section]['featured_image']['width'] ) ) ? $gmw_forms[$formID][$section]['featured_image']['width'] : '200px'; ?>" />px          
            </p>
            <p>
                <?php _e( 'Height', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms['.$_GET['formID'].']['.$section.'][featured_image][height]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['height'] ) && !empty( $gmw_forms[$formID][$section]['featured_image']['height'] ) ) ? $gmw_forms[$formID][$section]['featured_image']['height'] : '200px'; ?>" />px          
           </p>      
        </div>
    <?php
    }

    /**
     * excerpt 
     */
    public static function show_excerpt( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div class="gmw-ssb">
            <p>
                <input type="checkbox"  value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][use]'; ?>" <?php echo ( isset( $gmw_forms[$formID][$section]['excerpt']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Words count ( leave blank to show the eintire content )', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][count]'; ?>" value="<?php if ( isset( $gmw_forms[$formID][$section]['excerpt']['count'] ) ) echo $gmw_forms[$formID][$section]['excerpt']['count']; ?>" size="5" />
            </p>
            <p>
                <?php _e( 'Read more link ( leave blank for no link )', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][more]'; ?>" value="<?php if ( isset( $gmw_forms[$formID][$section]['excerpt']['more'] ) ) echo $gmw_forms[$formID][$section]['excerpt']['more']; ?>" size="15" />
            </p>
        </div>
        <?php
    }

    public function search_forms_folder($folder){
        if ( GEO_my_WP::gmw_check_addon( 'taxonomies' ) != false ) {
            $folder['pt_tx'] =  array(
                GMW_TX_PATH .'/search-forms/posts_taxonomies/'
            );
        }
        return $folder;
    }

    public function search_results_folder($folder){
        if ( GEO_my_WP::gmw_check_addon( 'taxonomies' ) != false ) {
            $folder['pt_tx'] =  array(
                GMW_TX_PATH .'/search-results/posts_taxonomies/'
            );
        }
        return $folder;
    }
}
new GMW_PT_TX_Form();
?>