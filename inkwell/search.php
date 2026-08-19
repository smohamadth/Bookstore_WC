<?php
/**
 * Product and author search results.
 *
 * @package Inkwell
 */
get_header();

$inkwell_is_product_search = class_exists( 'WooCommerce' ) && 'product' === get_query_var( 'post_type' );
?>
<div class="container two-col<?php echo $inkwell_is_product_search ? ' woocommerce' : ''; ?>">
	<div>
		<header class="page-header">
			<span class="eyebrow"><?php echo $inkwell_is_product_search ? esc_html__( 'Book search', 'inkwell' ) : esc_html__( 'Search', 'inkwell' ); ?></span>
			<h1>
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Results for “%s”', 'inkwell' ), esc_html( get_search_query() ) );
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<?php if ( $inkwell_is_product_search ) : ?>
				<?php
				wc_set_loop_prop( 'columns', 3 );
				wc_set_loop_prop( 'total', (int) $GLOBALS['wp_query']->found_posts );
				?>
				<div class="woocommerce-before-shop-loop">
					<?php do_action( 'woocommerce_before_shop_loop' ); ?>
				</div>
				<?php woocommerce_product_loop_start(); ?>
				<?php
				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
				?>
				<?php woocommerce_product_loop_end(); ?>
				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php else : ?>
				<div class="posts-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content' );
					}
					?>
				</div>
				<?php inkwell_pagination(); ?>
			<?php endif; ?>
		<?php elseif ( $inkwell_is_product_search ) : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
