<?php
/**
 * Loop start — book grid.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 3.3.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;
?>
<ul class="products columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?> inkwell-shop-grid">
