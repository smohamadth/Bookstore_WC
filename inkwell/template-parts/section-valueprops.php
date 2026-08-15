<?php
/**
 * Value props strip (icon tiles).
 *
 * @package Inkwell
 */
?>
<section class="value-props" aria-label="<?php esc_attr_e( 'Why shop with us', 'inkwell' ); ?>">
	<div class="container value-props-grid">
		<div class="value-prop" data-reveal>
			<span class="vp-icon"><?php echo inkwell_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div><strong><?php esc_html_e( 'Free shipping over €25', 'inkwell' ); ?></strong><span><?php esc_html_e( 'On every order across the store.', 'inkwell' ); ?></span></div>
		</div>
		<div class="value-prop" data-reveal>
			<span class="vp-icon"><?php echo inkwell_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div><strong><?php esc_html_e( 'Hand-picked selection', 'inkwell' ); ?></strong><span><?php esc_html_e( 'Curated titles, honest reviews.', 'inkwell' ); ?></span></div>
		</div>
		<div class="value-prop" data-reveal>
			<span class="vp-icon"><?php echo inkwell_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div><strong><?php esc_html_e( 'Secure checkout', 'inkwell' ); ?></strong><span><?php esc_html_e( 'Encrypted payments, always.', 'inkwell' ); ?></span></div>
		</div>
		<div class="value-prop" data-reveal>
			<span class="vp-icon"><?php echo inkwell_icon( 'return' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div><strong><?php esc_html_e( '30-day returns', 'inkwell' ); ?></strong><span><?php esc_html_e( 'Changed your mind? No problem.', 'inkwell' ); ?></span></div>
		</div>
	</div>
</section>
