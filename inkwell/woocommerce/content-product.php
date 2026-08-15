<?php
/**
 * Product card within loops — v2: cover + spine, quick-add overlay,
 * optional rank badge (bestsellers), "new" chip.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 9.4.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$inkwell_is_new = strtotime( (string) $product->get_date_created() ) > ( time() - 45 * DAY_IN_SECONDS );
$inkwell_rank   = isset( $GLOBALS['inkwell_loop_rank'] ) ? (int) $GLOBALS['inkwell_loop_rank'] : 0;
?>
<li <?php wc_product_class( '', $product ); ?>>

	<div class="card-media">
		<?php
		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item_title' );

		woocommerce_template_loop_product_link_open();
		woocommerce_template_loop_product_thumbnail();
		woocommerce_template_loop_product_link_close();
		?>

		<?php if ( $inkwell_rank > 0 ) : ?>
			<span class="rank-badge" aria-label="<?php echo esc_attr( sprintf( __( 'Number %d bestseller', 'inkwell' ), $inkwell_rank ) ); ?>"><?php echo esc_html( str_pad( (string) $inkwell_rank, 2, '0', STR_PAD_LEFT ) ); ?></span>
		<?php elseif ( $inkwell_is_new ) : ?>
			<span class="chip-new"><?php esc_html_e( 'New', 'inkwell' ); ?></span>
		<?php endif; ?>

		<div class="card-actions">
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</div>
	</div>

	<div class="card-body">
		<?php
		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );

		inkwell_product_authors();

		/**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );
		?>
	</div>

	<?php
	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 * (Inkwell renders the add-to-cart button inside .card-actions above;
	 * this hook stays for third-party additions.)
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
<?php
if ( $inkwell_rank > 0 && isset( $GLOBALS['inkwell_loop_rank'] ) ) {
	$GLOBALS['inkwell_loop_rank']++;
}
