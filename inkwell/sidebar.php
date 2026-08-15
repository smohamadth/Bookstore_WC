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
	<?php dynamic_sidebar( $inkwell_sidebar ); ?>
</aside>
