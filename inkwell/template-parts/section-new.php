<?php
/**
 * New arrivals row.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_new_hide', false ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}

$products = inkwell_get_new_arrivals();
if ( empty( $products ) ) {
	return;
}
?>
<section class="section section--alt">
	<div class="container">
		<?php
		inkwell_section_header(
			__( 'Fresh off the press', 'inkwell' ),
			__( 'New arrivals', 'inkwell' ),
			function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
			__( 'Shop all', 'inkwell' )
		);
		inkwell_render_products_row( $products, 8 );
		?>
	</div>
</section>
