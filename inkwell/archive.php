<?php
/**
 * Generic archive (categories, tags, dates).
 *
 * @package Inkwell
 */
get_header();

$title = '';
if ( is_category() ) {
	$title = single_cat_title( '', false );
} elseif ( is_tag() ) {
	$title = single_tag_title( '', false );
} elseif ( is_date() ) {
	$title = get_the_date( _x( 'F Y', 'archive date format', 'inkwell' ) );
} elseif ( is_author() ) {
	$title = get_the_author();
} elseif ( is_search() ) {
	$title = sprintf( __( 'Results for “%s”', 'inkwell' ), get_search_query() );
} else {
	$title = __( 'Archive', 'inkwell' );
}
?>
<div class="container two-col">
	<div>
		<header class="page-header">
			<span class="eyebrow"><?php esc_html_e( 'The journal', 'inkwell' ); ?></span>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php the_archive_description( '<div class="page-sub">', '</div>' ); ?>
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
