<?php
/**
 * Single product page.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Inkwell
 * @version 4.4.0 (adapted)
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

?>
<div class="container product-page">
	<?php
	while ( have_posts() ) :
		the_post();

		woocommerce_breadcrumb();

		wc_get_template_part( 'content', 'single-product' );
	endwhile;
	?>
</div>
<?php

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
