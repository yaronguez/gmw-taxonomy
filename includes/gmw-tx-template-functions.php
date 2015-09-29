<?php
/**
 * TX Search form function - Taxonomies Dropdown
 * @version 1.0
 * @author Yaron Guez
 */
function gmw_tx_form_get_taxonomies_dropdown( $gmw, $title, $class, $all_label ) {

    if ( empty( $gmw['search_form']['taxonomies'] ) )
        return;

    if ( count( $gmw['search_form']['taxonomies'] ) == 1 ) {
        $output = '<input type="hidden" id="gmw-single-taxonomy-' . $gmw['ID'] . '" class="gmw-single-taxonomy gmw-single-taxonomy-' . $gmw['ID'] . ' ' . $class . '" name="'.$gmw['url_px'].'taxonomy" value="'.implode( ' ', $gmw['search_form']['taxonomies'] ).'" />';
        return apply_filters( 'gmw_form_single_taxonomy', $output, $gmw, $title, $class, $all_label );
    }

    if ( empty( $all_label ) )
        $all_label = $gmw['labels']['search_form']['search_site'];

    $output = '';

    if ( !empty( $title ) ) {
        $output .= '<label for="gmw-taxonomies-dropdown-'.$gmw['ID'].'">'.$title.'</label>';
    }

    $output .= '<select name="'.$gmw['url_px'].'taxonomy" id="gmw-taxonomies-dropdown-' . $gmw['ID'] . '" class="gmw-taxonomies-dropdown gmw-taxonomies-dropdown-'.$gmw['ID'].' '.$class.'">';
    $output .= '<option value="'.implode( ' ', $gmw['search_form']['taxonomies'] ).'">'.$all_label.'</option>';

    foreach ( $gmw['search_form']['taxonomies'] as $taxonomy ) {

        $pti_post = ( isset( $_GET[$gmw['url_px'].'taxonomy'] ) && $_GET[$gmw['url_px'].'taxonomy'] == $taxonomy ) ? 'selected="selected"' : '';

        $output .= '<option value="'.$taxonomy.'" '.$pti_post.'>'.get_taxonomy( $taxonomy )->labels->name.'</option>';

    }
    $output .= '</select>';

    return apply_filters( 'gmw_form_taxonomies', $output, $gmw, $title, $class, $all_label );
}

function gmw_tx_form_taxonomies_dropdown( $gmw, $title, $class, $all ) {
    echo gmw_tx_form_get_taxonomies_dropdown( $gmw, $title, $class, $all );
}


/**
 * GMW function - Query taxonomies/categories dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_tx_query_taxonomies( $tax_args, $gmw ) {

    if ( !isset( $gmw['search_form']['taxonomies'] ) || empty( $gmw['search_form']['taxonomies'] ) )
        return $tax_args;

    $ptc = ( isset( $_GET[$gmw['url_px'].'post'] ) ) ? count( explode( " ", $_GET[$gmw['url_px'].'post'] ) ) : count( $gmw['search_form']['post_types'] );

    if ( isset( $ptc ) && $ptc > 1 )
        return $tax_args;

    $rr       = 0;
    $get_tax  = false;
    $args     = array( 'relation' => 'AND' );
    $postType = $gmw['search_form']['post_types'][0];

    if ( empty( $gmw['search_form']['taxonomies'][$postType] ) )
        return;

    foreach ( $gmw['search_form']['taxonomies'][$postType] as $tax => $values ) {

        if ( $values['style'] == 'drop' ) {

            $get_tax = false;
            if ( isset( $_GET['tax_' . $tax] ) )
                $get_tax = sanitize_text_field( $_GET['tax_' . $tax] );

            if ( $get_tax != 0 ) {
                $rr++;
                $args[] = array(
                    'taxonomy' => $tax,
                    'field'    => 'id',
                    'terms'    => array( $get_tax )
                );
            }
        }
    }

    if ( $rr == 0 )
        $args = false;

    return $args;

}
add_filter( 'gmw_tx_tax_query', 'gmw_tx_query_taxonomies', 10, 2 );

/**
 * TX results function - Display taxonomies per result.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_tx_get_taxonomies( $gmw, $post ) {

    if ( !isset( $gmw['search_results']['custom_taxes'] ) )
        return;

    $taxonomies = apply_filters( 'gmw_tx_results_taxonomies', get_object_taxonomies( $post->post_type, 'names' ), $gmw, $post );

    $output ='';

    foreach ( $taxonomies as $tax ) {

        $terms = get_the_terms( $post->ID, $tax );

        if ( $terms && !is_wp_error( $terms ) ) {

            $termsArray = array();
            $the_tax = get_taxonomy( $tax );

            foreach ( $terms as $term ) {
                $termsArray[] = $term->name;
            }

            $tax_output  = '<div class="gmw-taxes gmw-taxonomy-' . $the_tax->rewrite['slug'] . '">';
            $tax_output .= 	'<span class="tax-label">' . $the_tax->labels->singular_name . ': </span>';
            $tax_output .= 	'<span class="gmw-terms-wrapper gmw-'.$the_tax->rewrite['slug'].'-terms">'.join( ", ", $termsArray ).'</span>';
            $tax_output .= '</div>';

            $output .= apply_filters( 'gmw_pt_results_taxonomy', $tax_output, $gmw, $post, $taxonomies, $the_tax, $terms, $termsArray );

        }
    }

    return $output;
}

function gmw_tx_taxonomies( $gmw, $post ) {
    echo gmw_tx_get_taxonomies( $gmw, $post );
}

/**
 * TX results function - Day & Hours.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_tx_get_days_hours( $taxonomy_term, $gmw ) {

    $days_hours = get_post_meta( $taxonomy_term->ID, '_wppl_days_hours', true );
    $days_hours = get_option( '_wppl_days_hours_taxonomy_' . $taxonomy_term->term_taxonomy_id);
    $output     ='';
    $dh_output  = '';
    $dc         = 0;

    if ( !empty( $days_hours ) && is_array( $days_hours ) ) {

        foreach ( $days_hours as $day ) {
            if ( array_filter( $day ) ) {
                $dc++;
                $dh_output .= '<li class="single-days-hours"><span class="single-day">'.esc_attr( $day['days'] ).': </span><span class="single-hour">'.esc_attr( $day['hours'] ).'</span></li>';
            }
        }
    }

    if ( $dc > 0 ) {

        $output .= '<ul class="opening-hours-wrapper">';
        $output .= '<h4>'. esc_attr( $gmw['labels']['search_results']['opening_hours'] ).'</h4>';
        $output .= $dh_output;
        $output .= '</ul>';

    } elseif ( !empty( $nr_message ) && ( empty( $days_hours ) ) ) {
        $output .='<p class="days-na">' . esc_attr( $nr_message ) . '</p>';
    }

    return $output;
}

function gmw_tx_days_hours( $post, $gmw ) {
    echo gmw_tx_get_days_hours( $post, $gmw );
}