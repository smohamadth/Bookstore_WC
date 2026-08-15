<?php
/**
 * Plugin Name: Inkwell Books
 * Plugin URI: https://github.com/smohamadth/Bookstore_WC
 * Description: Portable book-author metadata and privacy-aware reading-list features for the Inkwell WooCommerce theme.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Inkwell Studio
 * Author URI: https://github.com/smohamadth
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INKWELL_BOOKS_VERSION', '1.0.0' );
define( 'INKWELL_BOOKS_PLUGIN_ACTIVE', true );

require __DIR__ . '/includes/books.php';
require __DIR__ . '/includes/newsletter.php';
