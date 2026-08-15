<?php
/**
 * Product category archive.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 4.7.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

inkwell_shop_archive_layout();

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
