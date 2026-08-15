<?php
/**
 * Front page: hero + curated sections (all toggles in Customizer).
 *
 * @package Inkwell
 */

get_header();

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/section', 'valueprops' );
get_template_part( 'template-parts/section', 'categories' );
get_template_part( 'template-parts/section', 'bestsellers' );
get_template_part( 'template-parts/section', 'quote' );
get_template_part( 'template-parts/section', 'new' );
get_template_part( 'template-parts/section', 'testimonials' );
get_template_part( 'template-parts/section', 'blog' );
get_template_part( 'template-parts/section', 'newsletter' );

get_footer();
