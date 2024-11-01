<?php
/*
Template Name: Shopp Store Closed
*/
?>
<?php get_header(); ?>
<div id="content" class="widecolumn">

<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : // The Loop ?>
					<?php the_post(); ?>
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<!-- title, meta, and date info -->
						<div class="entry-header clearfix">
                       	<?php 
                            echo '<h1>';
                            the_title();
                            echo '</h1>';
                        ?>
                        </div>
						<!-- post content -->
						<div class="entry-content clearfix">
							<?php the_content(); ?>
						</div>
                    </div>
					<!-- end .post -->
				<?php endwhile; // end of one post ?>
            </div>
        </div>
<?php endif; ?> 
	
</div>
<?php get_footer(); ?>

