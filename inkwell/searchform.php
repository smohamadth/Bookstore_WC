<?php
/**
 * Search form — site-wide search, products + posts + authors.
 *
 * @package Inkwell
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'inkwell' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search books, authors…', 'inkwell' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	</label>
	<button type="submit" class="button"><?php esc_html_e( 'Search', 'inkwell' ); ?></button>
</form>
