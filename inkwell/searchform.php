<?php
/**
 * Book search form — WooCommerce products and book authors.
 *
 * @package Inkwell
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php if ( post_type_exists( 'product' ) ) : ?>
		<input type="hidden" name="post_type" value="product" />
	<?php endif; ?>
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'inkwell' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search books, authors…', 'inkwell' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	</label>
	<button type="submit" class="button"><?php esc_html_e( 'Search', 'inkwell' ); ?></button>
</form>
