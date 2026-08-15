<?php
/**
 * Quote band.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_quote_hide', false ) ) {
	return;
}

$quote = inkwell_mod( 'inkwell_home_quote', '' );
$attr  = inkwell_mod( 'inkwell_home_quote_attr', '' );
if ( ! $quote ) {
	$quote = __( 'A reader lives a thousand lives before he dies. The man who never reads lives only one.', 'inkwell' );
	$attr  = __( 'George R.R. Martin', 'inkwell' );
}
?>
<section class="section quote-band">
	<div class="container" data-reveal>
		<span class="quote-mark" aria-hidden="true">“</span>
		<blockquote><?php echo esc_html( $quote ); ?></blockquote>
		<?php if ( $attr ) : ?>
			<cite>— <?php echo esc_html( $attr ); ?></cite>
		<?php endif; ?>
	</div>
</section>
