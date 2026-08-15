<?php
/**
 * Product category tile within the categories widget/shortcode loops.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 4.7.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;
?>
<li <?php wc_product_cat_class( '', $category ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_subcategory.
	 */
	do_action( 'woocommerce_before_subcategory', $category );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<a href="' . esc_url( get_term_link( $category, 'product_cat' ) ) . '" class="cat-tile">';

	/**
	 * Hook: woocommerce_before_subcategory_title.
	 *
	 * @hooked woocommerce_subcategory_thumbnail - 10
	 */
	do_action( 'woocommerce_before_subcategory_title', $category );

	echo '<span class="cat-tile-title">';
	echo esc_html( $category->name );
	if ( $category->count > 0 ) {
		echo ' <span class="count">' . esc_html( $category->count ) . '</span>';
	}
	echo '</span>';

	/**
	 * Hook: woocommerce_after_subcategory_title.
	 */
	do_action( 'woocommerce_after_subcategory_title', $category );

	echo '</a>';

	/**
	 * Hook: woocommerce_after_subcategory.
	 */
	do_action( 'woocommerce_after_subcategory', $category );
	?>
</li>
