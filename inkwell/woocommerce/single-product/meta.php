<?php
/**
 * Product meta: SKU, book details, categories, author.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 9.7.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="product_meta">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || 'variable' === $product->get_type() ) ) : ?>
		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? esc_html( $sku ) : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>
	<?php endif; ?>

	<?php
	$inkwell_details = inkwell_get_book_details( $product );
	foreach ( $inkwell_details as $inkwell_label => $inkwell_value ) :
		?>
		<span class="book-detail">
			<span class="book-detail-label"><?php echo esc_html( $inkwell_label ); ?>:</span>
			<span class="book-detail-value"><?php echo esc_html( $inkwell_value ); ?></span>
		</span>
	<?php endforeach; ?>

	<?php
	$inkwell_authors = get_the_terms( $product->get_id(), 'book_author' );
	if ( $inkwell_authors && ! is_wp_error( $inkwell_authors ) ) :
		$inkwell_links = array();
		foreach ( $inkwell_authors as $inkwell_author ) {
			$inkwell_links[] = '<a href="' . esc_url( get_term_link( $inkwell_author ) ) . '" rel="tag">' . esc_html( $inkwell_author->name ) . '</a>';
		}
		?>
		<span class="posted_by"><?php echo esc_html__( 'Author:', 'inkwell' ) . ' ' . implode( ', ', $inkwell_links ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	<?php endif; ?>

	<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>
