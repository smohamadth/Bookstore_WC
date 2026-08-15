<?php
/**
 * Journal preview (latest posts).
 *
 * @package Inkwell
 */
if ( get_theme_mod( 'inkwell_home_blog_hide', false ) ) {
	return;
}

$query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
	)
);
if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="section section--alt">
	<div class="container">
		<?php
		inkwell_section_header(
			__( 'From the journal', 'inkwell' ),
			__( 'Notes from the shelves', 'inkwell' ),
			get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ),
			__( 'All journal posts', 'inkwell' )
		);
		?>
		<div class="posts-grid">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part( 'template-parts/content' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
