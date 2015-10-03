<?php
/**
 * Posts by Taxonomy locator "default" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw  - the form being used ( array )
 * $post - each post in the loop
 * 
 * You could but It is not recommended to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "default" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/taxonomies/search-results/posts_taxonomies/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the posts locator form.
 * It will show in the "Search results" dropdown menu as "Custom: default".
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-pt-tx-default-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw, $post ); ?>
	
	<!-- results count -->
	<div class="gmw-results-count">
		<span><?php gmw_results_message( $gmw, false ); ?></span>
	</div>
	
	<?php do_action( 'gmw_search_results_before_top_pagination' , $gmw, $post ); ?>
	
	<div class="gmw-pt-tx-pagination-wrapper gmw-pt-tx-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 

	<div class="clear"></div>
	
	<?php do_action( 'gmw_search_results_before_loop' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<div class="gmw-posts-wrapper">
		
		<!--  this is where wp_query loop begins -->
		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
			
			<!--  single results wrapper  -->
			<?php $featured = ( !empty( $post->feature ) ) ? 'gmw-featured-post' : ''; ?>

			<div id="post-<?php the_ID(); ?>" <?php post_class( 'wppl-single-result '.$featured ); ?>>
				
				<?php do_action( 'gmw_search_results_loop_item_start' , $gmw, $post ); ?>
				
                <!-- Title -->
                <div class="wppl-title-holder">
                    <h2 class="wppl-h2">
                        <a href="<?php echo the_permalink(); ?>"><?php echo $post->post_count; ?>) <?php the_title(); ?></a>
                        <?php do_action('gmw_search_results_after_title', $gmw, $post); ?>
                    </h2>
                </div>
                
                <!--  Thumbnail -->
                <?php if ( isset( $gmw['search_results']['featured_image']['use'] ) && has_post_thumbnail() ) { ?>
                	
                	 <?php do_action( 'gmw_posts_loop_before_image' , $gmw, $post ); ?>
                	                
					 <div id="wppl-thumb" class="wppl-thumb">
						<?php the_post_thumbnail( array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) ); ?>
					</div>
				<?php } ?>

                <!--  Excerpt -->
				<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
				
					<?php do_action( 'gmw_posts_loop_before_excerpt' , $gmw, $post ); ?>
					                
					<div class="excerpt wppl-excerpt">
						<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
					</div>
				<?php } ?>

                <?php do_action( 'gmw_posts_loop_before_taxonomies' , $gmw, $post ); ?>
                
                <!--  taxonomies -->
                <div id="wppl-taxes-wrapper" class="wppl-taxes-wrapper">
                    <?php gmw_pt_taxonomies( $gmw, $post ); ?>
                </div>

                <div class="wppl-info">

                    <div class="wppl-info-left">
                        <div class="closest-taxonomy">
                            <span class="wppl-closest-taxonomy-label"><?php echo $gmw['closest_taxonomy_label']?>:</span>
                            <span class="wppl-closest-taxonomy"><?php gmw_distance_to_location( $post, $gmw ); ?></span>
                        </div>
                    </div>

                    <!-- info left ends-->

                    <div class="wppl-info-right">
                        <div class="num-taxonomies">
                            <span class="wppl-num-taxonomies-label"><?php echo $gmw['number_of_taxonomies_label']?>:</span>
                            <span class="wpppl-num-taxonomies"><?php echo $post->num_taxonomies ?></span>
                        </div>

                    </div><!-- info right -->

                </div> <!-- info -->
                
                <?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $post ); ?>

            </div> <!--  single- wrapper ends -->

           <div class="clear"></div>  

        <?php endwhile; ?>
        <!--  end of the loop -->

    </div> <!--  results wrapper -->    
	
	<?php do_action( 'gmw_search_results_before_bottom_pagination' , $gmw, $post ); ?>
	
    <div class="gmw-pt-tx-pagination-wrapper gmw-pt-tx-bottom-pagination-wrapper">
        <!--  paginations -->
        <?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
    </div> 

</div> <!-- output wrapper -->
