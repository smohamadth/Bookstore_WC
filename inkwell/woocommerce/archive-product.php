<?php
/**
 * Shop archive (main shop page).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 8.6.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

inkwell_shop_archive_layout();

/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
