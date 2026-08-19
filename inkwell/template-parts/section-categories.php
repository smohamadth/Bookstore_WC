<?php
/**
 * Featured genre tiles.
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_categories_hide', false ) || ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

$ids = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$id = (int) get_theme_mod( "inkwell_home_cat_{$i}", 0 );
	if ( $id ) {
		$ids[] = $id;
	}
}

// Fallback: first four non-empty categories.
if ( ! $ids ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 4,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}
	$ids = wp_list_pluck( $terms, 'term_id' );
}

$terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'include'    => $ids,
		'hide_empty' => false,
		'orderby'    => 'include',
	)
);
if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<?php
		inkwell_section_header(
			__( 'Genres', 'inkwell' ),
			__( 'Browse by genre', 'inkwell' ),
			function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
			__( 'Shop all', 'inkwell' )
		);
		?>
		<div class="genre-grid">
			<?php $tile_number = 0; ?>
			<?php foreach ( $terms as $term ) : ?>
				<?php
				$tile_number++;
				$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$count    = (int) $term->count;
				?>
				<a class="genre-tile" href="<?php echo esc_url( get_term_link( $term ) ); ?>" data-reveal>
					<span class="genre-tile__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tile_number, 2, '0', STR_PAD_LEFT ) ); ?></span>
					<?php
					if ( $thumb_id ) {
						echo wp_get_attachment_image( $thumb_id, 'inkwell-tile' );
					} else {
						echo '<span class="genre-tile-fallback">' . esc_html( $term->name ) . '</span>';
					}
					?>
					<span class="tile-label">
						<?php echo esc_html( $term->name ); ?>
						<span class="tile-count">
							<?php
							/* translators: %d: number of books. */
							printf( esc_html( _n( '%d book', '%d books', $count, 'inkwell' ) ), esc_html( $count ) );
							?>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
