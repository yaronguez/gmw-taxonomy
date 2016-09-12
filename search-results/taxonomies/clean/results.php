<?php
/**
 * Taxonomy locator "clean" search results template file.
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * 
 * You could but It is not recommended to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "clean" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/taxonomies/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the taxonomies locator form.
 * It will show in the "Search results" dropdown menu as "Custom: clean".
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-pt-results-wrapper">

	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
	
	<!-- results count -->
	<div class="gmw-results-count">
		<span><?php gmw_results_message( $gmw, false ); ?></span>
	</div>
	
	<?php do_action( 'gmw_search_results_before_top_pagination' , $gmw ); ?>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
		
	 <!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>
	
	<div class="clear"></div>
	
	<?php do_action( 'gmw_search_results_before_loop' , $gmw ); ?>
	
	<!--  Results wrapper -->
	<div class="gmw-posts-wrapper">
		<?php foreach($gmw['results'] as $taxonomy_term):?>
			<!--  single results wrapper  -->
			<div id="post-<?php echo $taxonomy_term->term_taxonomy_id ?>" class="gmw-taxonomy-term">

		
				<?php do_action( 'gmw_search_results_loop_item_start' , $taxonomy_term ); ?>

				<!-- Title -->
				<div class="wppl-title-holder">
					<h2 class="wppl-h2">
                        <a href="<?php echo get_term_link((int)$taxonomy_term->term_taxonomy_id , $taxonomy_term->taxonomy);?>"
                            title="<?php echo $taxonomy_term->name; ?>">
                            <?php echo $taxonomy_term->name; ?>
                        </a>
						<?php do_action('gmw_search_results_after_title', $gmw, $taxonomy_term); ?>
						<span class="radius-dis">(<?php gmw_distance_to_location( $taxonomy_term, $gmw ); ?>)</span>
					</h2>
				</div>

				<!--  Excerpt -->
				<?php if ( isset( $gmw['search_results']['show_description'] ) && $gmw['search_results']['show_description'] ): ?>
					
					<?php do_action( 'gmw_search_results_before_excerpt' , $gmw, $taxonomy_term ); ?>
					
					<div class="wppl-excerpt">
						<?php echo $taxonomy_term->description; ?>
					</div>
				<?php endif; ?>
				
				<?php do_action( 'gmw_search_results_before_taxonomies' , $gmw, $taxonomy_term ); ?>
					
		    	<div class="clear"></div>
		    	
		    	<div class="wppl-info">
		    		
		    		<div class="wppl-info-left">
			    		<?php if ( !empty( $gmw['search_results']['additional_info'] ) ) { ?>
    
					    	<?php do_action( 'gmw_search_results_before_contact_info', $taxonomy_term, $gmw ); ?>
						   	
						   	<div class="contact-info">
								<h3><?php echo $gmw['labels']['search_results']['contact_info']['contact_info']; ?></h3>
					    		<?php gmw_additional_info( $taxonomy_term, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], 'ul' ); ?>
					    	</div>
					    <?php } ?>	    
		    		</div>
		    		
		    		<?php if ( !empty( $gmw['search_results']['opening_hours'] ) ) { ?>
    
				    	<?php do_action( 'gmw_search_results_before_opening_hours', $taxonomy_term, $gmw ); ?>
					   	
				    	<div class="opening-hours">
				    		<?php gmw_tx_days_hours( $taxonomy_term, $gmw ); ?>
				    	</div>
				    <?php } ?>
					    
		    		<div class="wppl-info-right">
		    			
		    			<!--  Address -->
		    			<div class="wppl-address">
		    				<div class="address-wrapper">
						    	<span class="fa fa-map-marker address-icon"></span>
						    	<span class="wppl-address"><?php gmw_location_address( $taxonomy_term, $gmw ); ?></span>
						    </div>
		    			</div>
		    		
		    			<!--  Driving Distance -->
						<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
		    				<?php gmw_driving_distance( $taxonomy_term, $gmw, false ); ?>
		    			<?php } ?>
		    			
		    			<!-- Get directions -->	 	
						<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
							<div class="wppl-get-directions">
		    					<?php gmw_directions_link( $taxonomy_term, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
		    				</div>
		    			<?php } ?>
			    		

		    		</div><!-- info right end -->
		    	
		    	</div> <!-- info end -->
		    
		    	<?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $taxonomy_term ); ?>
		    	
		    </div> <!--  single- wrapper ends -->
		    
		    <div class="clear"></div>     
		  	
		<?php endforeach; ?>
			
		<!--  end of the loop -->
		
	</div> <!--  results wrapper -->    
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw ); ?>
		
	<?php do_action( 'gmw_search_results_before_bottom_pagination' , $gmw ); ?>
	
	<!--  Pagination -->
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_end' , $gmw ); ?>
	
</div> <!-- output wrapper -->