<?php
/**
 * Locale-aware presentation for theme-owned demo chrome.
 * Product/page content remains the responsibility of a multilingual content
 * plugin; these filters prevent imported menus, widgets, policies and category
 * labels from bypassing gettext.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a bundled non-English interface is active.
 *
 * @return bool
 */
function inkwell_uses_bundled_rtl_locale() {
	$locale = function_exists( 'inkwell_get_interface_locale' ) ? inkwell_get_interface_locale() : get_locale();
	return in_array( $locale, array( 'fa_IR', 'ckb' ), true );
}

/**
 * Translate known database strings written by the demo importer.
 *
 * @param string $value Stored value.
 * @return string
 */
function inkwell_translate_demo_value( $value ) {
	if ( ! inkwell_uses_bundled_rtl_locale() || ! is_string( $value ) ) {
		return $value;
	}

	static $translations = null;
	if ( null === $translations ) {
		$translations = array(
			'Home'                                                        => __( 'Home', 'inkwell' ),
			'Shop'                                                        => __( 'Shop', 'inkwell' ),
			'Genres'                                                      => __( 'Genres', 'inkwell' ),
			'Journal'                                                     => __( 'Journal', 'inkwell' ),
			'About'                                                       => __( 'About', 'inkwell' ),
			'Contact'                                                     => __( 'Contact', 'inkwell' ),
			'Filter by price'                                             => __( 'Filter by price', 'inkwell' ),
			'Top rated'                                                   => __( 'Top rated', 'inkwell' ),
			'Recent posts'                                                => __( 'Recent posts', 'inkwell' ),
			'Journal categories'                                         => __( 'Journal categories', 'inkwell' ),
			'Free shipping on orders over €25 — every book, everywhere.' => __( 'Free shipping on orders over €25 — every book, everywhere.', 'inkwell' ),
			'There is no friend as loyal as a book.'                      => __( 'There is no friend as loyal as a book.', 'inkwell' ),
			'Ordered on Monday, reading on Wednesday. Beautifully packed and the recommendation note was a lovely touch.' => __( 'Ordered on Monday, reading on Wednesday. Beautifully packed and the recommendation note was a lovely touch.', 'inkwell' ),
			'Inkwell found me a long-out-of-print edition I had hunted for years. Customers for life.' => __( 'Inkwell found me a long-out-of-print edition I had hunted for years. Customers for life.', 'inkwell' ),
			'The genre tiles make browsing a joy, and the staff picks never miss. My book budget is not okay.' => __( 'The genre tiles make browsing a joy, and the staff picks never miss. My book budget is not okay.', 'inkwell' ),
			'A hand-picked collection of books for curious minds — fiction, history, science and stories for every shelf.' => __( 'A hand-picked collection of books for curious minds — fiction, history, science and stories for every shelf.', 'inkwell' ),
			'Free shipping over €25'                  => __( 'Free shipping over €25', 'inkwell' ),
			'In stock — usually ships within 24 hours' => __( 'In stock — usually ships within 24 hours', 'inkwell' ),
			'30-day returns'                           => __( '30-day returns', 'inkwell' ),
		);
	}

	return isset( $translations[ $value ] ) ? $translations[ $value ] : $value;
}

/**
 * Translate imported navigation labels.
 *
 * @param string $title Menu title.
 * @return string
 */
function inkwell_translate_demo_menu_title( $title ) {
	return inkwell_translate_demo_value( $title );
}
add_filter( 'nav_menu_item_title', 'inkwell_translate_demo_menu_title' );
add_filter( 'widget_title', 'inkwell_translate_demo_value' );

/**
 * Translate imported theme options while preserving merchant-authored values.
 * Only exact known demo values are changed.
 */
function inkwell_register_demo_theme_mod_translations() {
	$mods = array(
		'inkwell_announcement',
		'inkwell_home_quote',
		'inkwell_home_testimonial_1_text',
		'inkwell_home_testimonial_2_text',
		'inkwell_home_testimonial_3_text',
		'inkwell_footer_about',
		'inkwell_shipping_message',
		'inkwell_fulfillment_message',
		'inkwell_returns_message',
	);
	foreach ( $mods as $mod ) {
		add_filter( 'theme_mod_' . $mod, 'inkwell_translate_demo_value' );
	}
}
add_action( 'after_setup_theme', 'inkwell_register_demo_theme_mod_translations', 30 );

/**
 * Translate the seven importer-owned product categories at display time.
 *
 * @param WP_Term|WP_Error|null $term     Term object.
 * @param string                $taxonomy Taxonomy name.
 * @return WP_Term|WP_Error|null
 */
function inkwell_translate_demo_term( $term, $taxonomy ) {
	if ( ! inkwell_uses_bundled_rtl_locale() || 'product_cat' !== $taxonomy || ! $term instanceof WP_Term ) {
		return $term;
	}

	$categories = array(
		'fiction'                 => array( __( 'Fiction', 'inkwell' ), __( 'Novels, classics and contemporary literary fiction.', 'inkwell' ) ),
		'science-fiction-fantasy' => array( __( 'Science Fiction & Fantasy', 'inkwell' ), __( 'Other worlds, far futures and impossible magic.', 'inkwell' ) ),
		'mystery-thriller'        => array( __( 'Mystery & Thriller', 'inkwell' ), __( 'Whodunits, suspense and page-turning thrillers.', 'inkwell' ) ),
		'non-fiction'             => array( __( 'Non-Fiction', 'inkwell' ), __( 'Ideas, science, business and big questions.', 'inkwell' ) ),
		'history-biography'       => array( __( 'History & Biography', 'inkwell' ), __( 'True stories and the lives behind them.', 'inkwell' ) ),
		'childrens-books'         => array( __( 'Children’s Books', 'inkwell' ), __( 'Picture books and first reads for young bookworms.', 'inkwell' ) ),
		'poetry'                  => array( __( 'Poetry', 'inkwell' ), __( 'Verse, collections and poems to savour slowly.', 'inkwell' ) ),
	);
	if ( isset( $categories[ $term->slug ] ) ) {
		$term              = clone $term;
		$term->name        = $categories[ $term->slug ][0];
		$term->description = $categories[ $term->slug ][1];
	}
	return $term;
}
add_filter( 'get_term', 'inkwell_translate_demo_term', 20, 2 );
