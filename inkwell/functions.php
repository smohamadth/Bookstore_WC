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

define( 'INKWELL_VERSION', '2.0.4' );

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/books.php';
require get_template_directory() . '/inc/woocommerce.php';
require get_template_directory() . '/inc/newsletter.php';
require get_template_directory() . '/inc/demo-import.php';
require get_template_directory() . '/inc/customizer.php';
