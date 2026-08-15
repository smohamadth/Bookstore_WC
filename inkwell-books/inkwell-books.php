<?php
/**
 * Plugin Name: Inkwell Books
 * Plugin URI: https://github.com/smohamadth/Bookstore_WC
 * Description: Portable book-author metadata and privacy-aware reading-list features for the Inkwell WooCommerce theme.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: Inkwell Studio
 * Author URI: https://github.com/smohamadth
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: inkwell-books
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INKWELL_BOOKS_VERSION', '1.0.0' );
define( 'INKWELL_BOOKS_PLUGIN_ACTIVE', true );

/**
 * Load bundled translations for non-WordPress.org installations.
 */
function inkwell_books_load_textdomain() {
	load_plugin_textdomain( 'inkwell-books', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'inkwell_books_load_textdomain', 0 );

/**
 * Explain the declared dependency on WordPress versions that predate the
 * Requires Plugins header UI.
 */
function inkwell_books_woocommerce_notice() {
	if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error"><p><?php esc_html_e( 'Inkwell Books requires WooCommerce to manage book products.', 'inkwell-books' ); ?></p></div>
	<?php
}
add_action( 'admin_notices', 'inkwell_books_woocommerce_notice' );

require __DIR__ . '/includes/books.php';
require __DIR__ . '/includes/newsletter.php';

/**
 * Register author rewrites immediately when the companion plugin is activated.
 */
function inkwell_books_activate() {
	inkwell_register_book_author();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'inkwell_books_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
