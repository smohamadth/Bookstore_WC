<?php
/**
 * Search results.
 *
 * @package Inkwell
 */
get_header();
?>
<div class="container two-col">
	<div>
		<header class="page-header">
			<span class="eyebrow"><?php esc_html_e( 'Search', 'inkwell' ); ?></span>
			<h1>
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Results for “%s”', 'inkwell' ), esc_html( get_search_query() ) );
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="posts-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content' );
				}
				?>
			</div>
			<?php inkwell_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
