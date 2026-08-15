<?php
/**
 * Blog index.
 *
 * @package Inkwell
 */
get_header();
?>
<div class="container two-col">
	<div>
		<header class="page-header">
			<span class="eyebrow"><?php esc_html_e( 'The journal', 'inkwell' ); ?></span>
			<h1><?php echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Journal', 'inkwell' ) ); ?></h1>
			<p class="page-sub"><?php esc_html_e( 'Reading lists, behind-the-shelves stories and bookish notes.', 'inkwell' ); ?></p>
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
