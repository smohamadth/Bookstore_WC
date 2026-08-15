<?php
/**
 * Footer + closing tags.
 *
 * @package Inkwell
 */
?>
	</main><!-- #primary -->

	<footer id="colophon" class="site-footer">
		<div class="container">
			<div class="footer-top">

				<div class="footer-col footer-about">
					<span class="site-title"><?php bloginfo( 'name' ); ?></span>
					<p>
						<?php
						$about = inkwell_mod( 'inkwell_footer_about', '' );
						if ( $about ) {
							echo esc_html( $about );
						} else {
							esc_html_e( 'A hand-picked collection of books for curious minds — fiction, history, science and stories for every shelf.', 'inkwell' );
						}
						?>
					</p>
					<div class="footer-social">
						<a href="#" aria-label="Facebook" rel="me noopener"><?php echo inkwell_icon( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<a href="#" aria-label="Instagram" rel="me noopener"><?php echo inkwell_icon( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<a href="#" aria-label="X (formerly Twitter)" rel="me noopener"><?php echo inkwell_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					</div>
				</div>

				<div class="footer-col">
					<h4><?php esc_html_e( 'Shop', 'inkwell' ); ?></h4>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-shop',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => 'wp_page_menu',
						)
					);
					?>
				</div>

				<div class="footer-col">
					<h4><?php esc_html_e( 'Help', 'inkwell' ); ?></h4>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-help',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => 'wp_page_menu',
						)
					);
					?>
				</div>

				<div class="footer-col">
					<h4><?php esc_html_e( 'The reading list', 'inkwell' ); ?></h4>
					<p style="margin-bottom:1.2em;"><?php esc_html_e( 'One thoughtful email a month. New arrivals, staff picks, no spam.', 'inkwell' ); ?></p>
					<?php echo inkwell_newsletter_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

			</div>

			<div class="footer-bottom">
				<p style="margin:0;">
					<?php
					$copyright = inkwell_mod( 'inkwell_footer_copyright', '' );
					if ( $copyright ) {
						echo esc_html( $copyright );
					} else {
						printf(
							/* translators: 1: year, 2: site name. */
							esc_html__( '© %1$s %2$s. All rights reserved.', 'inkwell' ),
							esc_html( gmdate( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
					}
					?>
					<span class="theme-version">· <?php echo esc_html( sprintf( 'Inkwell v%s', INKWELL_VERSION ) ); ?></span>
				</p>
				<div class="payment-badges" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'inkwell' ); ?>">
					<span>VISA</span><span>Mastercard</span><span>Amex</span><span>PayPal</span><span>Apple&nbsp;Pay</span>
				</div>
			</div>
		</div>
	</footer>

	<button class="back-to-top" data-back-to-top aria-label="<?php esc_attr_e( 'Back to top', 'inkwell' ); ?>">
		<?php echo inkwell_icon( 'up' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</button>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
