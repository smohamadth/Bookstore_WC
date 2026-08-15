<?php
/**
 * Bestsellers row (featured products).
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_bestsellers_hide', false ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}

$products = inkwell_get_bestsellers();
if ( empty( $products ) ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<?php
		inkwell_section_header(
			__( 'Reader favourites', 'inkwell' ),
			__( 'Bestsellers', 'inkwell' ),
			function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
			__( 'Shop all', 'inkwell' )
		);
		inkwell_render_products_row( $products, 8, true );
		?>
	</div>
</section>
