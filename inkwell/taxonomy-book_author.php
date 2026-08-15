<?php
/**
 * Book author archive: bio + all their books.
 *
 * @package Inkwell
 */
get_header();

$inkwell_term = get_queried_object();
?>
<div class="container">
	<header class="page-header author-archive-head">
		<span class="eyebrow"><?php esc_html_e( 'Book author', 'inkwell' ); ?></span>
		<div class="author-head-inner">
			<div class="author-monogram"><?php echo esc_html( inkwell_first_character( $inkwell_term->name ) ); ?></div>
			<div>
				<h1><?php echo esc_html( $inkwell_term->name ); ?></h1>
				<?php
				$inkwell_bio = term_description( $inkwell_term );
				if ( $inkwell_bio ) {
					echo '<div class="page-sub">' . wp_kses_post( $inkwell_bio ) . '</div>';
				}
				?>
			</div>
		</div>
	</header>

	<?php if ( have_posts() ) : ?>
			<ul class="products products-row author-products-row">
			<?php
			while ( have_posts() ) {
				the_post();
				wc_get_template_part( 'content', 'product' );
			}
			?>
			</ul>
			<?php inkwell_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No books found for this author yet.', 'inkwell' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
