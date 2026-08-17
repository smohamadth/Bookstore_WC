<?php
/**
 * Inkwell — theme bootstrap.
 *
 * Loads the theme's modules in dependency order.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INKWELL_VERSION', '2.0.5' );

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/languages.php';
require get_template_directory() . '/inc/template-tags.php';

// Backward-compatible fallbacks: the companion Inkwell Books plugin loads
// these persistent data features first when it is active.
if ( ! function_exists( 'inkwell_register_book_author' ) ) {
	require get_template_directory() . '/inc/books.php';
}
if ( ! function_exists( 'inkwell_newsletter_form' ) ) {
	require get_template_directory() . '/inc/newsletter.php';
}

require get_template_directory() . '/inc/woocommerce.php';
require get_template_directory() . '/inc/demo-import.php';
require get_template_directory() . '/inc/customizer.php';

/**
 * Ensure author archive rewrites exist immediately after theme activation.
 */
function inkwell_flush_rewrites_on_switch() {
	if ( function_exists( 'inkwell_register_book_author' ) ) {
		inkwell_register_book_author();
	}
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'inkwell_flush_rewrites_on_switch' );
