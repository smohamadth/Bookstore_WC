<?php
/**
 * WooCommerce integration: hooks, badges, tabs, author box, fragments.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shop grid configuration.
 */
function inkwell_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'inkwell_loop_columns' );

function inkwell_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'inkwell_products_per_page' );

function inkwell_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'inkwell_related_args' );

function inkwell_upsells_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_upsells_products_args', 'inkwell_upsells_args' );

/**
 * Percentage-based sale badge: "−25%".
 *
 * @param string $html    Default HTML.
 * @param object $post    Post object.
 * @param object $product Product object.
 * @return string
 */
function inkwell_sale_flash( $html, $post, $product ) {
	$badge = __( 'Sale', 'inkwell' );

	if ( $product->is_type( 'simple' ) && $product->get_regular_price() > 0 ) {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $sale > 0 && $regular > $sale ) {
			$badge = '−' . round( ( $regular - $sale ) / $regular * 100 ) . '%';
		}
	}

	return '<span class="onsale">' . esc_html( $badge ) . '</span>';
}
add_filter( 'woocommerce_sale_flash', 'inkwell_sale_flash', 10, 3 );

/**
 * Cleaner breadcrumbs.
 */
function inkwell_breadcrumb_defaults( $defaults ) {
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'inkwell' ) . '">';
	$defaults['wrap_after']  = '</nav>';
	$defaults['delimiter']   = ' <span class="crumb-sep" aria-hidden="true">›</span> ';
	$defaults['home']        = __( 'Home', 'inkwell' );
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'inkwell_breadcrumb_defaults' );

/**
 * Hide the default shop page title (we render our own page header).
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );

/**
 * Product tabs: drop attributes, add Book Details.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function inkwell_product_tabs( $tabs ) {
	unset( $tabs['additional_information'] );

	if ( inkwell_get_book_details( get_the_ID() ) ) {
		$tabs['book_details'] = array(
			'title'    => __( 'Book Details', 'inkwell' ),
			'priority' => 25,
			'callback' => 'inkwell_book_details_tab',
		);
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'inkwell_product_tabs' );

/**
 * Book Details tab content.
 *
 * @param string $key Tab key.
 */
function inkwell_book_details_tab( $key ) {
	$details = inkwell_get_book_details( get_the_ID() );
	if ( ! $details ) {
		return;
	}
	echo '<h2 class="tab-title">' . esc_html__( 'Book Details', 'inkwell' ) . '</h2>';
	echo '<table class="book-details-table">';
	foreach ( $details as $label => $value ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
	}
	echo '</table>';
}

/**
 * "In stock" chip in the summary (all demo books ship from stock).
 */
function inkwell_stock_chip() {
	global $product;
	if ( ! $product || ! $product->is_in_stock() ) {
		return;
	}
	$message = inkwell_mod( 'inkwell_fulfillment_message', '' );
	if ( $message ) {
		echo '<p class="stock in-stock">' . esc_html( $message ) . '</p>';
	}
}
add_action( 'woocommerce_single_product_summary', 'inkwell_stock_chip', 15 );

/**
 * Trust chips under the product meta.
 */
function inkwell_meta_trust_chips() {
	$items = array_filter(
		array(
			'truck'  => inkwell_mod( 'inkwell_shipping_message', '' ),
			'return' => inkwell_mod( 'inkwell_returns_message', '' ),
		)
	);
	if ( ! $items ) {
		return;
	}
	echo '<div class="meta-chips">';
	foreach ( $items as $icon => $message ) {
		echo '<span class="chip">' . inkwell_icon( $icon ) . esc_html( $message ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}
add_action( 'woocommerce_product_meta_end', 'inkwell_meta_trust_chips' );

/**
 * Author box under the single product summary, with "more by this author".
 */
function inkwell_author_box() {
	$terms = get_the_terms( get_the_ID(), 'book_author' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}

	echo '<section class="author-box" data-reveal>';
	foreach ( $terms as $term ) {
		$bio = term_description( $term );
		$url = get_term_link( $term );

		echo '<div class="author-box-inner">';
		echo '<div class="author-monogram">' . esc_html( inkwell_first_character( $term->name ) ) . '</div>';
		echo '<div class="author-box-body">';
		echo '<span class="eyebrow">' . esc_html__( 'About the author', 'inkwell' ) . '</span>';
		echo '<h3 class="author-name"><a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a></h3>';
		if ( $bio ) {
			echo '<div class="author-bio">' . wp_kses_post( wpautop( $bio ) ) . '</div>';
		}
		echo '<a class="author-books-link" href="' . esc_url( $url ) . '">' . esc_html__( 'Browse all books by this author', 'inkwell' ) . ' →</a>';

		// More by this author (mini cover strip).
		$more = wc_get_products(
			array(
				'status'    => 'publish',
				'limit'     => 4,
				'exclude'   => array( get_the_ID() ),
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'book_author',
						'field'    => 'term_id',
						'terms'    => array( $term->term_id ),
					),
				),
			)
		);
		if ( $more ) {
			echo '<div class="author-more">';
			echo '<div class="more-label">' . esc_html__( 'More by this author', 'inkwell' ) . '</div>';
			echo '<div class="more-grid">';
			foreach ( $more as $m ) {
				$img = $m->get_image( 'thumbnail', array( 'loading' => 'lazy' ) );
				if ( ! $img ) {
					continue;
				}
				echo '<a class="more-item" href="' . esc_url( get_permalink( $m->get_id() ) ) . '" title="' . esc_attr( $m->get_name() ) . '">' . $img . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</div></div>';
		}

		echo '</div></div>';
	}
	echo '</section>';
}
add_action( 'woocommerce_after_single_product_summary', 'inkwell_author_box', 30 );

/**
 * Cart fragments: live header count & mobile total.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function inkwell_cart_fragments( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

	$fragments['.inkwell-cart-count'] = '<span class="cart-count inkwell-cart-count">' . (int) $count . '</span>';

	$fragments['.inkwell-mobile-total'] = inkwell_mobile_cart_link_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'inkwell_cart_fragments' );

/**
 * Fallback cover for products without an image (bundled asset).
 *
 * @param string $image_html Default HTML.
 * @return string
 */
function inkwell_placeholder_img( $image_html ) {
	$src = get_template_directory_uri() . '/assets/cover-fallback.png';
	return '<img src="' . esc_url( $src ) . '" class="card-placeholder-img" alt="' . esc_attr__( 'Cover coming soon', 'inkwell' ) . '" width="600" height="900" loading="lazy" />';
}
add_filter( 'woocommerce_placeholder_img', 'inkwell_placeholder_img' );

/**
 * Trust strip on the cart page.
 */
function inkwell_cart_trust() {
	$items = array_filter(
		array(
			'truck'  => inkwell_mod( 'inkwell_shipping_message', '' ),
			'check'  => inkwell_mod( 'inkwell_fulfillment_message', '' ),
			'return' => inkwell_mod( 'inkwell_returns_message', '' ),
		)
	);
	if ( ! $items ) {
		return;
	}
	echo '<div class="cart-trust">';
	foreach ( $items as $icon => $message ) {
		echo '<span>' . inkwell_icon( $icon ) . esc_html( $message ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}
add_action( 'woocommerce_before_cart', 'inkwell_cart_trust' );

/**
 * Trust note on checkout.
 */
function inkwell_checkout_trust() {
	$returns = inkwell_mod( 'inkwell_returns_message', '' );
	if ( $returns ) {
		echo '<p class="checkout-note">' . inkwell_icon( 'return' ) . esc_html( $returns ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
add_action( 'woocommerce_before_checkout_form', 'inkwell_checkout_trust', 5 );

/**
 * Friendly empty-state notice for product grids.
 */
function inkwell_no_products_found() {
	wc_print_notice( esc_html__( 'No books found. Try widening your filters or search again.', 'inkwell' ), 'notice' );
}
remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
add_action( 'woocommerce_no_products_found', 'inkwell_no_products_found', 10 );

/* ------------------------------------------------------------------ *
 * Layout strategy: we take full control of the WC page skeleton.
 * Default wrappers / sidebar / breadcrumb are removed; our template
 * overrides (archive-product, taxonomy-*, single-product) render them.
 * Loop links are rendered manually so the card can host the quick-add
 * overlay without nested anchors.
 * ------------------------------------------------------------------ */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// The card template renders the thumbnail inside the link itself —
// drop WooCommerce's bare (unlinked) copy to avoid duplicate images.
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

// Category cards provide their own styled link. Keep the hooks available to
// extensions, but remove WooCommerce's wrappers to avoid nested anchors.
remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );

/**
 * Use the tall book-cover crop on loop cards.
 *
 * @return string
 */
function inkwell_archive_thumbnail_size() {
	return 'inkwell-card';
}
add_filter( 'single_product_archive_thumbnail_size', 'inkwell_archive_thumbnail_size' );

/**
 * Shared layout for shop / genre / author-adjacent archives.
 * (Called from archive-product.php, taxonomy-product-cat.php,
 * taxonomy-product-attribute.php.)
 */
function inkwell_shop_archive_layout() {
	$is_tax    = is_product_taxonomy();
	$term      = $is_tax ? get_queried_object() : null;
	$subtitle  = '';

	if ( $term && ! is_wp_error( $term ) ) {
		$title    = $term->name;
		$subtitle = term_description( $term );
	} else {
		$title = woocommerce_page_title( false );
	}

	$shop_url = wc_get_page_permalink( 'shop' );
	?>
	<div class="container">
		<div class="two-col shop-layout">
			<div class="shop-main">

				<header class="page-header">
					<?php if ( $is_tax && $shop_url ) : ?>
						<a class="eyebrow" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop', 'inkwell' ); ?></a>
					<?php else : ?>
						<span class="eyebrow"><?php esc_html_e( 'Bookshop', 'inkwell' ); ?></span>
					<?php endif; ?>
					<h1><?php echo esc_html( $title ); ?></h1>
					<?php if ( $subtitle ) : ?>
						<div class="page-sub"><?php echo wp_kses_post( wpautop( $subtitle ) ); ?></div>
					<?php endif; ?>
				</header>

				<?php
				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 *
				 * WooCommerce floats the result count and ordering control in its
				 * legacy stylesheet. Keep them in a flex formatting context so those
				 * floats cannot interfere with the product grid below.
				 */
				?>
				<div class="woocommerce-before-shop-loop">
					<?php do_action( 'woocommerce_before_shop_loop' ); ?>
				</div>
				<?php

				if ( woocommerce_product_loop() ) {
					woocommerce_product_loop_start();

					if ( wc_get_loop_prop( 'total' ) ) {
						while ( have_posts() ) {
							the_post();
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						}
					}

					woocommerce_product_loop_end();

					/**
					 * Hook: woocommerce_after_shop_loop.
					 *
					 * @hooked woocommerce_pagination - 10
					 */
					do_action( 'woocommerce_after_shop_loop' );
				} else {
					/**
					 * Hook: woocommerce_no_products_found.
					 */
					do_action( 'woocommerce_no_products_found' );
				}
				?>

			</div>

			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php
}
