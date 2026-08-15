<?php
/**
 * 404.
 *
 * @package Inkwell
 */
get_header();
?>
<div class="container error-404">
	<p class="error-code" aria-hidden="true">404</p>
	<h1><?php esc_html_e( 'This page has been checked out', 'inkwell' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for has moved, been borrowed, or never existed. Try a search, or start from the shop.', 'inkwell' ); ?></p>

	<?php get_search_form(); ?>

	<div class="hero-ctas" style="justify-content:center;">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'inkwell' ); ?></a>
		<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
			<a class="button button--ghost" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse the shop', 'inkwell' ); ?></a>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
