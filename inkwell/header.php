<?php
/**
 * Site header: announcement bar, sticky header, nav, search, mini-cart.
 *
 * @package Inkwell
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<script>document.documentElement.classList.add('inkwell-js');</script>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'inkwell' ); ?></a>

<div id="page" class="site">

	<?php
	$announcement = inkwell_mod( 'inkwell_announcement', '' );
	if ( $announcement ) :
		?>
		<div class="announcement-bar"><?php echo wp_kses_post( $announcement ); ?></div>
	<?php endif; ?>

	<header id="masthead" class="site-header" data-header>
		<div class="container header-inner">

				<button class="menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu">
					<span class="screen-reader-text" data-menu-label><?php esc_html_e( 'Open menu', 'inkwell' ); ?></span>
				<span data-icon-open><?php echo inkwell_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<span data-icon-close style="display:none"><?php echo inkwell_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</button>

			<div class="site-branding">
				<?php inkwell_logo(); ?>
			</div>

			<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'inkwell' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => 'inkwell_menu_fallback',
					)
				);
				?>
			</nav>

			<div class="header-actions">
				<?php inkwell_language_switcher(); ?>
				<button class="header-action" data-search-toggle aria-expanded="false" aria-controls="search-panel">
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'inkwell' ); ?></span>
					<?php echo inkwell_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
				<?php inkwell_account_link(); ?>

				<?php if ( function_exists( 'WC' ) && function_exists( 'woocommerce_mini_cart' ) ) : ?>
					<div class="cart-toggle-wrap" data-cart-wrap>
						<?php inkwell_cart_link(); ?>
						<div id="inkwell-mini-cart" class="mini-cart-panel" role="region" aria-label="<?php esc_attr_e( 'Shopping cart', 'inkwell' ); ?>">
							<div class="widget_shopping_cart_content">
								<?php woocommerce_mini_cart(); ?>
							</div>
						</div>
					</div>
				<?php else : ?>
					<?php inkwell_cart_link(); ?>
				<?php endif; ?>
			</div>

		</div>

		<div id="search-panel" class="search-panel">
			<div class="container">
				<?php get_search_form(); ?>
			</div>
		</div>

		<nav id="mobile-menu" class="mobile-menu" aria-label="<?php esc_attr_e( 'Mobile', 'inkwell' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => 'inkwell_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
			<div class="mobile-language-switcher"><?php inkwell_language_switcher(); ?></div>
			<div class="mobile-menu-actions">
				<?php inkwell_cart_total(); ?>
				<a class="button button--ghost" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>">
					<?php esc_html_e( 'My account', 'inkwell' ); ?>
				</a>
			</div>
		</nav>
	</header>

	<main id="primary" class="site-main">
