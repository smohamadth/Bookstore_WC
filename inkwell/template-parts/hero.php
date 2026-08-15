<?php
/**
 * Hero — dark split with floating book covers.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_hero_hide', false ) ) {
	return;
}

$eyebrow = inkwell_mod( 'inkwell_hero_eyebrow', '' );
$title   = inkwell_mod( 'inkwell_hero_title', '' );
$sub     = inkwell_mod( 'inkwell_hero_sub', '' );
$btn1    = inkwell_mod( 'inkwell_hero_btn1', '' );
$url1    = inkwell_mod( 'inkwell_hero_url1', '' );
$btn2    = inkwell_mod( 'inkwell_hero_btn2', '' );
$url2    = inkwell_mod( 'inkwell_hero_url2', '' );
$image   = inkwell_mod( 'inkwell_hero_image', '' );

if ( ! $eyebrow && ! $title && ! $sub && ! $btn1 && ! $btn2 ) {
	$eyebrow = __( 'The modern library', 'inkwell' );
	$title   = __( 'Books worth <em>keeping</em>', 'inkwell' );
	$sub     = __( 'Hand-picked fiction, history, science and poetry — from modern classics to hidden gems, delivered with care.', 'inkwell' );
	$btn1    = __( 'Shop bestsellers', 'inkwell' );
	$url1    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$btn2    = __( 'Browse genres', 'inkwell' );
	$url2    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

/*
 * Cover collage: first three featured products (fallback: latest products).
 */
$hero_products = array();
if ( class_exists( 'WooCommerce' ) ) {
	$hero_products = inkwell_get_bestsellers();
	if ( empty( $hero_products ) ) {
		$hero_products = inkwell_get_new_arrivals();
	}
	$hero_products = array_slice( $hero_products, 0, 3 );
}
?>
<section class="hero" data-reveal>
	<div class="container hero-inner">

		<div class="hero-copy">
			<?php if ( $eyebrow ) : ?>
				<span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<h1><?php echo wp_kses_post( $title ); ?></h1>
			<?php if ( $sub ) : ?>
				<p class="hero-sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
			<?php if ( $btn1 || $btn2 ) : ?>
				<div class="hero-ctas">
					<?php if ( $btn1 && $url1 ) : ?>
						<a class="button button--gold" href="<?php echo esc_url( $url1 ); ?>"><?php echo esc_html( $btn1 ); ?></a>
					<?php endif; ?>
					<?php if ( $btn2 && $url2 ) : ?>
						<a class="button button--outline-light" href="<?php echo esc_url( $url2 ); ?>"><?php echo esc_html( $btn2 ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="hero-trust">
				<span><?php echo inkwell_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Free shipping over €25', 'inkwell' ); ?></span>
				<span><?php echo inkwell_icon( 'return' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( '30-day returns', 'inkwell' ); ?></span>
				<span><?php echo inkwell_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Secure checkout', 'inkwell' ); ?></span>
			</div>
		</div>

		<?php if ( ! empty( $hero_products ) ) : ?>
			<div class="hero-covers" aria-hidden="true">
				<?php
				foreach ( $hero_products as $i => $hp ) :
					$img = $hp->get_image( 'inkwell-card', array( 'loading' => 'eager' ) );
					if ( ! $img ) {
						continue;
					}
					?>
					<div class="hero-cover c<?php echo (int) ( $i + 1 ); ?>"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endforeach; ?>
				<span class="hero-badge"><?php echo inkwell_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Staff picks', 'inkwell' ); ?></span>
			</div>
		<?php endif; ?>

	</div>
</section>
