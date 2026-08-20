<?php
/**
 * Theme setup: supports, menus, image sizes, assets, widget areas.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * After setup theme.
 */
function inkwell_setup() {
	load_theme_textdomain( 'inkwell', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 220,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	/*
	 * WooCommerce support — classic layout with gallery features.
	 */
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'     => __( 'Primary Menu', 'inkwell' ),
			'footer-shop' => __( 'Footer — Shop', 'inkwell' ),
			'footer-help' => __( 'Footer — Help', 'inkwell' ),
		)
	);

	add_image_size( 'inkwell-card', 480, 640, true );
	add_image_size( 'inkwell-tile', 640, 400, true );
	add_image_size( 'inkwell-hero', 1600, 900, true );

	// Content width for embeds.
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'inkwell_setup' );

/**
 * Register widget areas.
 */
function inkwell_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Shop Sidebar', 'inkwell' ),
			'id'            => 'sidebar-shop',
			'description'   => __( 'Shown on the shop and product-category pages. Perfect for the Product Categories, Price Filter and On Sale widgets.', 'inkwell' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'inkwell' ),
			'id'            => 'sidebar-blog',
			'description'   => __( 'Shown on the journal archive and single posts.', 'inkwell' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'inkwell_widgets_init' );

/**
 * Enqueue styles and scripts.
 */
function inkwell_scripts() {
	$ver = INKWELL_VERSION;

	// Fonts are self-hosted (@font-face in style.css) — zero external requests.
	wp_enqueue_style( 'inkwell-style', get_stylesheet_uri(), array(), $ver );

	if ( class_exists( 'WooCommerce' ) ) {
		$wc_deps = array( 'inkwell-style' );
		foreach ( array( 'woocommerce-general', 'woocommerce-layout' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) ) {
				$wc_deps[] = $handle;
			}
		}
		wp_enqueue_style(
			'inkwell-woocommerce',
			get_template_directory_uri() . '/css/woocommerce.css',
			$wc_deps,
			$ver
		);
	}

	wp_enqueue_script(
		'inkwell-main',
		get_template_directory_uri() . '/js/main.js',
		array(),
		$ver,
		array( 'in_footer' => true )
	);

	wp_localize_script(
		'inkwell-main',
		'inkwellVars',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'newsletter'  => wp_create_nonce( 'inkwell_newsletter' ),
			'i18n'        => array(
				'subscribed' => __( 'Thank you — you are on the list!', 'inkwell' ),
				'error'      => __( 'Something went wrong. Please try again.', 'inkwell' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'inkwell_scripts', 20 );

/**
 * Accent color → CSS custom property.
 */
function inkwell_color_vars() {
	$accent      = sanitize_hex_color( get_theme_mod( 'inkwell_accent', '#b4532a' ) );
	$accent_dark = sanitize_hex_color( get_theme_mod( 'inkwell_accent_dark', '#8f3d1e' ) );
	if ( ! $accent ) {
		return;
	}
	if ( ! $accent_dark ) {
		$accent_dark = $accent;
	}
	?>
	<style id="inkwell-color-vars">
		:root { --accent: <?php echo esc_attr( $accent ); ?>; --accent-dark: <?php echo esc_attr( $accent_dark ); ?>; }
	</style>
	<?php
}
add_action( 'wp_head', 'inkwell_color_vars', 5 );

/**
 * Add helpful body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function inkwell_body_classes( $classes ) {
	if ( get_theme_mod( 'inkwell_header_dark', false ) ) {
		$classes[] = 'header-dark';
	}
	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	}
	return $classes;
}
add_filter( 'body_class', 'inkwell_body_classes' );

/**
 * Editor styles (admin).
 */
function inkwell_editor_styles() {
	add_editor_style( array( 'style.css' ) );
}
add_action( 'after_setup_theme', 'inkwell_editor_styles' );
