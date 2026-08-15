<?php
/**
 * Inkwell demo content importer.
 *
 * Run with:  wp eval-file demo-content/import-products.php
 * Creates categories, authors, products, pages, menus, posts, reviews,
 * widgets and core settings. Idempotent: re-running updates by SKU.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$inkwell_data = json_decode( file_get_contents( __DIR__ . '/books.json' ), true );
if ( ! is_array( $inkwell_data ) ) {
	WP_CLI::error( 'Could not read books.json' );
}

$inkwell_root = dirname( __DIR__ );

/* ------------------------------------------------------------------ *
 * Helpers
 * ------------------------------------------------------------------ */

/**
 * Import a local image file into the media library.
 *
 * @param string $file Absolute path to file.
 * @param int    $post_id Attach to post.
 * @return int|WP_Error
 */
function inkwell_import_image( $file, $post_id = 0 ) {
	$uploads = wp_upload_dir();
	$dest    = $uploads['path'] . '/' . basename( $file );
	copy( $file, $dest );

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
	$attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	return $attach_id;
}

/**
 * Ensure a page exists and is published; create/publish if needed.
 *
 * @param string $slug    Slug.
 * @param string $title   Title.
 * @param string $content Content.
 * @return int Page ID.
 */
function inkwell_ensure_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		if ( 'publish' !== $existing->post_status ) {
			wp_update_post(
				array(
					'ID'          => $existing->ID,
					'post_status' => 'publish',
				)
			);
		}
		return (int) $existing->ID;
	}
	return wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
}

/* ------------------------------------------------------------------ *
 * Categories
 * ------------------------------------------------------------------ */
$inkwell_cats = array(
	'fiction'                  => array( 'Fiction', 'Novels, classics and contemporary literary fiction.' ),
	'science-fiction-fantasy'  => array( 'Science Fiction & Fantasy', 'Other worlds, far futures and impossible magic.' ),
	'mystery-thriller'         => array( 'Mystery & Thriller', 'Whodunits, suspense and page-turning thrillers.' ),
	'non-fiction'              => array( 'Non-Fiction', 'Ideas, science, business and big questions.' ),
	'history-biography'        => array( 'History & Biography', 'True stories and the lives behind them.' ),
	'childrens-books'          => array( "Children's Books", 'Picture books and first reads for young bookworms.' ),
	'poetry'                   => array( 'Poetry', 'Verse, collections and poems to savour slowly.' ),
);
$inkwell_cat_ids = array();
$inkwell_cat_by_name = array();
foreach ( $inkwell_cats as $slug => $data ) {
	$term = term_exists( $slug, 'product_cat' );
	if ( ! $term ) {
		$term = wp_insert_term( $data[0], 'product_cat', array( 'slug' => $slug, 'description' => $data[1] ) );
	}
	$inkwell_cat_ids[ $slug ]     = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
	$inkwell_cat_by_name[ $data[0] ] = $slug;
	WP_CLI::log( 'Category: ' . $data[0] );
}

/* ------------------------------------------------------------------ *
 * Authors
 * ------------------------------------------------------------------ */
$inkwell_author_bios = array(
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

foreach ( array_keys( $inkwell_author_bios ) as $inkwell_author ) {
	$term = term_exists( $inkwell_author, 'book_author' );
	if ( ! $term ) {
		$term = wp_insert_term( $inkwell_author, 'book_author' );
	}
	if ( is_array( $term ) ) {
		wp_update_term( $term['term_id'], 'book_author', array( 'description' => $inkwell_author_bios[ $inkwell_author ] ) );
	}
}
WP_CLI::log( 'Authors: ' . count( $inkwell_author_bios ) . ' created/updated' );

/* ------------------------------------------------------------------ *
 * Products
 * ------------------------------------------------------------------ */
$inkwell_cover_ids = array();

foreach ( $inkwell_data as $inkwell_index => $book ) {
	$sku     = $book['sku'];
	$cover   = $inkwell_root . '/demo-content/covers/' . $sku . '.png';
	$product = wc_get_product_id_by_sku( $sku );

	if ( $product ) {
		$product = wc_get_product( $product );
	} else {
		$product = new WC_Product_Simple();
	}

	/*
	 * Stagger publication into the shop (deterministic on every run):
	 * books near the end of the list are "new arrivals", earlier ones are
	 * backlist. 9 days apart.
	 */
	$inkwell_total    = count( $inkwell_data );
	$inkwell_age_days = ( $inkwell_total - 1 - $inkwell_index ) * 9;
	$product->set_date_created( gmdate( 'Y-m-d H:i:s', time() - $inkwell_age_days * DAY_IN_SECONDS ) );

	$product->set_name( $book['title'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_sku( $sku );
	$product->set_regular_price( $book['price'] );
	if ( $book['sale'] ) {
		$product->set_sale_price( $book['sale'] );
	}
	$product->set_featured( (bool) $book['featured'] );
	$product->set_short_description( $book['desc'] );

	$long = $book['desc'] . "\n\n" .
		'This edition: ' . ucfirst( $book['format'] ) . ' · ' . $book['pages'] . ' pages · ' .
		$book['publisher'] . ', ' . $book['year'] . ' · Language: ' . $book['language'] . ' · ISBN-13: ' . $book['isbn'] . '.' .
		"\n\nEvery order is packed with care and ships within 24 hours. Free shipping on orders over €25 and 30-day returns — because books should find their readers.";
	$product->set_description( $long );

	$product->set_category_ids( array( $inkwell_cat_ids[ $inkwell_cat_by_name[ $book['cat'] ] ?? $inkwell_cat_by_name['Fiction'] ] ) );
	$product->set_manage_stock( false );
	$product->set_stock_status( 'instock' );

	// Book details meta.
	$product->update_meta_data( '_inkwell_isbn', $book['isbn'] );
	$product->update_meta_data( '_inkwell_publisher', $book['publisher'] );
	$product->update_meta_data( '_inkwell_year', (int) $book['year'] );
	$product->update_meta_data( '_inkwell_pages', (int) $book['pages'] );
	$product->update_meta_data( '_inkwell_format', $book['format'] );
	$product->update_meta_data( '_inkwell_language', $book['language'] );

	$product_id = $product->save();

	// Author term.
	$author_term = term_exists( $book['author'], 'book_author' );
	if ( $author_term ) {
		wp_set_object_terms( $product_id, array( (int) ( is_array( $author_term ) ? $author_term['term_id'] : $author_term ) ), 'book_author' );
	}

	// Cover image.
	if ( ! isset( $inkwell_cover_ids[ $sku ] ) && file_exists( $cover ) ) {
		$img = inkwell_import_image( $cover, $product_id );
		if ( ! is_wp_error( $img ) ) {
			$inkwell_cover_ids[ $sku ] = (int) $img;
			$product->set_image_id( $img );
			$product->save();
		}
	}

	WP_CLI::log( 'Product: ' . $book['title'] . ( $book['sale'] ? ' (on sale)' : '' ) );
}

// Category thumbnails = representative covers.
$inkwell_cat_covers = array(
	'fiction'                 => 'pride-prejudice',
	'science-fiction-fantasy' => 'dune',
	'mystery-thriller'        => 'orient-express',
	'non-fiction'             => 'sapiens',
	'history-biography'       => 'long-walk-freedom',
	'childrens-books'         => 'hungry-caterpillar',
	'poetry'                  => 'waste-land',
);
foreach ( $inkwell_cat_covers as $slug => $sku ) {
	if ( isset( $inkwell_cover_ids[ $sku ] ) ) {
		update_term_meta( $inkwell_cat_ids[ $slug ], 'thumbnail_id', $inkwell_cover_ids[ $sku ] );
	}
}

/* ------------------------------------------------------------------ *
 * Pages
 * ------------------------------------------------------------------ */
$inkwell_shop_page    = wc_get_page_id( 'shop' );

// Front page: reuse existing setting; slug must NOT be "home" (WP canonical
// redirect would send "/" to "/home/").
$inkwell_home_page = (int) get_option( 'page_on_front' );
if ( ! $inkwell_home_page || 'page' !== get_post_type( $inkwell_home_page ) ) {
	$inkwell_home_page = inkwell_ensure_page( 'front', 'Home', '' );
}
if ( $inkwell_home_page ) {
	wp_update_post(
		array(
			'ID'        => $inkwell_home_page,
			'post_name' => 'front',
			'post_title' => 'Home',
		)
	);
}
$inkwell_about_page   = inkwell_ensure_page(
	'about',
	'About Inkwell',
	"<h2>Books, chosen by hand</h2>\n<p>Inkwell started as a single shelf in a small apartment and grew into the shop you see today. We are a small team of readers, and we stock the books we genuinely love — fiction, history, science, children's stories and everything in between.</p>\n<p>Every title is chosen by a human, read (at least partly) by a human, and shipped by a human who thinks books deserve better than plastic wrapping. If we wouldn't recommend a book to a friend, we don't stock it.</p>\n<h2>Independent by design</h2>\n<p>We work with independent publishers and small distributors wherever we can, and we donate a share of every sale to literacy programmes. Books are how ideas travel; we like to keep the routes open.</p>"
);
$inkwell_contact_page = inkwell_ensure_page(
	'contact',
	'Contact',
	"<p>We would love to hear from you — questions about an order, a recommendation request, or just to talk about what you're reading.</p>\n<p>Email: <a href=\"mailto:hello@inkwell.example\">hello@inkwell.example</a><br />\nPhone: +33 1 23 45 67 89<br />\nBookshop: 12 Rue des Lettres, 75000 Paris</p>\n<hr />\n<p><strong>Prefer email newsletters over phone calls?</strong> Join the reading list:</p>\n[inkwell_newsletter]"
);
$inkwell_privacy_page = inkwell_ensure_page(
	'privacy-policy',
	'Privacy Policy',
	"<p>This is a demo shop. We store only what is needed to fulfil orders, never share your data with third parties for marketing, and you can request deletion of your account at any time.</p>\n<p>Newsletter subscribers are stored securely and can unsubscribe with one click.</p>"
);
$inkwell_journal_page = inkwell_ensure_page( 'journal', 'The Journal', '' );

// WooCommerce pages (Shop, Cart, Checkout, My Account).
if ( ! $inkwell_shop_page ) {
	$inkwell_shop_page = inkwell_ensure_page( 'shop', 'Shop', '' );
	update_option( 'woocommerce_shop_page_id', $inkwell_shop_page );
}
foreach ( array( 'cart', 'checkout', 'myaccount' ) as $inkwell_wc_page ) {
	if ( ! wc_get_page_id( $inkwell_wc_page ) ) {
		$id = inkwell_ensure_page( $inkwell_wc_page, ucwords( $inkwell_wc_page ), '' );
		update_option( 'woocommerce_' . $inkwell_wc_page . '_page_id', $id );
	}
}

/*
 * Inkwell is a classic-template theme: pin Cart & Checkout to the classic
 * shortcodes so the theme's templates, hooks and styling fully apply.
 * (Users who prefer the block-based cart/checkout can swap the content in
 * the editor — the theme ships fallback styles for the blocks too.)
 */
$inkwell_cart_id     = wc_get_page_id( 'cart' );
$inkwell_checkout_id = wc_get_page_id( 'checkout' );
if ( $inkwell_cart_id && false === strpos( get_post( $inkwell_cart_id )->post_content, '[woocommerce_cart]' ) ) {
	wp_update_post(
		array(
			'ID'           => $inkwell_cart_id,
			'post_content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
		)
	);
}
if ( $inkwell_checkout_id && false === strpos( get_post( $inkwell_checkout_id )->post_content, '[woocommerce_checkout]' ) ) {
	wp_update_post(
		array(
			'ID'           => $inkwell_checkout_id,
			'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
		)
	);
}
if ( ! wc_get_page_id( 'terms' ) ) {
	update_option( 'woocommerce_terms_page_id', $inkwell_privacy_page );
}

// Tidy up defaults we don't need.
$inkwell_sample = get_page_by_path( 'sample-page' );
if ( $inkwell_sample ) {
	wp_delete_post( $inkwell_sample->ID, true );
}

/* ------------------------------------------------------------------ *
 * Journal posts
 * ------------------------------------------------------------------ */
$inkwell_post_cats = array( 'reading-lists' => 'Reading Lists', 'behind-the-shelves' => 'Behind the Shelves', 'staff-picks' => 'Staff Picks' );
foreach ( $inkwell_post_cats as $slug => $name ) {
	$term = term_exists( $slug, 'category' );
	if ( ! $term ) {
		wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
	}
}

$inkwell_posts = array(
	array(
		'title'   => 'Autumn reading list: 7 books for long evenings',
		'cat'     => 'reading-lists',
		'content' => "<p>The light goes early and the evenings get long — which, let's be honest, is a gift for readers. Here is what the Inkwell team is reading this season.</p>\n<h3>1. Where the Crawdads Sing — Delia Owens</h3>\n<p>Mystery and marshland. We could not put it down.</p>\n<h3>2. Sapiens — Yuval Noah Harari</h3>\n<p>The big-picture book that starts a hundred conversations.</p>\n<h3>3. Dune — Frank Herbert</h3>\n<p>With the films bringing new readers, now is the time.</p>\n<p>…and four more, in the shop and in our newsletter. <a href=\"/shop/\">Browse the full collection →</a></p>",
	),
	array(
		'title'   => 'Behind the shelves: how we pick our stock',
		'cat'     => 'behind-the-shelves',
		'content' => "<p>People often ask how a small bookshop decides what to stock. The honest answer: slowly, and by committee.</p>\n<p>Every month we each bring one book we loved to the table. We talk about it over terrible coffee. If two of us have read it and one of us can't stop talking about it, it goes on the shelf. That's the whole algorithm.</p>\n<p>We also keep the classics in print, support debut authors, and listen carefully when you ask us to find something. Two of our best-selling titles this year started as customer requests.</p>",
	),
	array(
		'title'   => 'Staff pick: The Name of the Wind',
		'cat'     => 'staff-picks',
		'content' => "<p>Every so often a book arrives that you press into people's hands. For me, that book is Patrick Rothfuss's <em>The Name of the Wind</em>.</p>\n<p>It is the story of Kvothe — musician, student, legend — told in his own words, and it does what the very best fantasy does: it makes the world feel real enough to walk into. The prose is beautiful, the magic system is genuinely clever, and the mystery at its heart kept me up for three nights.</p>\n<p>If you like one thing this season, let it be this. — Claire, Inkwell</p>",
	),
);

$inkwell_post_ids = array();
foreach ( $inkwell_posts as $i => $post ) {
	$existing = get_page_by_path( sanitize_title( $post['title'] ), OBJECT, 'post' );
	$pid      = $existing ? (int) $existing->ID : wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $post['title'],
			'post_name'    => sanitize_title( $post['title'] ),
			'post_content' => $post['content'],
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 3 - $i ) * 86400 * 4 ),
		)
	);
	wp_set_object_terms( $pid, array( $post['cat'] ), 'category' );

	// Feature image: a cover from a matching category.
	$cover_sku = array( 'hobbit', 'dune', 'name-of-the-wind' )[ $i ];
	if ( isset( $inkwell_cover_ids[ $cover_sku ] ) ) {
		set_post_thumbnail( $pid, $inkwell_cover_ids[ $cover_sku ] );
	}
	$inkwell_post_ids[] = $pid;
	WP_CLI::log( 'Post: ' . $post['title'] );
}

/* ------------------------------------------------------------------ *
 * Reviews
 * ------------------------------------------------------------------ */
$inkwell_reviews = array(
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
foreach ( $inkwell_reviews as $review ) {
	$pid = wc_get_product_id_by_sku( $review['sku'] );
	if ( ! $pid ) {
		continue;
	}
	// Idempotent: skip if this reviewer already left this review.
	$exists = get_comments(
		array(
			'post_id'    => $pid,
			'author'     => $review['author'],
			'content'    => $review['content'],
			'type'       => 'review',
			'count'      => true,
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
			'comment_date'         => gmdate( 'Y-m-d H:i:s', time() - rand( 1, 30 ) * 86400 ),
		)
	);
	if ( $cid ) {
		update_comment_meta( $cid, 'rating', (int) $review['rating'] );
	}
}
WP_CLI::log( 'Reviews: ' . count( $inkwell_reviews ) . ' added' );

/* ------------------------------------------------------------------ *
 * Menus
 * ------------------------------------------------------------------ */
$inkwell_menu = wp_get_nav_menu_object( 'Main Menu' );
if ( ! $inkwell_menu ) {
	$inkwell_menu_id = wp_create_nav_menu( 'Main Menu' );
} else {
	$inkwell_menu_id = (int) $inkwell_menu->term_id;
}

if ( $inkwell_menu_id ) {
	// Reset.
	foreach ( wp_get_nav_menu_items( $inkwell_menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}

	$items = array(
		array( 'title' => 'Home', 'type' => 'post_type', 'object' => 'page', 'object_id' => $inkwell_home_page ),
		array( 'title' => 'Shop', 'type' => 'post_type', 'object' => 'page', 'object_id' => $inkwell_shop_page ),
		array( 'title' => 'Genres', 'type' => 'custom', 'url' => get_permalink( $inkwell_shop_page ) ),
		array( 'title' => 'Journal', 'type' => 'post_type', 'object' => 'page', 'object_id' => $inkwell_journal_page ),
		array( 'title' => 'About', 'type' => 'post_type', 'object' => 'page', 'object_id' => $inkwell_about_page ),
		array( 'title' => 'Contact', 'type' => 'post_type', 'object' => 'page', 'object_id' => $inkwell_contact_page ),
	);

	$genre_parent = 0;
	foreach ( $items as $item ) {
		$args = array(
			'menu-item-title'   => $item['title'],
			'menu-item-status'  => 'publish',
			'menu-item-type'    => $item['type'],
		);
		if ( 'post_type' === $item['type'] ) {
			$args['menu-item-object']    = $item['object'];
			$args['menu-item-object-id'] = $item['object_id'];
		} else {
			$args['menu-item-url'] = $item['url'];
		}
		$item_id = wp_update_nav_menu_item( $inkwell_menu_id, 0, $args );
		if ( 'Genres' === $item['title'] ) {
			$genre_parent = $item_id;
		}
	}

	foreach ( $inkwell_cats as $slug => $data ) {
		wp_update_nav_menu_item(
			$inkwell_menu_id,
			0,
			array(
				'menu-item-title'     => $data[0],
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'taxonomy',
				'menu-item-object'    => 'product_cat',
				'menu-item-object-id' => $inkwell_cat_ids[ $slug ],
				'menu-item-parent-id' => $genre_parent,
			)
		);
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $inkwell_menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	WP_CLI::log( 'Menu: Main Menu with ' . count( $items ) + 6 . ' items' );
}

// Footer menus.
$inkwell_footer_shop = wp_get_nav_menu_object( 'Footer Shop' );
if ( ! $inkwell_footer_shop ) {
	$inkwell_footer_shop_id = wp_create_nav_menu( 'Footer Shop' );
} else {
	$inkwell_footer_shop_id = (int) $inkwell_footer_shop->term_id;
}
foreach ( wp_get_nav_menu_items( $inkwell_footer_shop_id ) as $item ) {
	wp_delete_post( $item->ID, true );
}
wp_update_nav_menu_item(
	$inkwell_footer_shop_id,
	0,
	array(
		'menu-item-title'  => 'Shop all books',
		'menu-item-status' => 'publish',
		'menu-item-type'   => 'post_type',
		'menu-item-object' => 'page',
		'menu-item-object-id' => $inkwell_shop_page,
	)
);
foreach ( $inkwell_cats as $slug => $data ) {
	wp_update_nav_menu_item(
		$inkwell_footer_shop_id,
		0,
		array(
			'menu-item-title'     => $data[0],
			'menu-item-status'    => 'publish',
			'menu-item-type'      => 'taxonomy',
			'menu-item-object'    => 'product_cat',
			'menu-item-object-id' => $inkwell_cat_ids[ $slug ],
		)
	);
}

$inkwell_footer_help = wp_get_nav_menu_object( 'Footer Help' );
if ( ! $inkwell_footer_help ) {
	$inkwell_footer_help_id = wp_create_nav_menu( 'Footer Help' );
} else {
	$inkwell_footer_help_id = (int) $inkwell_footer_help->term_id;
}
foreach ( wp_get_nav_menu_items( $inkwell_footer_help_id ) as $item ) {
	wp_delete_post( $item->ID, true );
}
$inkwell_help_items = array(
	array( 'About us', $inkwell_about_page ),
	array( 'Contact', $inkwell_contact_page ),
	array( 'The Journal', $inkwell_journal_page ),
	array( 'My account', wc_get_page_id( 'myaccount' ) ),
	array( 'Privacy policy', $inkwell_privacy_page ),
);
foreach ( $inkwell_help_items as $item ) {
	wp_update_nav_menu_item(
		$inkwell_footer_help_id,
		0,
		array(
			'menu-item-title'     => $item[0],
			'menu-item-status'    => 'publish',
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $item[1],
		)
	);
}

$locations = get_theme_mod( 'nav_menu_locations', array() );
$locations['footer-shop'] = $inkwell_footer_shop_id;
$locations['footer-help'] = $inkwell_footer_help_id;
set_theme_mod( 'nav_menu_locations', $locations );

/* ------------------------------------------------------------------ *
 * Widgets
 * ------------------------------------------------------------------ */
update_option(
	'widget_woocommerce_product_categories',
	array(
		1 => array( 'title' => 'Genres', 'count' => 1, 'hierarchical' => 1, 'dropdown' => 0 ),
		'_multiwidget' => 1,
	)
);
update_option(
	'widget_woocommerce_price_filter',
	array(
		1 => array( 'title' => 'Filter by price' ),
		'_multiwidget' => 1,
	)
);
update_option(
	'widget_woocommerce_top_rated_products',
	array(
		1 => array( 'title' => 'Top rated', 'number' => 3 ),
		'_multiwidget' => 1,
	)
);
update_option(
	'widget_recent-posts',
	array(
		1 => array( 'title' => 'Recent posts', 'number' => 4, 'show_date' => 0 ),
		'_multiwidget' => 1,
	)
);
update_option(
	'widget_categories',
	array(
		1 => array( 'title' => 'Journal categories', 'count' => 1, 'dropdown' => 0, 'hierarchical' => 0 ),
		'_multiwidget' => 1,
	)
);

$sidebars = get_option( 'sidebars_widgets' );
$sidebars['sidebar-shop'] = array( 'woocommerce_product_categories-1', 'woocommerce_price_filter-1', 'woocommerce_top_rated_products-1' );
$sidebars['sidebar-blog'] = array( 'recent-posts-1', 'categories-1' );
update_option( 'sidebars_widgets', $sidebars );

/* ------------------------------------------------------------------ *
 * Settings & theme mods
 * ------------------------------------------------------------------ */
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'woocommerce_currency', 'EUR' );

// WC 9.5+ ships "coming soon" store mode on by default for new installs.
// The demo store is live, so disable it explicitly.
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_store_pages_only', 'no' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $inkwell_home_page );
update_option( 'page_for_posts', $inkwell_journal_page );

set_theme_mod( 'inkwell_announcement', 'Free shipping on orders over €25 — every book, everywhere.' );
set_theme_mod( 'inkwell_home_cat_1', $inkwell_cat_ids['fiction'] );
set_theme_mod( 'inkwell_home_cat_2', $inkwell_cat_ids['science-fiction-fantasy'] );
set_theme_mod( 'inkwell_home_cat_3', $inkwell_cat_ids['mystery-thriller'] );
set_theme_mod( 'inkwell_home_cat_4', $inkwell_cat_ids['poetry'] );
set_theme_mod( 'inkwell_home_quote', 'There is no friend as loyal as a book.' );
set_theme_mod( 'inkwell_home_quote_attr', 'Ernest Hemingway' );
set_theme_mod(
	'inkwell_home_testimonial_1_text',
	'Ordered on Monday, reading on Wednesday. Beautifully packed and the recommendation note was a lovely touch.'
);
set_theme_mod( 'inkwell_home_testimonial_1_name', 'Marie D. — Paris' );
set_theme_mod(
	'inkwell_home_testimonial_2_text',
	'Inkwell found me a long-out-of-print edition I had hunted for years. Customers for life.'
);
set_theme_mod( 'inkwell_home_testimonial_2_name', 'Tom R. — London' );
set_theme_mod(
	'inkwell_home_testimonial_3_text',
	'The genre tiles make browsing a joy, and the staff picks never miss. My book budget is not okay.'
);
set_theme_mod( 'inkwell_home_testimonial_3_name', 'Sofia K. — Berlin' );
set_theme_mod( 'inkwell_footer_about', 'A hand-picked collection of books for curious minds — fiction, history, science and stories for every shelf. Independent, reader-owned, shipping worldwide.' );

// Refresh product lookup tables so sorting/ratings are correct.
if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
	wc_update_product_lookup_tables();
}

// Clear per-product rating transients.
foreach ( $inkwell_data as $book ) {
	$pid = wc_get_product_id_by_sku( $book['sku'] );
	if ( $pid && class_exists( 'WC_Comments' ) ) {
		WC_Comments::clear_transients( $pid );
	}
}

WP_CLI::success( 'Demo content imported. Run: wp rewrite flush --hard' );
