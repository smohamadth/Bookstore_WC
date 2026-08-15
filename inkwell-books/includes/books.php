<?php
/**
 * Book domain: `book_author` taxonomy + "Book Details" product meta.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the book author taxonomy (non-hierarchical, tag-style).
 */
function inkwell_register_book_author() {
	$labels = array(
		'name'          => __( 'Book Authors', 'inkwell' ),
		'singular_name' => __( 'Book Author', 'inkwell' ),
		'search_items'  => __( 'Search Authors', 'inkwell' ),
		'all_items'     => __( 'All Authors', 'inkwell' ),
		'edit_item'     => __( 'Edit Author', 'inkwell' ),
		'update_item'   => __( 'Update Author', 'inkwell' ),
		'add_new_item'  => __( 'Add New Author', 'inkwell' ),
		'new_item_name' => __( 'New Author Name', 'inkwell' ),
		'menu_name'     => __( 'Authors', 'inkwell' ),
	);

	register_taxonomy(
		'book_author',
		array( 'product' ),
		array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'meta_box_cb'       => 'post_tags_meta_box',
			'rewrite'           => array( 'slug' => 'book-author', 'with_front' => false ),
		)
	);
}
add_action( 'init', 'inkwell_register_book_author' );

/**
 * Book Details meta keys.
 *
 * @return array
 */
function inkwell_book_meta_fields() {
	return array(
		'_inkwell_isbn'     => array(
			'label' => __( 'ISBN-13', 'inkwell' ),
			'type'  => 'text',
			'placeholder' => '978-0-00-000000-0',
		),
		'_inkwell_publisher' => array(
			'label' => __( 'Publisher', 'inkwell' ),
			'type'  => 'text',
		),
		'_inkwell_year'     => array(
			'label' => __( 'Publication Year', 'inkwell' ),
			'type'  => 'number',
		),
		'_inkwell_pages'    => array(
			'label' => __( 'Pages', 'inkwell' ),
			'type'  => 'number',
		),
		'_inkwell_format'   => array(
			'label'   => __( 'Format', 'inkwell' ),
			'type'    => 'select',
			'options' => array(
				''           => __( '— Select —', 'inkwell' ),
				'paperback'  => __( 'Paperback', 'inkwell' ),
				'hardcover'  => __( 'Hardcover', 'inkwell' ),
				'ebook'      => __( 'Ebook', 'inkwell' ),
				'audiobook'  => __( 'Audiobook', 'inkwell' ),
			),
		),
		'_inkwell_language' => array(
			'label' => __( 'Language', 'inkwell' ),
			'type'  => 'text',
		),
	);
}

/**
 * Add "Book Details" tab to the product data box.
 *
 * @param array $tabs WooCommerce product data tabs.
 * @return array
 */
function inkwell_product_data_tab( $tabs ) {
	$tabs['inkwell_book'] = array(
		'label'    => __( 'Book Details', 'inkwell' ),
		'target'   => 'inkwell_book_details',
		'class'    => array( 'show_if_simple', 'show_if_variable' ),
		'priority' => 25,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'inkwell_product_data_tab' );

/**
 * Render the Book Details panel.
 */
function inkwell_book_details_panel() {
	global $post;
	?>
	<div id="inkwell_book_details" class="panel woocommerce_options_panel">
		<div class="options_group">
			<?php
			foreach ( inkwell_book_meta_fields() as $key => $field ) {
				$value = get_post_meta( $post->ID, $key, true );
				if ( 'select' === $field['type'] ) {
					woocommerce_wp_select(
						array(
							'id'      => $key,
							'label'   => $field['label'],
							'value'   => $value,
							'options' => $field['options'],
						)
					);
				} else {
					woocommerce_wp_text_input(
						array(
							'id'          => $key,
							'label'       => $field['label'],
							'value'       => $value,
							'type'        => $field['type'],
							'placeholder' => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						)
					);
				}
			}
			?>
		</div>
		<p class="description" style="padding:0 12px 12px;">
			<?php esc_html_e( 'Bibliographic details shown on the product page and in the Book Details tab. The Author field is managed from the Authors box.', 'inkwell' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'inkwell_book_details_panel' );

/**
 * Save Book Details meta (HPOS-compatible, all product types).
 *
 * @param WC_Product $product Product object.
 */
function inkwell_save_book_details( $product ) {
	$fields = inkwell_book_meta_fields();
	foreach ( $fields as $key => $field ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce handled by WC core.
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'number' === $field['type'] ) {
			$value = absint( $value );
		} elseif ( 'select' === $field['type'] ) {
			$value = sanitize_key( $value );
		} else {
			$value = sanitize_text_field( $value );
		}
		if ( '' === $value ) {
			$product->delete_meta_data( $key );
		} else {
			$product->update_meta_data( $key, $value );
		}
	}
	$product->save_meta_data();
}
add_action( 'woocommerce_admin_process_product_object', 'inkwell_save_book_details' );

/**
 * Add ISBN + author columns to the product list in admin.
 *
 * @param array $columns Columns.
 * @return array
 */
function inkwell_admin_product_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'name' === $key ) {
			$new['inkwell_author'] = __( 'Author', 'inkwell' );
			$new['inkwell_isbn']   = __( 'ISBN', 'inkwell' );
		}
	}
	return $new;
}
add_filter( 'manage_product_posts_columns', 'inkwell_admin_product_columns' );

/**
 * Render admin product columns.
 *
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 */
function inkwell_admin_product_column_content( $column, $post_id ) {
	if ( 'inkwell_author' === $column ) {
		$terms = get_the_terms( $post_id, 'book_author' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$names = array();
			foreach ( $terms as $term ) {
				$names[] = $term->name;
			}
			echo esc_html( implode( ', ', $names ) );
		} else {
			echo '—';
		}
	}
	if ( 'inkwell_isbn' === $column ) {
		$isbn = get_post_meta( $post_id, '_inkwell_isbn', true );
		echo $isbn ? esc_html( $isbn ) : '—';
	}
}
add_action( 'manage_product_posts_custom_column', 'inkwell_admin_product_column_content', 10, 2 );

/**
 * Include author names in shop keyword search.
 *
 * @param string    $search SQL search clause.
 * @param WP_Query  $query  The query.
 * @return string
 */
function inkwell_search_by_author( $search, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $search;
	}
	if ( 'product' !== $query->get( 'post_type' ) || ! $query->get( 's' ) ) {
		return $search;
	}

	$term = $wpdb->esc_like( $query->get( 's' ) );
	$like = '%' . $term . '%';

	$author_clause = $wpdb->prepare(
		"EXISTS (
			SELECT 1 FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
			WHERE tt.taxonomy = 'book_author'
			AND tr.object_id = {$wpdb->posts}.ID
			AND t.name LIKE %s
		)",
		$like
	);

	/*
	 * WP_Query returns a search fragment beginning with AND and, for logged-out
	 * visitors, ending with a password condition. Put the author match inside
	 * the same parenthesized search expression so the OR cannot escape later
	 * post-type or post-status constraints.
	 */
	$password_clause = "AND ({$wpdb->posts}.post_password = '')";
	$expression      = trim( $search );
	$password        = '';
	if ( strlen( $expression ) >= strlen( $password_clause ) && $password_clause === substr( $expression, -strlen( $password_clause ) ) ) {
		$expression = trim( substr( $expression, 0, -strlen( $password_clause ) ) );
		$password   = ' ' . $password_clause;
	}
	$expression = preg_replace( '/^AND\s+/i', '', $expression );

	if ( ! $expression ) {
		return $search;
	}

	return ' AND ( (' . $expression . ') OR ' . $author_clause . ' )' . $password . ' ';
}
add_filter( 'posts_search', 'inkwell_search_by_author', 10, 2 );

/**
 * Collected book details for a product (label → value pairs).
 *
 * @param int|WC_Product $product Product.
 * @return array
 */
function inkwell_get_book_details( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return array();
	}

	$details = array();
	foreach ( inkwell_book_meta_fields() as $key => $field ) {
		$value = $product->get_meta( $key );
		if ( '' === $value ) {
			continue;
		}
		if ( 'select' === $field['type'] && isset( $field['options'][ $value ] ) ) {
			$value = $field['options'][ $value ];
		}
		$details[ $field['label'] ] = $value;
	}
	return $details;
}
