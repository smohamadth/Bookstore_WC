<?php
/**
 * Testimonials — SVG stars + initials avatars.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_testimonials_hide', false ) ) {
	return;
}

$items = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$text = inkwell_mod( "inkwell_home_testimonial_{$i}_text", '' );
	$name = inkwell_mod( "inkwell_home_testimonial_{$i}_name", '' );
	if ( $text || $name ) {
		$items[] = array( 'text' => $text, 'name' => $name );
	}
}
if ( empty( $items ) ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<?php inkwell_section_header( __( 'Word of mouth', 'inkwell' ), __( 'What readers say', 'inkwell' ) ); ?>
		<div class="testimonials-grid">
			<?php foreach ( $items as $item ) : ?>
				<figure class="testimonial" data-reveal>
					<span class="stars" aria-label="<?php esc_attr_e( 'Rated 5 out of 5', 'inkwell' ); ?>">
						<?php
						for ( $s = 0; $s < 5; $s++ ) {
							echo inkwell_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
					</span>
					<blockquote><?php echo esc_html( $item['text'] ); ?></blockquote>
					<figcaption class="cite-wrap">
						<span class="avatar-initials" aria-hidden="true"><?php echo esc_html( mb_substr( trim( (string) $item['name'] ), 0, 1 ) ); ?></span>
						<cite>
							<?php echo esc_html( $item['name'] ); ?>
							<span><?php esc_html_e( 'Verified reader', 'inkwell' ); ?></span>
						</cite>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
