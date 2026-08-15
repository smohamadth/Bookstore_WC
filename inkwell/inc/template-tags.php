<?php
/**
 * Template tags & helpers: icons, logo, cart, section headers, pagination.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icon set (stroke, 24×24, currentColor).
 *
 * @param string $name Icon name.
 * @return string SVG markup.
 */
function inkwell_icon( $name ) {
	$icons = array(
		'cart'   => '<path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6L5 3H2"/><circle cx="9" cy="20" r="1.6"/><circle cx="17" cy="20" r="1.6"/>',
		'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.5-4.5"/>',
		'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'  => '<path d="M6 6l12 12M18 6L6 18"/>',
		'arrow'  => '<path d="M4 12h16m-6-6l6 6-6 6"/>',
		'up'     => '<path d="M12 20V4m-6 6l6-6 6 6"/>',
		'truck'  => '<path d="M1 7h13v9H1zM14 10h4l4 3v3h-8"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
		'book'   => '<path d="M4 4.5A2.5 2.5 0 016.5 2H20v17H6.5A2.5 2.5 0 004 21.5z"/><path d="M4 4.5V21.5A2.5 2.5 0 016.5 19H20"/>',
		'shield' => '<path d="M12 2l8 3v6c0 5-3.5 9.5-8 11-4.5-1.5-8-6-8-11V5z"/><path d="M9 12l2 2 4-4"/>',
		'return'=> '<path d="M4 10h13a5 5 0 010 10h-6"/><path d="M8 6L4 10l4 4"/>',
		'check' => '<path d="M4 12.5l5 5L20 6.5"/>',
		'quote' => '<path d="M9 6c-3 1-5 3.5-5 7v5h6v-6H6.5C6.5 9.5 8 8 10 7.5zM20 6c-3 1-5 3.5-5 7v5h6v-6h-3.5c0-2.5 1.5-4 3.5-4.5z"/>',
		'star'  => '<path d="M12 3l2.7 5.6 6.1.8-4.5 4.3 1.1 6-5.4-2.9-5.4 2.9 1.1-6L3.2 9.4l6.1-.8z"/>',
		'facebook' => '<path d="M14 8h2.5V4.8H14c-2.2 0-3.6 1.5-3.6 3.8V11H8v3.2h2.4V21h3.2v-6.8h2.6l.4-3.2h-3v-1.7c0-.9.2-1.3 1.4-1.3z" fill="currentColor" stroke="none"/>',
		'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="3.8"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/>',
		'x'      => '<path d="M4 4l16 16M20 4L4 20"/>',
	);
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * Site logo: custom logo if set, else the inline wordmark.
 */
function inkwell_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
			<path d="M10 6h22a4 4 0 014 4v30H14a4 4 0 01-4-4z" fill="#b4532a" opacity="0.16"/>
			<path d="M10 6h22a4 4 0 014 4v30H14a4 4 0 01-4-4z" stroke="#b4532a" stroke-width="2.4" stroke-linejoin="round"/>
			<path d="M10 40a4 4 0 014-4h26" stroke="#b4532a" stroke-width="2.4" stroke-linecap="round"/>
			<path d="M18 14h12M18 20h12M18 26h7" stroke="#23272f" stroke-width="2.4" stroke-linecap="round"/>
		</svg>
		<span class="site-title">
			<?php bloginfo( 'name' ); ?>
			<small><?php echo esc_html( get_bloginfo( 'description' ) ); ?></small>
		</span>
	</a>
	<?php
}

/**
 * Header account link (WooCommerce aware).
 */
function inkwell_account_link() {
	$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	if ( ! $url ) {
		$url = wp_login_url();
	}
	printf(
		'<a class="header-action" href="%s" aria-label="%s">%s</a>',
		esc_url( $url ),
		esc_attr__( 'My account', 'inkwell' ),
		inkwell_icon( 'user' ) // phpcs:ignore WordPress.Security.EscapeOutput
	);
}

/**
 * Header cart: icon, count and total, updated via WC fragments.
 */
function inkwell_cart_link() {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$total = WC()->cart ? WC()->cart->get_cart_subtotal() : '';

	printf(
		'<a class="header-action" href="%s" aria-label="%s">%s<span class="cart-count inkwell-cart-count">%d</span></a>',
		esc_url( wc_get_cart_url() ),
		esc_attr__( 'View cart', 'inkwell' ),
		inkwell_icon( 'cart' ), // phpcs:ignore WordPress.Security.EscapeOutput
		(int) $count
	);
}

/**
 * Mini "cart total" chip shown in the mobile menu.
 */
function inkwell_cart_total() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$count = WC()->cart->get_cart_contents_count();
	if ( 0 === $count ) {
		return;
	}
	echo '<a class="button" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html(
		/* translators: %d: number of items in cart. */
		sprintf( _n( 'Cart — %d item', 'Cart — %d items', $count, 'inkwell' ), $count )
	) . '</a>';
}

/**
 * Section header with eyebrow + title + optional link.
 *
 * @param string $eyebrow Small uppercase label.
 * @param string $title   Section title.
 * @param string $link    Optional "view all" URL.
 * @param string $link_label Optional link label.
 */
function inkwell_section_header( $eyebrow, $title, $link = '', $link_label = '' ) {
	if ( ! $link_label ) {
		$link_label = __( 'View all', 'inkwell' );
	}
	echo '<div class="section-head" data-reveal>';
	if ( $eyebrow ) {
		echo '<span class="eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	echo '<h2>' . esc_html( $title ) . '</h2>';
	if ( $link ) {
		echo '<a class="section-link" href="' . esc_url( $link ) . '">' . esc_html( $link_label ) . ' ' . inkwell_icon( 'arrow' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}

/**
 * Blog pagination.
 */
function inkwell_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
		)
	);
}

/**
 * Post meta line (date, categories).
 */
function inkwell_posted_meta() {
	echo '<div class="entry-meta">';
	echo '<span class="posted-on"><time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time></span>';
	if ( has_category() ) {
		echo '<span class="cat-links">' . get_the_category_list( ' · ' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}

/**
 * Book cover image for shop cards.
 *
 * @param int $size Image size.
 */
function inkwell_product_cover( $size = 'inkwell-card' ) {
	global $product;
	if ( $product && $product->get_image_id() ) {
		echo $product->get_image( $size, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		echo '<span class="card-placeholder">' . inkwell_icon( 'book' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Author line for a product ("by Jane Austen").
 *
 * @param string $sep Separator between authors.
 */
function inkwell_product_authors( $sep = ', ' ) {
	global $product;
	// Use the product object when looping out of context (front-page rows).
	$product_id = ( $product instanceof WC_Product ) ? $product->get_id() : get_the_ID();
	$terms      = get_the_terms( $product_id, 'book_author' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	$links = array();
	foreach ( $terms as $term ) {
		$links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
	}
	echo '<span class="product-authors">' . esc_html__( 'by', 'inkwell' ) . ' ' . implode( $sep, $links ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Shorthand theme mod getter with default.
 *
 * @param string $key     Theme mod key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function inkwell_mod( $key, $default = '' ) {
	return get_theme_mod( $key, $default );
}

/**
 * Menu fallback when no menu is assigned.
 */
function inkwell_menu_fallback() {
	echo '<ul id="primary-menu" class="menu">';
	wp_list_pages(
		array(
			'title_li' => '',
			'number'   => 6,
			'depth'    => 1,
		)
	);
	echo '</ul>';
}

/* ------------------------------------------------------------------ *
 * Product rows (front page sections)
 * ------------------------------------------------------------------ */

/**
 * Render a product grid (used by bestsellers / new arrivals rows).
 *
 * @param array $products Array of WC_Product.
 * @param int   $limit    Max items.
 * @param bool  $ranked   Show rank badges (bestsellers).
 */
function inkwell_render_products_row( $products, $limit = 8, $ranked = false ) {
	$products = array_slice( $products, 0, $limit );
	if ( empty( $products ) ) {
		return;
	}

	echo '<div class="products-row">';

	$old_post = $GLOBALS['post'] ?? null;
	$old      = $GLOBALS['product'] ?? null;
	if ( $ranked ) {
		$GLOBALS['inkwell_loop_rank'] = 1;
	}
	foreach ( $products as $product ) {
		/*
		 * Loop templates use get_the_title()/get_permalink()/the_title(),
		 * which read the global $post — so set up full post data manually.
		 * (wc_setup_product_data() alone only populates $GLOBALS['product'].)
		 */
		$GLOBALS['post'] = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $GLOBALS['post'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wc_get_template_part( 'content', 'product' );
	}
	if ( $old_post ) {
		$GLOBALS['post'] = $old_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $old_post );
	} else {
		wp_reset_postdata();
	}
	$GLOBALS['product'] = $old; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	unset( $GLOBALS['inkwell_loop_rank'] );
	woocommerce_reset_loop();

	echo '</div>';
}

/**
 * Bestsellers: featured products, falling back to newest.
 *
 * @return array
 */
function inkwell_get_bestsellers() {
	$products = wc_get_products(
		array(
			'status'   => 'publish',
			'featured' => true,
			'limit'    => 8,
			'orderby'  => 'date',
			'order'    => 'DESC',
		)
	);
	if ( empty( $products ) ) {
		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 8,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);
	}
	return $products;
}

/**
 * New arrivals.
 *
 * @return array
 */
function inkwell_get_new_arrivals() {
	return wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 8,
			'orderby' => 'date',
			'order'   => 'DESC',
		)
	);
}
