<?php
/**
 * Generic sidebar (shop or blog by context).
 *
 * @package Inkwell
 */
$inkwell_is_shop_context = function_exists( 'is_woocommerce' ) && ( is_woocommerce() || 'product' === get_query_var( 'post_type' ) );
$inkwell_sidebar         = $inkwell_is_shop_context ? 'sidebar-shop' : 'sidebar-blog';
if ( ! is_active_sidebar( $inkwell_sidebar ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'inkwell' ); ?>">
	<?php if ( $inkwell_is_shop_context ) : ?>
		<div class="shop-filter-drawer-head">
			<strong><?php esc_html_e( 'Refine your shelf', 'inkwell' ); ?></strong>
			<button type="button" data-shop-filter-close aria-label="<?php esc_attr_e( 'Close filters', 'inkwell' ); ?>"><?php echo inkwell_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</div>
	<?php endif; ?>
	<?php dynamic_sidebar( $inkwell_sidebar ); ?>
</aside>
