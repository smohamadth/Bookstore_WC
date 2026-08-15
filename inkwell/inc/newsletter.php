<?php
/**
 * Newsletter: shortcode + AJAX endpoint + admin-post fallback.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store a subscriber email. Returns array( bool $ok, string $message ).
 *
 * @param string $email Email address.
 * @return array
 */
function inkwell_newsletter_subscribe( $email ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return array( false, __( 'Please enter a valid email address.', 'inkwell' ) );
	}

	$subscribers = get_option( 'inkwell_subscribers', array() );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}

	$key = strtolower( $email );
	if ( isset( $subscribers[ $key ] ) ) {
		return array( true, __( 'You are already subscribed — thank you!', 'inkwell' ) );
	}

	$subscribers[ $key ] = array(
		'email' => $email,
		'time'  => current_time( 'mysql' ),
	);
	update_option( 'inkwell_subscribers', $subscribers, false );

	/**
	 * Fires when a new subscriber is added — hook a mail service here.
	 *
	 * @param string $email Subscriber email.
	 */
	do_action( 'inkwell_newsletter_subscribed', $email );

	return array( true, __( 'Welcome to the reading list! Check your inbox to confirm.', 'inkwell' ) );
}

/**
 * AJAX handler.
 */
function inkwell_newsletter_ajax() {
	check_ajax_referer( 'inkwell_newsletter', 'nonce' );

	if ( ! empty( $_POST['website'] ) ) { // Honeypot.
		wp_send_json_success( array( 'message' => __( 'Thank you!', 'inkwell' ) ) );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$result = inkwell_newsletter_subscribe( $email );

	if ( $result[0] ) {
		wp_send_json_success( array( 'message' => $result[1] ) );
	}
	wp_send_json_error( array( 'message' => $result[1] ), 400 );
}
add_action( 'wp_ajax_inkwell_newsletter', 'inkwell_newsletter_ajax' );
add_action( 'wp_ajax_nopriv_inkwell_newsletter', 'inkwell_newsletter_ajax' );

/**
 * No-JS fallback via admin-post.
 */
function inkwell_newsletter_form_handler() {
	if ( ! isset( $_POST['inkwell_newsletter_submit'] ) ) {
		return;
	}
	check_admin_referer( 'inkwell_newsletter_form', 'inkwell_nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	inkwell_newsletter_subscribe( $email );

	$back = wp_get_referer();
	if ( ! $back ) {
		$back = home_url( '/' );
	}
	wp_safe_redirect( add_query_arg( 'subscribed', '1', $back ) );
	exit;
}
add_action( 'admin_post_inkwell_newsletter_form', 'inkwell_newsletter_form_handler' );
add_action( 'admin_post_nopriv_inkwell_newsletter_form', 'inkwell_newsletter_form_handler' );

/**
 * Render the newsletter form.
 *
 * @return string
 */
function inkwell_newsletter_form() {
	ob_start();
	$nonce = wp_create_nonce( 'inkwell_newsletter_form' );
	$notice = isset( $_GET['subscribed'] ) ? __( 'Thank you — you are on the list!', 'inkwell' ) : '';
	?>
	<form class="newsletter-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-inkwell-newsletter novalidate>
		<input type="hidden" name="action" value="inkwell_newsletter_form" />
		<input type="hidden" name="inkwell_nonce" value="<?php echo esc_attr( $nonce ); ?>" />
		<span class="hp-field" aria-hidden="true">
			<label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off" /></label>
		</span>
		<label class="screen-reader-text" for="inkwell-newsletter-email"><?php esc_html_e( 'Email address', 'inkwell' ); ?></label>
		<input type="email" id="inkwell-newsletter-email" name="email" placeholder="<?php esc_attr_e( 'you@example.com', 'inkwell' ); ?>" required />
		<button type="submit" class="button button--light"><?php esc_html_e( 'Subscribe', 'inkwell' ); ?></button>
		<p class="newsletter-note" role="status" aria-live="polite"><?php echo esc_html( $notice ); ?></p>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode: [inkwell_newsletter]
 *
 * @return string
 */
function inkwell_newsletter_shortcode() {
	return inkwell_newsletter_form();
}
add_shortcode( 'inkwell_newsletter', 'inkwell_newsletter_shortcode' );
