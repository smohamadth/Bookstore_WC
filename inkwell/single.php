<?php
/**
 * Single post.
 *
 * @package Inkwell
 */
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'container single-post' ); ?>>
		<header class="single-post-header">
			<span class="eyebrow"><?php esc_html_e( 'Journal', 'inkwell' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<?php inkwell_posted_meta(); ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-post-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="two-col">
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'inkwell' ),
						'after'  => '</nav>',
					)
				);
				?>
				<?php if ( get_the_tags() ) : ?>
					<div class="entry-tags"><?php the_tags( '', ' ' ); ?></div>
				<?php endif; ?>
			</div>
			<?php get_sidebar(); ?>
		</div>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</article>
	<?php
endwhile;

get_footer();
