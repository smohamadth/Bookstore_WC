<?php
/**
 * Store benefits strip. Merchant promises are only rendered when configured.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_valueprops_hide', false ) ) {
	return;
}

$items = array(
	array(
		'icon'  => 'book',
		'title' => __( 'Hand-picked selection', 'inkwell' ),
		'text'  => __( 'Curated titles and honest recommendations.', 'inkwell' ),
	),
	array(
		'icon'  => 'search',
		'title' => __( 'Discover your next read', 'inkwell' ),
		'text'  => __( 'Browse books by genre and author.', 'inkwell' ),
	),
);

$shipping = inkwell_mod( 'inkwell_shipping_message', '' );
$delivery = inkwell_mod( 'inkwell_fulfillment_message', '' );
$returns  = inkwell_mod( 'inkwell_returns_message', '' );
if ( $shipping ) {
	$items[] = array( 'icon' => 'truck', 'title' => $shipping, 'text' => __( 'See checkout for availability and terms.', 'inkwell' ) );
}
if ( $delivery ) {
	$items[] = array( 'icon' => 'check', 'title' => $delivery, 'text' => __( 'Order handling information from this store.', 'inkwell' ) );
}
if ( $returns ) {
	$items[] = array( 'icon' => 'return', 'title' => $returns, 'text' => __( 'See the store policy for full details.', 'inkwell' ) );
}
$items = array_slice( $items, 0, 4 );
?>
<section class="value-props" aria-label="<?php esc_attr_e( 'Why shop with us', 'inkwell' ); ?>">
	<div class="container value-props-grid value-props-grid--<?php echo esc_attr( count( $items ) ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<div class="value-prop" data-reveal>
				<span class="vp-icon"><?php echo inkwell_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<div><strong><?php echo esc_html( $item['title'] ); ?></strong><span><?php echo esc_html( $item['text'] ); ?></span></div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
