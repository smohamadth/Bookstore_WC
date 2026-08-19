<?php
/**
 * One-click demo import — runs entirely from wp-admin, no WP-CLI needed.
 *
 * Adds: Appearance → "Import Demo Content", plus a gentle admin notice
 * when the shop has no products. Bundled demo data lives in
 * inkwell/demo/ (books.json + covers/). Safe to re-run: idempotent.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Demo data root (theme-bundled).
 *
 * @return string
 */
function inkwell_demo_dir() {
	return get_template_directory() . '/demo';
}

/**
 * Register the admin page.
 */
function inkwell_demo_import_menu() {
	add_theme_page(
		__( 'Import Demo Content', 'inkwell' ),
		__( 'Import Demo Content', 'inkwell' ),
		'manage_options',
		'inkwell-demo-import',
		'inkwell_demo_import_page'
	);
}
add_action( 'admin_menu', 'inkwell_demo_import_menu' );

/**
 * "Your shop is empty" notice.
 */
function inkwell_demo_import_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_inkwell-demo-import' === $screen->id ) {
		return;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Compatibility nudge for very old WooCommerce (theme needs WC 8+ APIs).
	if ( version_compare( WC()->version, '8.0', '<' ) ) {
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Inkwell: WooCommerce is out of date.', 'inkwell' ); ?></strong>
				<?php
				printf(
					/* translators: %s: installed WC version. */
					esc_html__( 'You are running WooCommerce %s — the theme requires 8.0 or newer. Please update WooCommerce for the shop and product pages to work correctly.', 'inkwell' ),
					esc_html( WC()->version )
				);
				?>
			</p>
		</div>
		<?php
		return;
	}

	$legacy_product_id = (int) wc_get_product_id_by_sku( 'pride-prejudice' );
	if ( $legacy_product_id > 0 && '9780141439518' === get_post_meta( $legacy_product_id, '_inkwell_isbn', true ) ) {
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Inkwell: retired demo catalog detected.', 'inkwell' ); ?></strong>
				<?php esc_html_e( 'Run the importer again to safely replace only the old demo products with fictional Kurdish books.', 'inkwell' ); ?>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'themes.php?page=inkwell-demo-import' ) ); ?>"><?php esc_html_e( 'Replace demo catalog', 'inkwell' ); ?></a>
			</p>
		</div>
		<?php
		return;
	}

	if ( 0 < wp_count_posts( 'product' )->publish ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Inkwell: your shop is empty.', 'inkwell' ); ?></strong>
			<?php esc_html_e( 'Seed it with the bundled fictional Kurdish catalog (33 books, 7 genres, 27 authors, reviews, menus and pages) in one click.', 'inkwell' ); ?>
			<a class="button button-primary" style="margin-left: 0.6em; vertical-align: middle;" href="<?php echo esc_url( admin_url( 'themes.php?page=inkwell-demo-import' ) ); ?>">
				<?php esc_html_e( 'Import demo content', 'inkwell' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'inkwell_demo_import_notice' );

/**
 * Admin page + import trigger.
 */
function inkwell_demo_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		?>
		<div class="wrap"><h1><?php esc_html_e( 'Inkwell — Import Demo Content', 'inkwell' ); ?></h1>
			<div class="notice notice-error"><p><?php esc_html_e( 'WooCommerce must be installed and active before demo content can be imported.', 'inkwell' ); ?></p></div>
		</div>
		<?php
		return;
	}

	$result = null;
	if ( isset( $_POST['inkwell_demo_import'] ) && check_admin_referer( 'inkwell_demo_import', 'inkwell_demo_nonce' ) ) {
		$apply_site_setup = isset( $_POST['inkwell_demo_apply_site_setup'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['inkwell_demo_apply_site_setup'] ) );
		$result           = inkwell_demo_import_catalog( $apply_site_setup );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Inkwell — Import Demo Content', 'inkwell' ); ?></h1>

		<?php if ( is_wp_error( $result ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
		<?php elseif ( $result ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Demo content imported successfully!', 'inkwell' ); ?></strong></p>
				<ul style="list-style: disc; padding-left: 1.4em; margin: 0.4em 0;">
					<?php foreach ( $result as $key => $label ) : ?>
						<li><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width: 720px; padding: 1.4em 1.6em;">
			<p style="font-size: 1.02em; margin-top: 0;">
				<?php esc_html_e( 'This imports the bundled sample store into your site — perfect for testing the theme before adding your own books:', 'inkwell' ); ?>
			</p>
			<ul style="list-style: disc; padding-left: 1.4em;">
				<li><strong>33 books</strong> <?php esc_html_e( 'fictional Sorani books with original covers, demo metadata, sale prices and featured flags', 'inkwell' ); ?></li>
				<li><strong>7 genres</strong> <?php esc_html_e( '(Kurdish fiction, science fiction and fantasy, mystery, non-fiction, history, children’s books and poetry)', 'inkwell' ); ?></li>
				<li><strong>27 fictional Kurdish authors</strong> <?php esc_html_e( 'with bios → author archive pages', 'inkwell' ); ?></li>
				<li><strong>14 reviews</strong>, <?php esc_html_e( '3 journal posts, menus, widgets, pages &amp; default settings', 'inkwell' ); ?></li>
			</ul>
			<p style="color: #666;">
				<?php esc_html_e( 'The catalog import is safe to run again: marked demo products and media are reused, while an unrelated product that happens to share a demo SKU is skipped.', 'inkwell' ); ?>
			</p>

			<?php if ( ! $result || is_wp_error( $result ) ) : ?>
				<form method="post" style="margin-top: 0.6em;">
					<?php wp_nonce_field( 'inkwell_demo_import', 'inkwell_demo_nonce' ); ?>
					<p>
						<label>
							<input type="checkbox" name="inkwell_demo_apply_site_setup" value="1" />
							<strong><?php esc_html_e( 'Also apply the complete demo-site setup', 'inkwell' ); ?></strong>
						</label><br />
						<span class="description"><?php esc_html_e( 'Optional: assigns a demo menu and sidebars, sets EUR currency, and selects the demo front page. Leave unchecked on an existing site.', 'inkwell' ); ?></span>
					</p>
					<button type="submit" name="inkwell_demo_import" class="button button-primary button-hero">
						<?php esc_html_e( 'Import demo content now', 'inkwell' ); ?>
					</button>
				</form>
			<?php else : ?>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'View the shop', 'inkwell' ); ?>
					</a>
					<a class="button" href="<?php echo esc_url( admin_url( 'themes.php?page=inkwell-demo-import' ) ); ?>">
						<?php esc_html_e( 'Import again', 'inkwell' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Media helper: attach a bundled image to the library.
 *
 * @param string $file       Absolute path.
 * @param int    $post_id    Attach to post.
 * @param string $source_key Stable demo asset key used for safe reruns.
 * @return int|WP_Error
 */
function inkwell_demo_import_image( $file, $post_id = 0, $source_key = '' ) {
	if ( $source_key ) {
		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_inkwell_demo_asset', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $source_key, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( $existing && get_attached_file( $existing[0] ) && file_exists( get_attached_file( $existing[0] ) ) ) {
			return (int) $existing[0];
		}
	}

	if ( ! is_readable( $file ) ) {
		return new WP_Error( 'missing_image', 'Could not read ' . $file );
	}
	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return new WP_Error( 'uploads', $uploads['error'] );
	}
	$filename = wp_unique_filename( $uploads['path'], basename( $file ) );
	$dest     = trailingslashit( $uploads['path'] ) . $filename;
	if ( ! copy( $file, $dest ) ) {
		return new WP_Error( 'copy', 'Could not copy ' . $file );
	}

	$filetype   = wp_check_filetype( basename( $dest ) );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $dest ) ),
		'post_status'    => 'inherit',
	);
	$attach_id  = wp_insert_attachment( $attachment, $dest, $post_id );
	if ( is_wp_error( $attach_id ) ) {
		return $attach_id;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$meta = wp_generate_attachment_metadata( $attach_id, $dest );
	wp_update_attachment_metadata( $attach_id, $meta );
	if ( $source_key ) {
		update_post_meta( $attach_id, '_inkwell_demo_asset', sanitize_key( $source_key ) );
	}
	return (int) $attach_id;
}

/**
 * Page helper: publish the page if it exists, else create.
 *
 * @param string $slug    Slug.
 * @param string $title   Title.
 * @param string $content Content.
 * @return int
 */
function inkwell_demo_ensure_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		if ( 'publish' !== $existing->post_status ) {
			wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
		}
		return (int) $existing->ID;
	}
	return (int) wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
}

/**
 * Ensure WooCommerce has a functional My Account page.
 * Existing non-empty page content is never replaced.
 *
 * @return int My Account page ID, or zero on failure.
 */
function inkwell_demo_ensure_myaccount_page() {
	$page_id = (int) wc_get_page_id( 'myaccount' );
	if ( $page_id <= 0 ) {
		$page_id = inkwell_demo_ensure_page( 'my-account', __( 'My Account', 'inkwell' ), '' );
		if ( $page_id > 0 ) {
			update_option( 'woocommerce_myaccount_page_id', $page_id );
		}
	}
	if ( $page_id > 0 && '' === trim( (string) get_post_field( 'post_content', $page_id ) ) ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->',
			)
		);
	}
	return max( 0, $page_id );
}

/**
 * Detect catalogs made by either the admin importer or its legacy WP-CLI tool.
 *
 * @return bool
 */
function inkwell_demo_catalog_detected() {
	if ( get_option( 'inkwell_demo_imported', false ) ) {
		return true;
	}
	$signatures = array(
		'pride-prejudice' => '9780141439518',
		'dune'            => '9780441172719',
		'sapiens'         => '9780062316097',
	);
	$matches = 0;
	foreach ( $signatures as $sku => $isbn ) {
		$product_id = (int) wc_get_product_id_by_sku( $sku );
		if ( $product_id > 0 && $isbn === get_post_meta( $product_id, '_inkwell_isbn', true ) ) {
			$matches++;
		}
	}
	return $matches >= 2;
}

/**
 * Repair account registration for sites created by an earlier demo importer.
 * This is deliberately limited to recognized demo sites and runs only once.
 */
function inkwell_demo_repair_account_registration() {
	if ( ! class_exists( 'WooCommerce' ) || get_option( 'inkwell_account_repaired_205', false ) ) {
		return;
	}
	if ( ! inkwell_demo_catalog_detected() ) {
		update_option( 'inkwell_account_repaired_205', 'not-demo', false );
		return;
	}
	inkwell_demo_ensure_myaccount_page();
	update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
	update_option( 'inkwell_account_repaired_205', 1, false );
}
add_action( 'init', 'inkwell_demo_repair_account_registration', 30 );

/**
 * Create or reuse a widget instance owned by the demo importer.
 *
 * @param string $option_name Widget option name.
 * @param string $id_base     Widget ID base.
 * @param array  $settings    Widget settings.
 * @return string Widget instance ID.
 */
function inkwell_demo_ensure_widget( $option_name, $id_base, $settings ) {
	$instances = get_option( $option_name, array() );
	if ( ! is_array( $instances ) ) {
		$instances = array();
	}
	foreach ( $instances as $number => $instance ) {
		if ( is_numeric( $number ) && is_array( $instance ) && ! empty( $instance['_inkwell_demo'] ) ) {
			return $id_base . '-' . $number;
		}
	}
	$numbers = array_filter( array_keys( $instances ), 'is_numeric' );
	$number  = $numbers ? max( array_map( 'intval', $numbers ) ) + 1 : 1;
	$settings['_inkwell_demo'] = 1;
	$instances[ $number ]      = $settings;
	$instances['_multiwidget'] = 1;
	update_option( $option_name, $instances );
	return $id_base . '-' . $number;
}

/**
 * Remove only products proven to belong to the retired real-book demo catalog.
 *
 * @return int Number of removed legacy demo products.
 */
function inkwell_demo_remove_legacy_products() {
	$legacy_products = array(
		'pride-prejudice'              => '9780141439518',
		'nineteen-eighty-four'         => '9780451524935',
		'great-gatsby'                 => '9780743273565',
		'crawdads'                     => '9780735219090',
		'to-kill-a-mockingbird'        => '9780061120084',
		'catcher-in-the-rye'           => '9780316769488',
		'one-hundred-years-of-solitude'=> '9780060883287',
		'normal-people'                => '9781984822178',
		'dune'                         => '9780441172719',
		'hobbit'                       => '9780547928227',
		'name-of-the-wind'             => '9780756404741',
		'neuromancer'                  => '9780441569595',
		'left-hand-of-darkness'        => '9780441478125',
		'project-hail-mary'            => '9780593135204',
		'orient-express'               => '9780062693662',
		'da-vinci-code'                => '9780307474278',
		'dragon-tattoo'                => '9780307454546',
		'gone-girl'                    => '9780307588371',
		'silent-patient'               => '9781250301697',
		'sapiens'                      => '9780062316097',
		'brief-history-time'           => '9780553380163',
		'thinking-fast-slow'           => '9780374533557',
		'atomic-habits'                => '9780735211292',
		'diary-young-girl'             => '9780553296983',
		'long-walk-freedom'            => '9780316548182',
		'educated'                     => '9780399590504',
		'waste-land'                   => '9780141182278',
		'milk-and-honey'               => '9781449474256',
		'hungry-caterpillar'           => '9780399226908',
		'matilda'                      => '9780142410370',
		'charlottes-web'               => '9780064400558',
		'and-then-there-were-none'     => '9780062073488',
		'charlie-chocolate-factory'    => '9780142410318',
	);
	$removed = 0;
	foreach ( $legacy_products as $sku => $isbn ) {
		$product_id = (int) wc_get_product_id_by_sku( $sku );
		if ( $product_id <= 0 ) {
			continue;
		}
		$is_owned = get_post_meta( $product_id, '_inkwell_demo_product', true );
		$is_match = $isbn === get_post_meta( $product_id, '_inkwell_isbn', true );
		if ( $is_owned || $is_match ) {
			wp_delete_post( $product_id, true );
			$removed++;
		}
	}
	return $removed;
}

/**
 * Run the import. Returns summary lines or a WP_Error.
 *
 * @param bool $apply_site_setup Whether to apply menus, widgets and store settings.
 * @return array|WP_Error
 */
function inkwell_demo_import_catalog( $apply_site_setup = false ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return new WP_Error( 'woocommerce_required', __( 'WooCommerce must be active before importing demo content.', 'inkwell' ) );
	}

	$dir      = inkwell_demo_dir();
	$json     = is_readable( $dir . '/books.json' ) ? file_get_contents( $dir . '/books.json' ) : false;
	$data     = false !== $json ? json_decode( $json, true ) : null;
	if ( ! is_array( $data ) || 33 !== count( $data ) ) {
		return new WP_Error( 'demo_data_missing', __( 'Could not read demo data. The theme files may be incomplete.', 'inkwell' ) );
	}
	$required_fields = array( 'sku', 'title', 'author', 'author_bio', 'category', 'price', 'isbn', 'publisher', 'year', 'pages', 'format', 'language', 'featured', 'desc' );
	$valid_categories = array( 'fiction', 'science-fiction-fantasy', 'mystery-thriller', 'non-fiction', 'history-biography', 'childrens-books', 'poetry' );
	foreach ( $data as $book ) {
		if ( array_diff( $required_fields, array_keys( $book ) ) || ! in_array( $book['category'], $valid_categories, true ) ) {
			return new WP_Error( 'demo_data_invalid', __( 'The fictional Kurdish catalog is incomplete and was not imported.', 'inkwell' ) );
		}
	}

	// Only opt a fresh site into pretty permalinks when full setup was requested.
	if ( $apply_site_setup && ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	$summary = array();
	$removed_legacy = inkwell_demo_remove_legacy_products();
	if ( $removed_legacy ) {
		$summary[] = sprintf( __( '%d retired demo products replaced with fictional Kurdish books', 'inkwell' ), $removed_legacy );
	}

	/* Categories */
	$cats = array(
		'fiction'                 => array( 'چیرۆک', 'ڕۆمان و چیرۆکی کورت لە ژیان، خۆشەویستی و کۆمەڵگای کوردی.' ),
		'science-fiction-fantasy' => array( 'زانستی-خەیاڵی و فانتازیا', 'جیهانی تر، داهاتووی دوور، تەکنەلۆژیا و جادوی ناممکن.' ),
		'mystery-thriller'        => array( 'نهێنی و هەستبزوێن', 'تاوان، نهێنی، پەرۆشی و چیرۆکی ڕاکێشەر.' ),
		'non-fiction'             => array( 'زانیاری و بیرکردنەوە', 'زانست، دەروونناسی، ئابووری، ژینگە و فێربوونی کرداری.' ),
		'history-biography'       => array( 'مێژوو و ژیاننامە', 'گێڕانەوەی مێژوویی و ژیانی کەسایەتییە خەیاڵییە کوردەکان.' ),
		'childrens-books'         => array( 'کتێبی منداڵان', 'چیرۆکی وێنەدار و سەرکێشی بۆ خوێنەرە بچووکەکان.' ),
		'poetry'                  => array( 'شیعر', 'هۆنراوە و کۆمەڵە شیعری نوێ بە زمانی کوردی.' ),
	);
	$cat_ids          = array();
	$has_demo_catalog = (bool) get_option( 'inkwell_demo_imported', false ) || $removed_legacy > 0;
	foreach ( $cats as $slug => $cdata ) {
		$term        = term_exists( $slug, 'product_cat' );
		$is_new_term = false;
		if ( ! $term ) {
			$term        = wp_insert_term( $cdata[0], 'product_cat', array( 'slug' => $slug, 'description' => $cdata[1] ) );
			$is_new_term = true;
		}
		if ( is_wp_error( $term ) ) {
			return $term;
		}
		$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		if ( $is_new_term || $has_demo_catalog || get_term_meta( $term_id, '_inkwell_demo_term', true ) ) {
			wp_update_term( $term_id, 'product_cat', array( 'name' => $cdata[0], 'description' => $cdata[1] ) );
			update_term_meta( $term_id, '_inkwell_demo_term', 1 );
		}
		$cat_ids[ $slug ] = $term_id;
	}
	$summary[] = sprintf( __( '%d product categories created/updated', 'inkwell' ), count( $cats ) );

	/* Fictional Kurdish authors sourced from the catalog. */
	$bios = array();
	foreach ( $data as $book ) {
		if ( ! empty( $book['author'] ) ) {
			$bios[ $book['author'] ] = isset( $book['author_bio'] ) ? $book['author_bio'] : '';
		}
	}

	// Remove only retired author terms previously marked as Inkwell demo data.
	$old_author_terms = get_terms(
		array(
			'taxonomy'   => 'book_author',
			'hide_empty' => false,
			'meta_key'   => '_inkwell_demo_term', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => 1, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( ! is_wp_error( $old_author_terms ) ) {
		foreach ( $old_author_terms as $old_author_term ) {
			if ( ! isset( $bios[ $old_author_term->name ] ) ) {
				wp_delete_term( $old_author_term->term_id, 'book_author' );
			}
		}
	}

	$author_ids = array();
	foreach ( $bios as $name => $bio ) {
		$term        = term_exists( $name, 'book_author' );
		$is_new_term = false;
		if ( ! $term ) {
			$term        = wp_insert_term( $name, 'book_author' );
			$is_new_term = true;
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		if ( is_array( $term ) ) {
			$term_id = (int) $term['term_id'];
			if ( $is_new_term || get_term_meta( $term_id, '_inkwell_demo_term', true ) ) {
				wp_update_term( $term_id, 'book_author', array( 'description' => $bio ) );
				update_term_meta( $term_id, '_inkwell_demo_term', 1 );
			}
			$author_ids[ $name ] = $term_id;
		}
	}
	$summary[] = sprintf( __( '%d fictional Kurdish authors with bios', 'inkwell' ), count( $author_ids ) );

	/* Products */
	$cover_ids        = array();
	$total            = count( $data );
	$skipped_products = 0;

	foreach ( $data as $index => $book ) {
		$sku         = sanitize_text_field( $book['sku'] );
		$existing_id = (int) wc_get_product_id_by_sku( $sku );
		$is_demo     = $existing_id && get_post_meta( $existing_id, '_inkwell_demo_product', true );
		if ( $existing_id && ! $is_demo ) {
			$skipped_products++;
			continue;
		}
		$product = $existing_id ? wc_get_product( $existing_id ) : new WC_Product_Simple();
		if ( ! $product ) {
			$skipped_products++;
			continue;
		}

		// Set deterministic dates only on newly created demo products.
		if ( ! $existing_id ) {
			$product->set_date_created( wp_date( 'Y-m-d H:i:s', time() - ( $total - 1 - $index ) * 9 * DAY_IN_SECONDS ) );
		}
		$product->set_name( $book['title'] );
		$product->set_slug( sanitize_title( $sku ) );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_sku( $sku );
		$product->set_regular_price( $book['price'] );
		if ( ! empty( $book['sale'] ) ) {
			$product->set_sale_price( $book['sale'] );
		} else {
			$product->set_sale_price( '' );
		}
		$product->set_featured( (bool) $book['featured'] );
		$product->set_short_description( $book['desc'] );
		$format_labels = array(
			'paperback' => 'بەرگی نەرم',
			'hardcover' => 'بەرگی ڕەق',
			'ebook'     => 'کتێبی ئەلیکترۆنی',
			'audiobook' => 'کتێبی دەنگی',
		);
		$format_label = isset( $format_labels[ $book['format'] ] ) ? $format_labels[ $book['format'] ] : $book['format'];
		$product->set_description( $book['desc'] . "\n\nئەم وەشانە: " . $format_label . ' · ' . $book['pages'] . ' پەڕە · ' . $book['publisher'] . '، ' . $book['year'] . ' · زمان: ' . $book['language'] . ' · ISBN-13: ' . $book['isbn'] . '.' );
		$product->set_category_ids( array( $cat_ids[ $book['category'] ] ) );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );

		$product->update_meta_data( '_inkwell_isbn', $book['isbn'] );
		$product->update_meta_data( '_inkwell_publisher', $book['publisher'] );
		$product->update_meta_data( '_inkwell_year', (int) $book['year'] );
		$product->update_meta_data( '_inkwell_pages', (int) $book['pages'] );
		$product->update_meta_data( '_inkwell_format', $book['format'] );
		$product->update_meta_data( '_inkwell_language', $book['language'] );
		$product->update_meta_data( '_inkwell_demo_product', 1 );
		$product->update_meta_data( '_inkwell_demo_catalog_version', 2 );

		$product_id = $product->save();

		if ( isset( $author_ids[ $book['author'] ] ) ) {
			wp_set_object_terms( $product_id, array( $author_ids[ $book['author'] ] ), 'book_author' );
		}

		$cover = $dir . '/covers/' . $sku . '.png';
		if ( $product->get_image_id() ) {
			$cover_ids[ $sku ] = $product->get_image_id();
			update_post_meta( $product->get_image_id(), '_inkwell_demo_asset', 'cover-' . sanitize_key( $sku ) );
		} elseif ( file_exists( $cover ) ) {
			$img = inkwell_demo_import_image( $cover, $product_id, 'cover-' . $sku );
			if ( is_wp_error( $img ) ) {
				$img = inkwell_demo_import_image( get_template_directory() . '/assets/cover-fallback.png', $product_id, 'cover-fallback' );
			}
			if ( ! is_wp_error( $img ) ) {
				$cover_ids[ $sku ] = $img;
				$product->set_image_id( $img );
				$product->save();
			}
		}
	}
	$summary[] = sprintf( __( '%d demo books created or updated', 'inkwell' ), count( $data ) - $skipped_products );
	if ( $skipped_products ) {
		$summary[] = sprintf( __( '%d existing products skipped because their SKUs were not marked as demo content', 'inkwell' ), $skipped_products );
	}

	/* Category thumbnails */
	$cat_covers = array(
		'fiction'                 => 'guli-shax',
		'science-fiction-fantasy' => 'sharistani-mang',
		'mystery-thriller'        => 'nheni-qalai-kon',
		'non-fiction'             => 'hunari-xwendinawa',
		'history-biography'       => 'chiroki-sharakan',
		'childrens-books'         => 'mrishka-u-mang',
		'poetry'                  => 'boni-xak',
	);
	foreach ( $cat_covers as $slug => $sku ) {
		if ( isset( $cover_ids[ $sku ] ) ) {
			update_term_meta( $cat_ids[ $slug ], 'thumbnail_id', $cover_ids[ $sku ] );
		}
	}

	if ( $apply_site_setup ) {
		/* Pages and journal content are part of the explicitly requested site setup. */
		$home    = inkwell_demo_ensure_page( 'front', 'Home', '' );
		$about   = inkwell_demo_ensure_page( 'about', 'About Inkwell', '<h2>Books, chosen by hand</h2><p>Inkwell started as a single shelf in a small apartment and grew into the shop you see today. We are a small team of readers, and we stock the books we genuinely love — fiction, history, science, children’s stories and everything in between.</p>' );
		$contact = inkwell_demo_ensure_page( 'contact', 'Contact', '<p>We would love to hear from you — questions about an order, a recommendation request, or just to talk about what you’re reading.</p><p>Email: <a href="mailto:hello@inkwell.example">hello@inkwell.example</a></p><hr /><p><strong>Prefer email newsletters?</strong> Join the reading list:</p>[inkwell_newsletter]<h3>Leave the reading list</h3>[inkwell_newsletter_unsubscribe]' );
		$journal = inkwell_demo_ensure_page( 'journal', 'The Journal', '' );
		$privacy = inkwell_demo_ensure_page( 'privacy-policy', 'Privacy Policy', '<p>This is a demo shop. We store only what is needed to fulfil orders and you can request deletion of your account at any time.</p><p>If you join the reading list, the site stores your email address and consent time until you unsubscribe or request erasure through the site owner.</p>' );

		$shop = (int) wc_get_page_id( 'shop' );
		if ( $shop <= 0 ) {
			$shop = inkwell_demo_ensure_page( 'shop', 'Shop', '' );
			update_option( 'woocommerce_shop_page_id', $shop );
		}
		foreach ( array( 'cart', 'checkout' ) as $wc_page ) {
			if ( (int) wc_get_page_id( $wc_page ) <= 0 ) {
				$id = inkwell_demo_ensure_page( $wc_page, ucwords( $wc_page ), '' );
				update_option( 'woocommerce_' . $wc_page . '_page_id', $id );
			}
		}
		inkwell_demo_ensure_myaccount_page();

		// On explicit full setup, initialize only genuinely empty cart/checkout
		// pages. Existing block or shortcode content is always preserved.
		$cart_id     = (int) wc_get_page_id( 'cart' );
		$checkout_id = (int) wc_get_page_id( 'checkout' );
		if ( $cart_id > 0 && '' === trim( (string) get_post_field( 'post_content', $cart_id ) ) ) {
			wp_update_post( array( 'ID' => $cart_id, 'post_content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->' ) );
		}
		if ( $checkout_id > 0 && '' === trim( (string) get_post_field( 'post_content', $checkout_id ) ) ) {
			wp_update_post( array( 'ID' => $checkout_id, 'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->' ) );
		}

		$summary[] = __( 'Pages created (Home, Shop, My Account, About, Contact, Journal, Privacy)', 'inkwell' );

		/* Fictional Kurdish journal posts. */
		$legacy_post_slugs = array(
			'autumn-reading-list-7-books-for-long-evenings',
			'behind-the-shelves-how-we-pick-our-stock',
			'staff-pick-the-name-of-the-wind',
		);
		if ( $has_demo_catalog ) {
			foreach ( $legacy_post_slugs as $legacy_post_slug ) {
				$legacy_post = get_page_by_path( $legacy_post_slug, OBJECT, 'post' );
				if ( $legacy_post ) {
					wp_delete_post( $legacy_post->ID, true );
				}
			}
		}

		$post_cats = array(
			'reading-lists'       => 'لیستی خوێندنەوە',
			'behind-the-shelves'  => 'لە پشت ڕەفەکان',
			'staff-picks'         => 'هەڵبژاردەی ستاف',
		);
		foreach ( $post_cats as $slug => $name ) {
			$category = term_exists( $slug, 'category' );
			if ( ! $category ) {
				wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
			} elseif ( $has_demo_catalog ) {
				$category_id = is_array( $category ) ? (int) $category['term_id'] : (int) $category;
				wp_update_term( $category_id, 'category', array( 'name' => $name ) );
			}
		}
		$posts = array(
			array(
				'title'   => 'حەوت کتێب بۆ شەوە درێژەکان',
				'cat'     => 'reading-lists',
				'content' => '<p>کاتێک شەو درێژ دەبێت، کتێب باشترین هاوڕێیە. ئەم وەرزە ستافی ئینکوێڵ ئەم کتێبە خەیاڵییە کوردییانە پێشنیار دەکات.</p><h3>١. گوڵی شاخ — ئاوات کریم</h3><p>ڕۆمانێکی هێمن لەسەر خێزان، خاک و نهێنییە کۆنەکان.</p><h3>٢. شارستانی مانگ — هێمن شێرزاد</h3><p>خەیاڵی زانستی بە ڕەنگێکی کوردی و جیهانسازیی ورد.</p><h3>٣. نهێنیی قەڵای کۆن — بەختیار عوسمان</h3><p>چیرۆکێکی تاوانی کە مێژوو و ئێستا بە یەکەوە دەبەستێت.</p>',
			),
			array(
				'title'   => 'لە پشت ڕەفەکان: چۆن کتێب هەڵدەبژێرین',
				'cat'     => 'behind-the-shelves',
				'content' => '<p>هەر مانگێک هەر یەکێک لە ئێمە کتێبێک دەهێنێت کە خۆشی ویستووە. لەسەر زمان، چیرۆک، دیزاین و ئەو پرسیارانە گفتوگۆ دەکەین کە کتێبەکە دروستیان دەکات.</p><p>کەتەلۆگی نموونەیی نوێ بە تەواوی خەیاڵییە و بۆ پیشاندانی توانای فرۆشگای کوردیی ئینکوێڵ دروست کراوە.</p>',
			),
			array(
				'title'   => 'هەڵبژاردەی ستاف: شارستانی مانگ',
				'cat'     => 'staff-picks',
				'content' => '<p><em>شارستانی مانگ</em> چیرۆکی یەکەم شارستانیی کوردی لەسەر مانگە؛ ڕۆمانێک لەسەر تەکنەلۆژیا، ناسنامە و ئەو نهێنییەی لە ژێر خاکی مانگدا دۆزرایەوە.</p><p>خێرایی چیرۆکەکە و وردەکاریی جیهانەکە وای کرد تا دوا پەڕە کتێبەکە دانەنێین. — ستافی ئینکوێڵ</p>',
			),
		);
		foreach ( $posts as $i => $post ) {
			$existing = get_page_by_path( sanitize_title( $post['title'] ), OBJECT, 'post' );
			$post_data = array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $post['title'],
				'post_name'    => sanitize_title( $post['title'] ),
				'post_content' => $post['content'],
				'post_date'    => wp_date( 'Y-m-d H:i:s', time() - ( 3 - $i ) * 4 * DAY_IN_SECONDS ),
			);
			if ( $existing ) {
				$post_data['ID'] = $existing->ID;
				$pid = (int) wp_update_post( $post_data );
			} else {
				$pid = (int) wp_insert_post( $post_data );
			}
			wp_set_object_terms( $pid, array( $post['cat'] ), 'category' );
			update_post_meta( $pid, '_inkwell_demo_post', 1 );
			$thumb_sku = array( 'guli-shax', 'sharistani-mang', 'nheni-qalai-kon' )[ $i ];
			if ( isset( $cover_ids[ $thumb_sku ] ) ) {
				set_post_thumbnail( $pid, $cover_ids[ $thumb_sku ] );
			}
		}

		$summary[] = __( '3 journal posts with covers', 'inkwell' );
	}

	/* Fictional Kurdish sample reviews. */
	$reviews = array(
		array( 'sku' => 'guli-shax', 'author' => 'شەیدا م.', 'rating' => 5, 'content' => 'زمانەکەی زۆر جوان و نزیکە. چیرۆکەکە تا دوا پەڕە لەگەڵ خۆی بردمی.' ),
		array( 'sku' => 'sharistani-mang', 'author' => 'ڕێبین ک.', 'rating' => 5, 'content' => 'خەیاڵی زانستی بە ڕەنگ و دەنگی کوردی؛ جیهانەکەی بە وردی دروست کراوە.' ),
		array( 'sku' => 'nheni-qalai-kon', 'author' => 'هێوا ع.', 'rating' => 4, 'content' => 'نهێنییەکە زیرەکانەیە و هەستی شوێنە مێژووییەکان زۆر بەهێزە.' ),
		array( 'sku' => 'meshki-aram', 'author' => 'نەرمین س.', 'rating' => 5, 'content' => 'ڕێنماییەکان سادە و کرداری بوون؛ لە هەفتەی یەکەمدا سوودم لێ بینی.' ),
		array( 'sku' => 'mrishka-u-mang', 'author' => 'دڵشاد ڕ.', 'rating' => 5, 'content' => 'منداڵەکانم هەموو شەوێک داوای دووبارە خوێندنەوەکەی دەکەن.' ),
		array( 'sku' => 'boni-xak', 'author' => 'ئاگرین ح.', 'rating' => 5, 'content' => 'هۆنراوەکان ئارام و پڕ لە وێنەی جوانن؛ بۆ دیاری زۆر گونجاوە.' ),
		array( 'sku' => 'dwayin-panjara', 'author' => 'سەربەست ن.', 'rating' => 4, 'content' => 'ڕۆمانێکی هێمن و کاریگەر لەسەر شار و یادەوەری. چاپەکەش جوانە.' ),
		array( 'sku' => 'kodi-zagros', 'author' => 'کارۆ ج.', 'rating' => 4, 'content' => 'تێکەڵکردنی تەکنەلۆژیا و شوێنە کۆنەکان بیرۆکەیەکی تازە و ڕاکێشەرە.' ),
		array( 'sku' => 'kujravi-juri-haft', 'author' => 'هاوژین ب.', 'rating' => 5, 'content' => 'لە دوو شەودا تەواوم کرد؛ کۆتاییەکە بەڕاستی چاوەڕواننەکراو بوو.' ),
		array( 'sku' => 'zhinga-u-ema', 'author' => 'ڕۆژگار ف.', 'rating' => 4, 'content' => 'بابەتە زانستییەکان بە نموونەی نزیک لە ژیانی ئێمە ڕوون کراونەتەوە.' ),
		array( 'sku' => 'chiroki-sharakan', 'author' => 'دیاکۆ و.', 'rating' => 5, 'content' => 'پڕ لە وردەکاری و چیرۆکی خۆشە؛ وێنەی شارەکان لە مێشکدا زیندوو دەکاتەوە.' ),
		array( 'sku' => 'pshilai-ktebxwen', 'author' => 'سارا پ.', 'rating' => 5, 'content' => 'چیرۆکێکی شیرین و گاڵتەجاڕ؛ کچەکەم خۆی دەستی بە خوێندنەوەی کرد.' ),
		array( 'sku' => 'dangi-rubar', 'author' => 'هەژار ت.', 'rating' => 4, 'content' => 'دەقێکی شاعیرانە و هەستیارە؛ هەندێک دیمەن دوای تەواوبوونیش لە یادم مانەوە.' ),
		array( 'sku' => 'gorani-bai-bakur', 'author' => 'لارا ئە.', 'rating' => 5, 'content' => 'ڕیتمی هۆنراوەکان وەک گۆرانییە؛ چەند جارێک دووبارەم خوێندنەوە.' ),
	);

	foreach ( $reviews as $review ) {
		$pid = wc_get_product_id_by_sku( $review['sku'] );
		if ( ! $pid || ! get_post_meta( $pid, '_inkwell_demo_product', true ) ) {
			continue;
		}
		$exists = get_comments(
			array(
				'post_id' => $pid,
				'author'  => $review['author'],
				'content' => $review['content'],
				'type'    => 'review',
				'count'   => true,
			)
		);
		if ( $exists ) {
			continue;
		}
		$cid = wp_insert_comment(
			array(
				'comment_post_ID'      => $pid,
				'comment_author'       => $review['author'],
				'comment_author_email' => 'reader@example.com',
				'comment_content'      => $review['content'],
				'comment_type'         => 'review',
				'comment_approved'     => 1,
				'comment_date'         => wp_date( 'Y-m-d H:i:s', time() - wp_rand( 1, 30 ) * DAY_IN_SECONDS ),
			)
		);
		if ( $cid ) {
			update_comment_meta( $cid, 'rating', (int) $review['rating'] );
		}
	}
	$summary[] = __( '14 fictional Kurdish demo reviews with ratings', 'inkwell' );

	/* Optional complete site setup. */
	if ( $apply_site_setup ) {
		/* Menus: use a dedicated demo menu so an existing "Main Menu" is never touched. */
		$menu_id = 0;
		$menu    = wp_get_nav_menu_object( 'Inkwell Demo Menu' );
		if ( $menu ) {
			$menu_id = (int) $menu->term_id;
		} else {
			$menu_id = (int) wp_create_nav_menu( 'Inkwell Demo Menu' );
		}
		if ( $menu_id ) {
			$existing_menu_items = (array) wp_get_nav_menu_items( $menu_id );
			if ( ! $existing_menu_items ) {
				$items = array(
				array( 'Home', 'post_type', $home ),
				array( 'Shop', 'post_type', $shop ),
				array( 'Genres', 'custom', $shop ),
				array( 'Journal', 'post_type', $journal ),
				array( 'About', 'post_type', $about ),
				array( 'Contact', 'post_type', $contact ),
			);
			$genre_parent = 0;
			foreach ( $items as $item ) {
				$args = array( 'menu-item-title' => $item[0], 'menu-item-status' => 'publish', 'menu-item-type' => $item[1] );
				if ( 'post_type' === $item[1] ) {
					$args['menu-item-object']    = 'page';
					$args['menu-item-object-id'] = $item[2];
				} else {
					$args['menu-item-url'] = get_permalink( $item[2] );
				}
				$new_id = wp_update_nav_menu_item( $menu_id, 0, $args );
				if ( 'Genres' === $item[0] ) {
					$genre_parent = $new_id;
				}
			}
				foreach ( $cats as $slug => $cdata ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-title'     => $cdata[0],
							'menu-item-status'    => 'publish',
							'menu-item-type'      => 'taxonomy',
							'menu-item-object'    => 'product_cat',
							'menu-item-object-id' => $cat_ids[ $slug ],
							'menu-item-parent-id' => $genre_parent,
						)
					);
				}
			}
			$locations            = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
		$summary[] = __( 'Dedicated demo navigation menu with genre dropdown assigned', 'inkwell' );

		/* Widgets: preserve every existing instance and only fill empty sidebars. */
		$shop_widgets = array(
			inkwell_demo_ensure_widget( 'widget_woocommerce_product_categories', 'woocommerce_product_categories', array( 'title' => __( 'Genres', 'inkwell' ), 'count' => 1, 'hierarchical' => 1, 'dropdown' => 0 ) ),
			inkwell_demo_ensure_widget( 'widget_woocommerce_price_filter', 'woocommerce_price_filter', array( 'title' => __( 'Filter by price', 'inkwell' ) ) ),
			inkwell_demo_ensure_widget( 'widget_woocommerce_top_rated_products', 'woocommerce_top_rated_products', array( 'title' => __( 'Top rated', 'inkwell' ), 'number' => 3 ) ),
		);
		$blog_widgets = array(
			inkwell_demo_ensure_widget( 'widget_recent-posts', 'recent-posts', array( 'title' => __( 'Recent posts', 'inkwell' ), 'number' => 4, 'show_date' => 0 ) ),
			inkwell_demo_ensure_widget( 'widget_categories', 'categories', array( 'title' => __( 'Journal categories', 'inkwell' ), 'count' => 1, 'dropdown' => 0, 'hierarchical' => 0 ) ),
		);
		$sidebars = get_option( 'sidebars_widgets', array() );
		if ( empty( $sidebars['sidebar-shop'] ) ) {
			$sidebars['sidebar-shop'] = $shop_widgets;
		}
		if ( empty( $sidebars['sidebar-blog'] ) ) {
			$sidebars['sidebar-blog'] = $blog_widgets;
		}
		update_option( 'sidebars_widgets', $sidebars );
		$summary[] = __( 'Empty shop and blog sidebars configured without replacing existing widgets', 'inkwell' );

		/* Settings. */
		update_option( 'woocommerce_currency', 'EUR' );
		update_option( 'woocommerce_coming_soon', 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );
		update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		update_option( 'page_for_posts', $journal );
		update_option( 'woocommerce_terms_page_id', $privacy );

		set_theme_mod( 'inkwell_announcement', 'Free shipping on orders over €25 — every book, everywhere.' );
		set_theme_mod( 'inkwell_home_cat_1', $cat_ids['fiction'] );
		set_theme_mod( 'inkwell_home_cat_2', $cat_ids['science-fiction-fantasy'] );
		set_theme_mod( 'inkwell_home_cat_3', $cat_ids['mystery-thriller'] );
		set_theme_mod( 'inkwell_home_cat_4', $cat_ids['poetry'] );
		set_theme_mod( 'inkwell_home_quote', 'There is no friend as loyal as a book.' );
		set_theme_mod( 'inkwell_home_quote_attr', 'Ernest Hemingway' );
		set_theme_mod( 'inkwell_home_testimonial_1_text', 'Ordered on Monday, reading on Wednesday. Beautifully packed and the recommendation note was a lovely touch.' );
		set_theme_mod( 'inkwell_home_testimonial_1_name', 'Marie D. — Paris' );
		set_theme_mod( 'inkwell_home_testimonial_2_text', 'Inkwell found me a long-out-of-print edition I had hunted for years. Customers for life.' );
		set_theme_mod( 'inkwell_home_testimonial_2_name', 'Tom R. — London' );
		set_theme_mod( 'inkwell_home_testimonial_3_text', 'The genre tiles make browsing a joy, and the staff picks never miss. My book budget is not okay.' );
		set_theme_mod( 'inkwell_home_testimonial_3_name', 'Sofia K. — Berlin' );
		set_theme_mod( 'inkwell_footer_about', 'A hand-picked collection of books for curious minds — fiction, history, science and stories for every shelf. Independent, reader-owned, shipping worldwide.' );
		set_theme_mod( 'inkwell_shipping_message', 'Free shipping over €25' );
		set_theme_mod( 'inkwell_fulfillment_message', 'In stock — usually ships within 24 hours' );
		set_theme_mod( 'inkwell_returns_message', '30-day returns' );
		set_theme_mod( 'inkwell_payment_methods', 'Visa, Mastercard, PayPal' );
		$summary[] = __( 'Store settings, front page & theme options applied', 'inkwell' );
	} else {
		$summary[] = __( 'Complete demo-site setup skipped; existing menus, sidebars, currency and front-page settings were preserved', 'inkwell' );
	}

	/* Refresh WC lookup tables so sorting & ratings are correct. */
	if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
		wc_update_product_lookup_tables();
	}
	foreach ( $data as $book ) {
		$pid = wc_get_product_id_by_sku( $book['sku'] );
		if ( $pid && get_post_meta( $pid, '_inkwell_demo_product', true ) && class_exists( 'WC_Comments' ) ) {
			WC_Comments::clear_transients( $pid );
		}
	}

	flush_rewrite_rules();
	update_option( 'inkwell_demo_imported', 1 );

	return $summary;
}
