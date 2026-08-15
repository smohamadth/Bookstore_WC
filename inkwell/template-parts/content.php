<?php
/**
 * Blog post card.
 *
 * @package Inkwell
 */
$inkwell_cats = get_the_category();
?>
<article <?php post_class( 'post-card' ); ?> data-reveal>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium_large' ); ?>
		</a>
	<?php endif; ?>
	<div class="card-body">
		<?php if ( $inkwell_cats ) : ?>
			<span class="card-cat"><?php echo esc_html( $inkwell_cats[0]->name ); ?></span>
		<?php endif; ?>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="card-meta"><?php echo esc_html( get_the_date() ); ?></div>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
		<a class="card-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'inkwell' ); ?> <?php echo inkwell_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
</article>
