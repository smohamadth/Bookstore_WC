<?php
/**
 * Newsletter band.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_newsletter_hide', false ) ) {
	return;
}

$title = inkwell_mod( 'inkwell_home_newsletter_title', '' );
$text  = inkwell_mod( 'inkwell_home_newsletter_text', '' );
if ( ! $title ) {
	$title = __( 'Join the reading list', 'inkwell' );
}
if ( ! $text ) {
	$text = __( 'One thoughtful email a month: new arrivals, staff picks and member-only offers. No spam, unsubscribe anytime.', 'inkwell' );
}
?>
<section class="section newsletter-band">
	<div class="container" data-reveal>
		<span class="eyebrow"><?php esc_html_e( 'Newsletter', 'inkwell' ); ?></span>
		<h2><?php echo esc_html( $title ); ?></h2>
		<p><?php echo esc_html( $text ); ?></p>
		<?php echo inkwell_newsletter_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
