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

	if ( 0 < wp_count_posts( 'product' )->publish ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Inkwell: your shop is empty.', 'inkwell' ); ?></strong>
			<?php esc_html_e( 'Seed it with the bundled sample catalog (33 books, 7 genres, 31 authors, reviews, menus and pages) in one click.', 'inkwell' ); ?>
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
				<li><strong>33 books</strong> <?php esc_html_e( 'with covers, real bibliographic data, sale prices and featured flags', 'inkwell' ); ?></li>
				<li><strong>7 genres</strong> <?php esc_html_e( '(Fiction, Sci-Fi & Fantasy, Mystery & Thriller, Non-Fiction, History & Biography, Children’s Books, Poetry)', 'inkwell' ); ?></li>
				<li><strong>31 authors</strong> <?php esc_html_e( 'with bios → author archive pages', 'inkwell' ); ?></li>
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
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'demo_data_missing', __( 'Could not read demo data. The theme files may be incomplete.', 'inkwell' ) );
	}

	// Only opt a fresh site into pretty permalinks when full setup was requested.
	if ( $apply_site_setup && ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	$summary = array();

	/* Categories */
	$cats = array(
		'fiction'                 => array( 'Fiction', 'Novels, classics and contemporary literary fiction.' ),
		'science-fiction-fantasy' => array( 'Science Fiction & Fantasy', 'Other worlds, far futures and impossible magic.' ),
		'mystery-thriller'        => array( 'Mystery & Thriller', 'Whodunits, suspense and page-turning thrillers.' ),
		'non-fiction'             => array( 'Non-Fiction', 'Ideas, science, business and big questions.' ),
		'history-biography'       => array( 'History & Biography', 'True stories and the lives behind them.' ),
		'childrens-books'         => array( "Children's Books", 'Picture books and first reads for young bookworms.' ),
		'poetry'                  => array( 'Poetry', 'Verse, collections and poems to savour slowly.' ),
	);
	$cat_ids = array();
	foreach ( $cats as $slug => $cdata ) {
		$term = term_exists( $slug, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $cdata[0], 'product_cat', array( 'slug' => $slug, 'description' => $cdata[1] ) );
		}
		if ( is_wp_error( $term ) ) {
			return $term;
		}
		$cat_ids[ $slug ] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
	}
	$summary[] = sprintf( __( '%d product categories created/updated', 'inkwell' ), count( $cats ) );

	/* Authors */
	$bios = array(
		'Jane Austen'        => 'Jane Austen (1775–1817) wrote six major novels, all of them quietly revolutionary in their wit and social insight. She remains one of the most widely read novelists in English.',
		'George Orwell'      => 'George Orwell (1903–1950) was an English novelist, essayist and journalist whose works — including Nineteen Eighty-Four and Animal Farm — shaped how we think about power and truth.',
		'F. Scott Fitzgerald' => 'F. Scott Fitzgerald (1896–1940) captured the glamour and disillusionment of the Jazz Age. The Great Gatsby is considered one of the great American novels.',
		'Delia Owens'        => 'Delia Owens is an American author and zoologist. Where the Crawdads Sing, her debut novel, spent years on bestseller lists around the world.',
		'Frank Herbert'      => 'Frank Herbert (1920–1986) was an American science fiction author, best known for Dune, the best-selling science fiction novel of all time.',
		'J.R.R. Tolkien'     => 'J.R.R. Tolkien (1892–1973) was an Oxford professor of philology and the creator of Middle-earth, father of modern fantasy literature.',
		'Patrick Rothfuss'   => 'Patrick Rothfuss is an American author whose debut, The Name of the Wind, won the Quill Award and became an international bestseller.',
		'Agatha Christie'    => 'Agatha Christie (1890–1976) is the best-selling novelist of all time and the queen of crime, creator of Hercule Poirot and Miss Marple.',
		'Dan Brown'          => 'Dan Brown is the American author of the Robert Langdon thrillers, including The Da Vinci Code, one of the best-selling novels ever published.',
		'Stieg Larsson'      => 'Stieg Larsson (1954–2004) was a Swedish journalist and crime writer, author of the Millennium trilogy that began with The Girl with the Dragon Tattoo.',
		'Yuval Noah Harari'  => 'Yuval Noah Harari is an Israeli historian and professor at the Hebrew University of Jerusalem, author of the international phenomenon Sapiens.',
		'Stephen Hawking'    => 'Stephen Hawking (1942–2018) was one of the greatest theoretical physicists of his generation and a tireless communicator of science.',
		'Daniel Kahneman'    => 'Daniel Kahneman is a psychologist and Nobel laureate in economics whose work transformed behavioural science; Thinking, Fast and Slow distils a lifetime of research.',
		'Anne Frank'         => 'Anne Frank (1929–1945) kept her famous diary while in hiding in Amsterdam during the Second World War. It has been read by tens of millions of people.',
		'Nelson Mandela'     => 'Nelson Mandela (1918–2013) was the first president of democratic South Africa and one of the most remarkable leaders of the twentieth century.',
		'Eric Carle'         => 'Eric Carle (1929–2021) was the beloved author and illustrator of more than seventy picture books, including The Very Hungry Caterpillar.',
		'Roald Dahl'         => 'Roald Dahl (1916–1990) was the world’s most famous storyteller for children, author of Matilda, Charlie and the Chocolate Factory and many more.',
		'Harper Lee'         => 'Harper Lee (1926–2016) wrote To Kill a Mockingbird, winner of the Pulitzer Prize and one of the best-loved novels of the twentieth century.',
		'J.D. Salinger'      => 'J.D. Salinger (1919–2010) was an American writer whose novel The Catcher in the Rye became one of the defining books of modern literature.',
		'Gabriel García Márquez' => 'Gabriel García Márquez (1927–2014) was a Colombian novelist, Nobel laureate and the master of magical realism, author of One Hundred Years of Solitude.',
		'Sally Rooney'       => 'Sally Rooney is an Irish novelist whose books — Conversations with Friends and Normal People — capture contemporary life with rare precision.',
		'William Gibson'     => 'William Gibson is the American-Canadian writer who invented cyberpunk with Neuromancer; his novels map the edge of technology and culture.',
		'Ursula K. Le Guin'  => 'Ursula K. Le Guin (1929–2018) was one of the most celebrated writers of science fiction and fantasy, a radical thinker about society, gender and power.',
		'Andy Weir'          => 'Andy Weir is the American author of The Martian and Project Hail Mary, beloved for rigorously researched, funny, optimistic science fiction.',
		'Gillian Flynn'      => 'Gillian Flynn is an American author and screenwriter whose psychological thrillers — Gone Girl, Sharp Objects, Dark Places — redefined the genre.',
		'Alex Michaelides'   => 'Alex Michaelides is a British-Cypriot screenwriter and author of The Silent Patient, one of the best-selling thrillers of the decade.',
		'James Clear'        => 'James Clear is an American author and speaker whose book Atomic Habits has helped millions build systems for lasting change.',
		'Tara Westover'      => 'Tara Westover is an American historian and memoirist. Educated, her story of leaving a survivalist childhood for Cambridge, won the year’s top prizes.',
		'T.S. Eliot'         => 'T.S. Eliot (1888–1965) was an American-born poet, playwright and critic, Nobel laureate and a central figure of literary modernism.',
		'Rupi Kaur'          => 'Rupi Kaur is a Canadian poet and illustrator whose debut collection Milk and Honey became one of the best-selling poetry books ever.',
		'E.B. White'         => 'E.B. White (1899–1985) was an American writer and essayist, author of Charlotte’s Web, Stuart Little and The Trumpet of the Swan.',
	);
	$author_ids = array();
	foreach ( array_keys( $bios ) as $name ) {
		$term       = term_exists( $name, 'book_author' );
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
				wp_update_term( $term_id, 'book_author', array( 'description' => $bios[ $name ] ) );
				update_term_meta( $term_id, '_inkwell_demo_term', 1 );
			}
			$author_ids[ $name ] = $term_id;
		}
	}
	$summary[] = sprintf( __( '%d book authors with bios', 'inkwell' ), count( $author_ids ) );

	/* Products */
	$cover_ids   = array();
	$cat_by_name = array();
	foreach ( $cats as $slug => $cdata ) {
		$cat_by_name[ $cdata[0] ] = $slug;
	}
	$total            = count( $data );
	$skipped_products = 0;

	foreach ( $data as $index => $book ) {
		$sku         = sanitize_text_field( $book['sku'] );
		$existing_id = (int) wc_get_product_id_by_sku( $sku );
		$is_demo     = $existing_id && ( get_post_meta( $existing_id, '_inkwell_demo_product', true ) || $book['isbn'] === get_post_meta( $existing_id, '_inkwell_isbn', true ) );
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
			$product->set_date_created( gmdate( 'Y-m-d H:i:s', time() - ( $total - 1 - $index ) * 9 * DAY_IN_SECONDS ) );
		}
		$product->set_name( $book['title'] );
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
		$product->set_description( $book['desc'] . "\n\nThis edition: " . ucfirst( $book['format'] ) . ' · ' . $book['pages'] . ' pages · ' . $book['publisher'] . ', ' . $book['year'] . ' · Language: ' . $book['language'] . ' · ISBN-13: ' . $book['isbn'] . '.' );
		$product->set_category_ids( array( $cat_ids[ $cat_by_name[ $book['cat'] ] ] ) );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );

		$product->update_meta_data( '_inkwell_isbn', $book['isbn'] );
		$product->update_meta_data( '_inkwell_publisher', $book['publisher'] );
		$product->update_meta_data( '_inkwell_year', (int) $book['year'] );
		$product->update_meta_data( '_inkwell_pages', (int) $book['pages'] );
		$product->update_meta_data( '_inkwell_format', $book['format'] );
		$product->update_meta_data( '_inkwell_language', $book['language'] );
		$product->update_meta_data( '_inkwell_demo_product', 1 );

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
		'fiction'                 => 'pride-prejudice',
		'science-fiction-fantasy' => 'dune',
		'mystery-thriller'        => 'orient-express',
		'non-fiction'             => 'sapiens',
		'history-biography'       => 'long-walk-freedom',
		'childrens-books'         => 'hungry-caterpillar',
		'poetry'                  => 'waste-land',
	);
	foreach ( $cat_covers as $slug => $sku ) {
		if ( isset( $cover_ids[ $sku ] ) ) {
			update_term_meta( $cat_ids[ $slug ], 'thumbnail_id', $cover_ids[ $sku ] );
		}
	}

	/* Pages */
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
	foreach ( array( 'cart', 'checkout', 'myaccount' ) as $wc_page ) {
		if ( (int) wc_get_page_id( $wc_page ) <= 0 ) {
			$id = inkwell_demo_ensure_page( $wc_page, ucwords( $wc_page ), '' );
			update_option( 'woocommerce_' . $wc_page . '_page_id', $id );
		}
	}

	// On explicit full setup, initialize only genuinely empty cart/checkout
	// pages. Existing block or shortcode content is always preserved.
	$cart_id     = (int) wc_get_page_id( 'cart' );
	$checkout_id = (int) wc_get_page_id( 'checkout' );
	if ( $apply_site_setup && $cart_id > 0 && '' === trim( (string) get_post_field( 'post_content', $cart_id ) ) ) {
		wp_update_post( array( 'ID' => $cart_id, 'post_content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->' ) );
	}
	if ( $apply_site_setup && $checkout_id > 0 && '' === trim( (string) get_post_field( 'post_content', $checkout_id ) ) ) {
		wp_update_post( array( 'ID' => $checkout_id, 'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->' ) );
	}

	$summary[] = __( 'Pages created (Home, Shop, About, Contact, Journal, Privacy)', 'inkwell' );

	/* Journal posts */
	$post_cats = array( 'reading-lists' => 'Reading Lists', 'behind-the-shelves' => 'Behind the Shelves', 'staff-picks' => 'Staff Picks' );
	foreach ( $post_cats as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
	$posts = array(
		array(
			'title'   => 'Autumn reading list: 7 books for long evenings',
			'cat'     => 'reading-lists',
			'content' => '<p>The light goes early and the evenings get long — which, let’s be honest, is a gift for readers. Here is what the Inkwell team is reading this season.</p><h3>1. Where the Crawdads Sing — Delia Owens</h3><p>Mystery and marshland. We could not put it down.</p><h3>2. Sapiens — Yuval Noah Harari</h3><p>The big-picture book that starts a hundred conversations.</p><h3>3. Dune — Frank Herbert</h3><p>With the films bringing new readers, now is the time.</p>',
		),
		array(
			'title'   => 'Behind the shelves: how we pick our stock',
			'cat'     => 'behind-the-shelves',
			'content' => '<p>People often ask how a small bookshop decides what to stock. The honest answer: slowly, and by committee.</p><p>Every month we each bring one book we loved to the table. We talk about it over terrible coffee. If two of us have read it and one of us can’t stop talking about it, it goes on the shelf. That’s the whole algorithm.</p>',
		),
		array(
			'title'   => 'Staff pick: The Name of the Wind',
			'cat'     => 'staff-picks',
			'content' => '<p>Every so often a book arrives that you press into people’s hands. For me, that book is Patrick Rothfuss’s <em>The Name of the Wind</em>.</p><p>It is the story of Kvothe — musician, student, legend — told in his own words, and it does what the very best fantasy does: it makes the world feel real enough to walk into. The prose is beautiful, the magic system is genuinely clever, and the mystery at its heart kept me up for three nights.</p><p>If you like one thing this season, let it be this. — Claire, Inkwell</p>',
		),
	);
	$post_ids = array();
	foreach ( $posts as $i => $post ) {
		$existing = get_page_by_path( sanitize_title( $post['title'] ), OBJECT, 'post' );
		$pid      = $existing ? (int) $existing->ID : (int) wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $post['title'],
				'post_name'    => sanitize_title( $post['title'] ),
				'post_content' => $post['content'],
				'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 3 - $i ) * 4 * DAY_IN_SECONDS ),
			)
		);
		wp_set_object_terms( $pid, array( $post['cat'] ), 'category' );
		$thumb_sku = array( 'hobbit', 'dune', 'name-of-the-wind' )[ $i ];
		if ( isset( $cover_ids[ $thumb_sku ] ) ) {
			set_post_thumbnail( $pid, $cover_ids[ $thumb_sku ] );
		}
		$post_ids[] = $pid;
	}
	$summary[] = __( '3 journal posts with covers', 'inkwell' );

	/* Reviews */
	$reviews = array(
		array( 'sku' => 'pride-prejudice', 'author' => 'Emma L.', 'rating' => 5, 'content' => 'Delightful from the first page to the last. This edition has a lovely introduction and the cover is beautiful on the shelf.' ),
		array( 'sku' => 'dune', 'author' => 'Marcus T.', 'rating' => 4, 'content' => 'A world so complete it feels real. Dense in places, but the payoff is enormous — I immediately ordered the sequels.' ),
		array( 'sku' => 'sapiens', 'author' => 'Priya S.', 'rating' => 5, 'content' => 'The kind of book that changes how you see everything. Arrived quickly and in perfect condition.' ),
		array( 'sku' => 'matilda', 'author' => 'Jonas W.', 'rating' => 5, 'content' => 'My daughter has made me read it four times. Worth every penny.' ),
		array( 'sku' => 'nineteen-eighty-four', 'author' => 'Amelie R.', 'rating' => 4, 'content' => 'Still chillingly relevant. A book every generation should read at least once.' ),
		array( 'sku' => 'to-kill-a-mockingbird', 'author' => 'Daniel O.', 'rating' => 5, 'content' => 'Somehow both gentle and devastating. The edition itself is lovely — deckled edges, good paper, a keeper.' ),
		array( 'sku' => 'neuromancer', 'author' => 'Sara H.', 'rating' => 4, 'content' => 'Dated in the best way — you can see every modern cyberpunk story standing on its shoulders.' ),
		array( 'sku' => 'atomic-habits', 'author' => 'Kevin B.', 'rating' => 5, 'content' => 'I have bought four copies for other people. The systems actually stick; a year on, I still use them daily.' ),
		array( 'sku' => 'left-hand-of-darkness', 'author' => 'Iris V.', 'rating' => 5, 'content' => 'Le Guin at her finest. A quiet, profound novel about trust and difference — and the winter world of Gethen is unforgettable.' ),
		array( 'sku' => 'gone-girl', 'author' => 'Tom R.', 'rating' => 4, 'content' => 'Raced through it in two nights. You will not see the turn coming, even when you think you have.' ),
		array( 'sku' => 'waste-land', 'author' => 'Nadia K.', 'rating' => 4, 'content' => 'The notes in this edition are worth the price alone. April is the cruellest month, indeed.' ),
		array( 'sku' => 'charlottes-web', 'author' => 'Hannah P.', 'rating' => 5, 'content' => 'Reading this to my son — some pages are magic, some are tears. A perfect book.' ),
		array( 'sku' => 'and-then-there-were-none', 'author' => 'George F.', 'rating' => 5, 'content' => 'The perfect locked-room mystery. I guessed the ending at about 60% and still could not put it down.' ),
		array( 'sku' => 'charlie-chocolate-factory', 'author' => 'Lily M.', 'rating' => 5, 'content' => 'The kids loved the audio edition on a long drive. Pure joy, and the illustrations in this edition are wonderful.' ),
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
				'comment_date'         => gmdate( 'Y-m-d H:i:s', time() - wp_rand( 1, 30 ) * DAY_IN_SECONDS ),
			)
		);
		if ( $cid ) {
			update_comment_meta( $cid, 'rating', (int) $review['rating'] );
		}
	}
	$summary[] = __( '14 customer reviews with ratings', 'inkwell' );

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
