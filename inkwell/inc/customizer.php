<?php
/**
 * Customizer: every front-page section + colors + footer.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product category choices for the customizer.
 *
 * @return array
 */
function inkwell_category_choices() {
	$choices = array( '' => __( '— None —', 'inkwell' ) );
	if ( taxonomy_exists( 'product_cat' ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 40,
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$choices[ $term->term_id ] = $term->name;
			}
		}
	}
	return $choices;
}

/**
 * Register settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function inkwell_customize_register( $wp_customize ) {
	/* ------------------------------------------------------------------ *
	 * Colors
	 * ------------------------------------------------------------------ */
	$wp_customize->add_section(
		'inkwell_colors',
		array( 'title' => __( 'Inkwell Colors', 'inkwell' ), 'priority' => 25 )
	);

	$wp_customize->add_setting(
		'inkwell_accent',
		array( 'default' => '#2e6b52', 'sanitize_callback' => 'sanitize_hex_color' )
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'inkwell_accent',
			array(
				'label'   => __( 'Accent color', 'inkwell' ),
				'section' => 'inkwell_colors',
			)
		)
	);

	$wp_customize->add_setting(
		'inkwell_accent_dark',
		array( 'default' => '#21503d', 'sanitize_callback' => 'sanitize_hex_color' )
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'inkwell_accent_dark',
			array(
				'label'   => __( 'Accent (hover / dark)', 'inkwell' ),
				'section' => 'inkwell_colors',
			)
		)
	);

	$wp_customize->add_setting(
		'inkwell_header_dark',
		array( 'default' => false, 'sanitize_callback' => 'inkwell_sanitize_checkbox' )
	);
	$wp_customize->add_control(
		'inkwell_header_dark',
		array(
			'label'   => __( 'Dark header & announcement bar', 'inkwell' ),
			'type'    => 'checkbox',
			'section' => 'inkwell_colors',
		)
	);

	/* ------------------------------------------------------------------ *
	 * Header
	 * ------------------------------------------------------------------ */
	$wp_customize->add_section(
		'inkwell_header',
		array( 'title' => __( 'Header & Announcement Bar', 'inkwell' ), 'priority' => 28 )
	);

	$wp_customize->add_setting(
		'inkwell_announcement',
		array( 'default' => '', 'sanitize_callback' => 'wp_kses_post' )
	);
	$wp_customize->add_control(
		'inkwell_announcement',
		array(
			'label'       => __( 'Announcement bar text', 'inkwell' ),
			'description' => __( 'e.g. “Free shipping on orders over €25”. Leave empty to hide the bar.', 'inkwell' ),
			'type'        => 'text',
			'section'     => 'inkwell_header',
		)
	);

	/* ------------------------------------------------------------------ *
	 * Store policies
	 * ------------------------------------------------------------------ */
	$wp_customize->add_section(
		'inkwell_store_policies',
		array( 'title' => __( 'Store Benefits & Policies', 'inkwell' ), 'priority' => 29 )
	);

	$policy_fields = array(
		'inkwell_shipping_message'    => __( 'Shipping benefit', 'inkwell' ),
		'inkwell_fulfillment_message' => __( 'Fulfillment promise', 'inkwell' ),
		'inkwell_returns_message'     => __( 'Returns policy summary', 'inkwell' ),
		'inkwell_payment_methods'     => __( 'Accepted payment methods (comma-separated)', 'inkwell' ),
	);
	foreach ( $policy_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'inkwell_sanitize_field' ) );
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $label,
				'description' => __( 'Leave blank to hide this claim. Only publish terms your store actually offers.', 'inkwell' ),
				'type'        => 'text',
				'section'     => 'inkwell_store_policies',
			)
		);
	}

	/* ------------------------------------------------------------------ *
	 * Hero
	 * ------------------------------------------------------------------ */
	$wp_customize->add_section(
		'inkwell_hero',
		array( 'title' => __( 'Hero Section', 'inkwell' ), 'priority' => 30 )
	);

	$hero_fields = array(
		'inkwell_hero_hide'    => array( 'checkbox', __( 'Hide the hero entirely', 'inkwell' ) ),
		'inkwell_hero_eyebrow' => array( 'text', __( 'Eyebrow text', 'inkwell' ) ),
		'inkwell_hero_title'   => array( 'text', __( 'Headline', 'inkwell' ) ),
		'inkwell_hero_sub'     => array( 'textarea', __( 'Subheadline', 'inkwell' ) ),
		'inkwell_hero_btn1'    => array( 'text', __( 'Primary button label', 'inkwell' ) ),
		'inkwell_hero_url1'    => array( 'url', __( 'Primary button URL', 'inkwell' ) ),
		'inkwell_hero_btn2'    => array( 'text', __( 'Secondary button label', 'inkwell' ) ),
		'inkwell_hero_url2'    => array( 'url', __( 'Secondary button URL', 'inkwell' ) ),
	);

	foreach ( $hero_fields as $id => $cfg ) {
		$sanitize = 'inkwell_sanitize_field';
		if ( 'url' === $cfg[0] ) {
			$sanitize = 'esc_url_raw';
		} elseif ( 'checkbox' === $cfg[0] ) {
			$sanitize = 'inkwell_sanitize_checkbox';
		}
		$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => $sanitize ) );
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $cfg[1],
				'type'    => $cfg[0],
				'section' => 'inkwell_hero',
			)
		);
	}

	$wp_customize->add_setting(
		'inkwell_hero_image',
		array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' )
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'inkwell_hero_image',
			array(
				'label'   => __( 'Hero background image', 'inkwell' ),
				'section' => 'inkwell_hero',
			)
		)
	);

	/* ------------------------------------------------------------------ *
	 * Home page sections
	 * ------------------------------------------------------------------ */
	$wp_customize->add_section(
		'inkwell_home',
		array( 'title' => __( 'Home Page Sections', 'inkwell' ), 'priority' => 35 )
	);

	$home_fields = array(
		'inkwell_home_valueprops_hide' => array( 'checkbox', __( 'Hide the store benefits strip', 'inkwell' ) ),
		'inkwell_home_categories_hide' => array( 'checkbox', __( 'Hide genre tiles', 'inkwell' ) ),
		'inkwell_home_bestsellers_hide' => array( 'checkbox', __( 'Hide bestsellers row', 'inkwell' ) ),
		'inkwell_home_new_hide'         => array( 'checkbox', __( 'Hide new arrivals row', 'inkwell' ) ),
		'inkwell_home_quote_hide'       => array( 'checkbox', __( 'Hide quote band', 'inkwell' ) ),
		'inkwell_home_quote'            => array( 'textarea', __( 'Quote text', 'inkwell' ) ),
		'inkwell_home_quote_attr'       => array( 'text', __( 'Quote attribution', 'inkwell' ) ),
		'inkwell_home_testimonials_hide' => array( 'checkbox', __( 'Hide testimonials', 'inkwell' ) ),
		'inkwell_home_blog_hide'        => array( 'checkbox', __( 'Hide journal preview', 'inkwell' ) ),
		'inkwell_home_newsletter_hide'  => array( 'checkbox', __( 'Hide newsletter band', 'inkwell' ) ),
		'inkwell_home_newsletter_title' => array( 'text', __( 'Newsletter title', 'inkwell' ) ),
		'inkwell_home_newsletter_text'  => array( 'textarea', __( 'Newsletter text', 'inkwell' ) ),
	);
	foreach ( $home_fields as $id => $cfg ) {
		$sanitize = ( 'checkbox' === $cfg[0] ) ? 'inkwell_sanitize_checkbox' : 'inkwell_sanitize_field';
		$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => $sanitize ) );
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $cfg[1],
				'type'    => $cfg[0],
				'section' => 'inkwell_home',
			)
		);
	}

	// Featured genre tiles.
	for ( $i = 1; $i <= 4; $i++ ) {
		$wp_customize->add_setting(
			"inkwell_home_cat_{$i}",
			array( 'default' => '', 'sanitize_callback' => 'absint' )
		);
		$wp_customize->add_control(
			"inkwell_home_cat_{$i}",
			array(
				'label'   => sprintf(
					/* translators: %d: tile number. */
					__( 'Genre tile %d (product category)', 'inkwell' ),
					$i
				),
				'type'    => 'select',
				'choices' => inkwell_category_choices(),
				'section' => 'inkwell_home',
			)
		);
	}

	// Testimonials.
	for ( $i = 1; $i <= 3; $i++ ) {
		$wp_customize->add_setting(
			"inkwell_home_testimonial_{$i}_text",
			array( 'default' => '', 'sanitize_callback' => 'inkwell_sanitize_field' )
		);
		$wp_customize->add_control(
			"inkwell_home_testimonial_{$i}_text",
			array(
				'label'   => sprintf( __( 'Testimonial %d — quote', 'inkwell' ), $i ),
				'type'    => 'textarea',
				'section' => 'inkwell_home',
			)
		);
		$wp_customize->add_setting(
			"inkwell_home_testimonial_{$i}_name",
			array( 'default' => '', 'sanitize_callback' => 'inkwell_sanitize_field' )
		);
		$wp_customize->add_control(
			"inkwell_home_testimonial_{$i}_name",
			array(
				'label'   => sprintf( __( 'Testimonial %d — name', 'inkwell' ), $i ),
				'type'    => 'text',
				'section' => 'inkwell_home',
			)
		);
	}

	/* ------------------------------------------------------------------ *
	 * Footer
	 * ------------------------------------------------------------------ */
	$wp_customize->add_section(
		'inkwell_footer',
		array( 'title' => __( 'Footer', 'inkwell' ), 'priority' => 40 )
	);

	$wp_customize->add_setting(
		'inkwell_footer_about',
		array( 'default' => '', 'sanitize_callback' => 'inkwell_sanitize_field' )
	);
	$wp_customize->add_control(
		'inkwell_footer_about',
		array(
			'label'   => __( 'About text (footer)', 'inkwell' ),
			'type'    => 'textarea',
			'section' => 'inkwell_footer',
		)
	);

	$wp_customize->add_setting(
		'inkwell_footer_copyright',
		array( 'default' => '', 'sanitize_callback' => 'inkwell_sanitize_field' )
	);
	$wp_customize->add_control(
		'inkwell_footer_copyright',
		array(
			'label'   => __( 'Copyright line', 'inkwell' ),
			'type'    => 'text',
			'section' => 'inkwell_footer',
		)
	);

	$social_fields = array(
		'inkwell_social_facebook'  => __( 'Facebook URL', 'inkwell' ),
		'inkwell_social_instagram' => __( 'Instagram URL', 'inkwell' ),
		'inkwell_social_x'         => __( 'X / Twitter URL', 'inkwell' ),
	);
	foreach ( $social_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'type'    => 'url',
				'section' => 'inkwell_footer',
			)
		);
	}
}

/**
 * Sanitize a checkbox.
 *
 * @param mixed $checked Value.
 * @return bool
 */
function inkwell_sanitize_checkbox( $checked ) {
	return isset( $checked ) && in_array( $checked, array( true, 1, '1', 'on' ), true );
}

/**
 * Sanitize text-ish fields.
 *
 * @param mixed $value Value.
 * @return string
 */
function inkwell_sanitize_field( $value ) {
	return sanitize_textarea_field( $value );
}
