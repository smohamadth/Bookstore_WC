<?php
/**
 * Language-pack management and storefront language switcher.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Languages whose theme interfaces are bundled with Inkwell.
 *
 * @return array
 */
function inkwell_supported_languages() {
	return array(
		'en_US' => array(
			'name'  => 'English',
			'native' => 'English',
			'short' => 'EN',
		),
		'fa_IR' => array(
			'name'  => 'Persian',
			'native' => 'فارسی',
			'short' => 'فا',
		),
		'ckb'   => array(
			'name'  => 'Sorani Kurdish',
			'native' => 'کوردی',
			'short' => 'کوردی',
		),
	);
}

/**
 * Apply the visitor's interface-language preference on the storefront.
 * Multilingual plugins take precedence because they also switch page content.
 */
function inkwell_apply_visitor_language() {
	if ( is_admin() || function_exists( 'pll_the_languages' ) || defined( 'ICL_SITEPRESS_VERSION' ) ) {
		return;
	}

	$supported = inkwell_supported_languages();
	$requested = isset( $_GET['inkwell_lang'] ) ? sanitize_text_field( wp_unslash( $_GET['inkwell_lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- harmless visitor preference.
	$stored    = isset( $_COOKIE['inkwell_language'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['inkwell_language'] ) ) : '';
	$locale    = isset( $supported[ $requested ] ) ? $requested : $stored;

	if ( $requested && isset( $supported[ $requested ] ) ) {
		setcookie(
			'inkwell_language',
			$requested,
			time() + YEAR_IN_SECONDS,
			defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
			defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			is_ssl(),
			true
		);
		$_COOKIE['inkwell_language'] = $requested;
		add_action( 'template_redirect', 'inkwell_clean_language_url', 1 );
	}

	if ( isset( $supported[ $locale ] ) && get_locale() !== $locale ) {
		$GLOBALS['inkwell_interface_locale'] = $locale;
		if ( ! switch_to_locale( $locale ) ) {
			global $wp_locale, $text_direction;
			if ( is_object( $wp_locale ) ) {
				$wp_locale->text_direction = 'rtl';
			}
			$text_direction = 'rtl';
			add_action( 'after_setup_theme', 'inkwell_load_bundled_interface_translation', 3 );
			add_filter( 'language_attributes', 'inkwell_interface_language_attributes', 20 );
		}
	}
}
add_action( 'after_setup_theme', 'inkwell_apply_visitor_language', 1 );

/**
 * Load the theme catalog even when the matching WordPress core pack has not yet
 * been installed. Core and WooCommerce strings still require their own packs.
 */
function inkwell_load_bundled_interface_translation() {
	$locale = isset( $GLOBALS['inkwell_interface_locale'] ) ? $GLOBALS['inkwell_interface_locale'] : '';
	$mofile = get_template_directory() . '/languages/' . $locale . '.mo';
	if ( $locale && file_exists( $mofile ) ) {
		load_theme_textdomain( 'inkwell', get_template_directory() . '/languages' );
		unload_textdomain( 'inkwell' );
		load_textdomain( 'inkwell', $mofile );
	}
}

/**
 * Current visitor-facing interface locale.
 *
 * @return string
 */
function inkwell_get_interface_locale() {
	return isset( $GLOBALS['inkwell_interface_locale'] ) ? $GLOBALS['inkwell_interface_locale'] : get_locale();
}

/**
 * Whether the selected Inkwell interface language is right-to-left.
 *
 * @return bool
 */
function inkwell_interface_is_rtl() {
	return in_array( inkwell_get_interface_locale(), array( 'fa_IR', 'ckb' ), true );
}

/**
 * Correct document language/direction when only the bundled theme catalog is
 * available and WordPress cannot perform a full locale switch yet.
 *
 * @param string $output Existing language attributes.
 * @return string
 */
function inkwell_interface_language_attributes( $output ) {
	$locale = inkwell_get_interface_locale();
	if ( ! in_array( $locale, array( 'fa_IR', 'ckb' ), true ) ) {
		return $output;
	}
	return 'dir="rtl" lang="' . esc_attr( str_replace( '_', '-', $locale ) ) . '"';
}

/**
 * Remove the language action parameter after its preference cookie is saved.
 */
function inkwell_clean_language_url() {
	$clean_url = remove_query_arg( 'inkwell_lang' );
	wp_safe_redirect( $clean_url );
	exit;
}

/**
 * Normalize built-in or multilingual-plugin languages for the selector.
 *
 * @return array
 */
function inkwell_language_options() {
	$options = array();

	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
		foreach ( (array) $languages as $language ) {
			$code      = isset( $language['slug'] ) ? $language['slug'] : '';
			$options[] = array(
				'locale'  => isset( $language['locale'] ) ? $language['locale'] : $code,
				'code'    => strtoupper( $code ),
				'name'    => isset( $language['name'] ) ? $language['name'] : $code,
				'native'  => isset( $language['name'] ) ? $language['name'] : $code,
				'url'     => isset( $language['url'] ) ? $language['url'] : home_url( '/' ),
				'current' => ! empty( $language['current_lang'] ),
				'rel'     => '',
			);
		}
		return $options;
	}

	if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		$languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
		foreach ( (array) $languages as $language ) {
			$code      = isset( $language['language_code'] ) ? $language['language_code'] : '';
			$options[] = array(
				'locale'  => $code,
				'code'    => strtoupper( $code ),
				'name'    => isset( $language['translated_name'] ) ? $language['translated_name'] : $code,
				'native'  => isset( $language['native_name'] ) ? $language['native_name'] : $code,
				'url'     => isset( $language['url'] ) ? $language['url'] : home_url( '/' ),
				'current' => ! empty( $language['active'] ),
				'rel'     => '',
			);
		}
		return $options;
	}

	$current = inkwell_get_interface_locale();
	foreach ( inkwell_supported_languages() as $locale => $language ) {
		$options[] = array(
			'locale'  => $locale,
			'code'    => 'en_US' === $locale ? 'EN' : ( 'fa_IR' === $locale ? 'FA' : 'CKB' ),
			'name'    => $language['name'],
			'native'  => $language['native'],
			'url'     => add_query_arg( 'inkwell_lang', $locale ),
			'current' => $locale === $current,
			'rel'     => 'nofollow',
		);
	}
	return $options;
}

/**
 * Render an accessible, content-aware language selector.
 */
function inkwell_language_switcher() {
	if ( get_theme_mod( 'inkwell_language_switcher_hide', false ) ) {
		return;
	}

	static $instance = 0;
	$instance++;
	$options = inkwell_language_options();
	if ( ! $options ) {
		return;
	}
	$current = $options[0];
	foreach ( $options as $option ) {
		if ( $option['current'] ) {
			$current = $option;
			break;
		}
	}
	$menu_id = 'inkwell-language-menu-' . $instance;
	?>
	<nav class="language-selector" data-language-selector aria-label="<?php esc_attr_e( 'Language', 'inkwell' ); ?>">
		<button class="language-selector__toggle" type="button" data-language-toggle aria-haspopup="true" aria-expanded="false" aria-controls="<?php echo esc_attr( $menu_id ); ?>">
			<?php echo inkwell_icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="language-selector__current" dir="auto"><?php echo esc_html( $current['native'] ); ?></span>
			<span class="language-selector__chevron"><?php echo inkwell_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</button>
		<div id="<?php echo esc_attr( $menu_id ); ?>" class="language-selector__menu" data-language-menu>
			<ul>
				<?php foreach ( $options as $option ) : ?>
					<?php $rtl = in_array( $option['locale'], array( 'fa_IR', 'fa', 'ckb' ), true ); ?>
					<li>
						<a class="language-selector__option<?php echo $option['current'] ? ' is-current' : ''; ?>" href="<?php echo esc_url( $option['url'] ); ?>" lang="<?php echo esc_attr( str_replace( '_', '-', $option['locale'] ) ); ?>" dir="<?php echo $rtl ? 'rtl' : 'ltr'; ?>"<?php echo $option['current'] ? ' aria-current="page"' : ''; ?><?php echo $option['rel'] ? ' rel="' . esc_attr( $option['rel'] ) . '"' : ''; ?>>
							<span class="language-selector__names">
								<strong><?php echo esc_html( $option['native'] ); ?></strong>
								<small><?php echo esc_html( $option['name'] . ' · ' . $option['code'] ); ?></small>
							</span>
							<?php if ( $option['current'] ) : ?>
								<span class="language-selector__check"><?php echo inkwell_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</nav>
	<?php
}

/**
 * Register the bundled-language administration page.
 */
function inkwell_languages_admin_menu() {
	add_theme_page(
		__( 'Inkwell Languages', 'inkwell' ),
		__( 'Inkwell Languages', 'inkwell' ),
		'install_languages',
		'inkwell-languages',
		'inkwell_languages_admin_page'
	);
}
add_action( 'admin_menu', 'inkwell_languages_admin_menu' );

/**
 * Point administrators to the core-pack installer while bundled packs are
 * missing. The notice does not change the site's active language.
 */
function inkwell_languages_admin_notice() {
	if ( ! current_user_can( 'install_languages' ) ) {
		return;
	}
	$installed = get_available_languages();
	if ( in_array( 'fa_IR', $installed, true ) && in_array( 'ckb', $installed, true ) ) {
		return;
	}
	?>
	<div class="notice notice-info"><p>
		<strong><?php esc_html_e( 'Inkwell language setup:', 'inkwell' ); ?></strong>
		<?php esc_html_e( 'Install the Persian and Sorani WordPress packs to translate core and WooCommerce strings as well as the theme.', 'inkwell' ); ?>
		<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'themes.php?page=inkwell-languages' ) ); ?>"><?php esc_html_e( 'Manage languages', 'inkwell' ); ?></a>
	</p></div>
	<?php
}
add_action( 'admin_notices', 'inkwell_languages_admin_notice' );

/**
 * Render language installation and activation controls.
 */
function inkwell_languages_admin_page() {
	if ( ! current_user_can( 'install_languages' ) ) {
		return;
	}
	$installed = array_merge( array( 'en_US' ), get_available_languages() );
	$active    = get_option( 'WPLANG' ) ?: 'en_US';
	$status    = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only status.
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Inkwell Languages', 'inkwell' ); ?></h1>
		<p><?php esc_html_e( 'Install the matching WordPress core pack, then activate a site-wide language or use the storefront switcher.', 'inkwell' ); ?></p>
		<?php if ( 'installed' === $status ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Language pack installed.', 'inkwell' ); ?></p></div>
		<?php elseif ( 'activated' === $status ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Site language activated.', 'inkwell' ); ?></p></div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'The WordPress language pack could not be installed. Check filesystem permissions and outbound connections.', 'inkwell' ); ?></p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<thead><tr><th scope="col"><?php esc_html_e( 'Language', 'inkwell' ); ?></th><th scope="col"><?php esc_html_e( 'Theme interface', 'inkwell' ); ?></th><th scope="col"><?php esc_html_e( 'WordPress core pack', 'inkwell' ); ?></th><th scope="col"><?php esc_html_e( 'Actions', 'inkwell' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( inkwell_supported_languages() as $locale => $language ) : ?>
				<?php $is_installed = in_array( $locale, $installed, true ); ?>
				<tr>
					<td><strong><?php echo esc_html( $language['native'] ); ?></strong><br /><code><?php echo esc_html( $locale ); ?></code></td>
					<td><?php esc_html_e( 'Bundled', 'inkwell' ); ?></td>
					<td><?php echo $is_installed ? esc_html__( 'Installed', 'inkwell' ) : esc_html__( 'Not installed', 'inkwell' ); ?></td>
					<td>
						<?php if ( $locale === $active ) : ?>
							<strong><?php esc_html_e( 'Active', 'inkwell' ); ?></strong>
						<?php else : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
								<input type="hidden" name="action" value="inkwell_manage_language" />
								<input type="hidden" name="locale" value="<?php echo esc_attr( $locale ); ?>" />
								<input type="hidden" name="mode" value="activate" />
								<?php wp_nonce_field( 'inkwell_manage_language' ); ?>
								<button class="button button-primary" type="submit"><?php esc_html_e( 'Use as site language', 'inkwell' ); ?></button>
							</form>
						<?php endif; ?>
						<?php if ( ! $is_installed && 'en_US' !== $locale ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
								<input type="hidden" name="action" value="inkwell_manage_language" />
								<input type="hidden" name="locale" value="<?php echo esc_attr( $locale ); ?>" />
								<input type="hidden" name="mode" value="install" />
								<?php wp_nonce_field( 'inkwell_manage_language' ); ?>
								<button class="button" type="submit"><?php esc_html_e( 'Install core language', 'inkwell' ); ?></button>
							</form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description"><?php esc_html_e( 'For translated products and pages with language-specific URLs, install Polylang, WPML, or TranslatePress.', 'inkwell' ); ?></p>
	</div>
	<?php
}

/**
 * Install or activate a supported core language pack.
 */
function inkwell_manage_language() {
	if ( ! current_user_can( 'install_languages' ) ) {
		wp_die( esc_html__( 'You are not allowed to install languages.', 'inkwell' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'inkwell_manage_language' );
	$locale    = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';
	$mode      = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
	$supported = inkwell_supported_languages();
	$status    = 'error';

	if ( isset( $supported[ $locale ] ) ) {
		$installed = 'en_US' === $locale || in_array( $locale, get_available_languages(), true );
		if ( ! $installed ) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			$installed = (bool) wp_download_language_pack( $locale );
		}
		if ( $installed && 'activate' === $mode ) {
			update_option( 'WPLANG', 'en_US' === $locale ? '' : $locale );
			delete_site_transient( 'available_translations' );
			$status = 'activated';
		} elseif ( $installed ) {
			$status = 'installed';
		}
	}

	wp_safe_redirect( add_query_arg( 'status', $status, admin_url( 'themes.php?page=inkwell-languages' ) ) );
	exit;
}
add_action( 'admin_post_inkwell_manage_language', 'inkwell_manage_language' );
