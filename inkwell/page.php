<?php
/**
 * Static pages (About, Contact, cart/checkout shortcode pages, …).
 *
 * @package Inkwell
 */
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'container' ); ?>>
		<header class="page-header">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="page-sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

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
		</div>
	</article>
	<?php
endwhile;

get_footer();
