<?php
/**
 * Newsletter: shortcode, privacy-aware local storage, AJAX and no-JS fallback.
 * For larger lists, connect an email service through
 * `inkwell_newsletter_subscribed` and avoid local storage.
 *
 * @package Inkwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit public newsletter writes per IP without storing the raw address.
 *
 * @return bool True when the request is allowed.
 */
function inkwell_newsletter_rate_limit() {
	$address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key     = 'inkwell_nl_' . hash_hmac( 'sha256', $address, wp_salt( 'nonce' ) );
	$count   = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return false;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * Store a subscriber email. Returns array( bool $ok, string $message ).
 *
 * @param string $email   Email address.
 * @param bool   $consent Whether explicit consent was supplied.
 * @return array
 */
function inkwell_newsletter_subscribe( $email, $consent = false ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return array( false, __( 'Please enter a valid email address.', 'inkwell-books' ) );
	}
	if ( ! $consent ) {
		return array( false, __( 'Please agree to receive the newsletter.', 'inkwell-books' ) );
	}

	$subscribers = get_option( 'inkwell_subscribers', array() );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}

	$key = strtolower( $email );
	if ( isset( $subscribers[ $key ] ) ) {
		return array( true, __( 'You are already subscribed — thank you!', 'inkwell-books' ) );
	}

	/**
	 * Filters the maximum size of the built-in local list. Larger stores should
	 * connect a dedicated email provider through the subscription action.
	 *
	 * @param int $limit Maximum local subscribers.
	 */
	$limit = max( 1, (int) apply_filters( 'inkwell_newsletter_local_limit', 1000 ) );
	if ( count( $subscribers ) >= $limit ) {
		return array( false, __( 'The local reading list is full. Please contact the store owner.', 'inkwell-books' ) );
	}

	$subscribers[ $key ] = array(
		'email'   => $email,
		'time'    => current_time( 'mysql', true ),
		'consent' => true,
	);
	update_option( 'inkwell_subscribers', $subscribers, false );

	/**
	 * Fires when a new subscriber is added — hook a mail service here.
	 *
	 * @param string $email Subscriber email.
	 */
	do_action( 'inkwell_newsletter_subscribed', $email );

	return array( true, __( 'Welcome to the reading list!', 'inkwell-books' ) );
}

/**
 * Remove a locally stored subscriber.
 *
 * @param string $email Email address.
 * @return bool
 */
function inkwell_newsletter_unsubscribe( $email ) {
	$email       = sanitize_email( $email );
	$subscribers = get_option( 'inkwell_subscribers', array() );
	$key         = strtolower( $email );
	if ( ! is_email( $email ) || ! is_array( $subscribers ) || ! isset( $subscribers[ $key ] ) ) {
		return false;
	}
	unset( $subscribers[ $key ] );
	update_option( 'inkwell_subscribers', $subscribers, false );
	do_action( 'inkwell_newsletter_unsubscribed', $email );
	return true;
}

/**
 * AJAX subscribe handler.
 */
function inkwell_newsletter_ajax() {
	check_ajax_referer( 'inkwell_newsletter', 'nonce' );

	$website = isset( $_POST['website'] ) ? sanitize_text_field( wp_unslash( $_POST['website'] ) ) : '';
	if ( '' !== $website ) { // Honeypot.
		wp_send_json_success( array( 'message' => __( 'Thank you!', 'inkwell-books' ) ) );
	}
	if ( ! inkwell_newsletter_rate_limit() ) {
		wp_send_json_error( array( 'message' => __( 'Too many attempts. Please try again later.', 'inkwell-books' ) ), 429 );
	}

	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$consent = isset( $_POST['consent'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['consent'] ) );
	$result  = inkwell_newsletter_subscribe( $email, $consent );

	if ( $result[0] ) {
		wp_send_json_success( array( 'message' => $result[1] ) );
	}
	wp_send_json_error( array( 'message' => $result[1] ), 400 );
}
add_action( 'wp_ajax_inkwell_newsletter', 'inkwell_newsletter_ajax' );
add_action( 'wp_ajax_nopriv_inkwell_newsletter', 'inkwell_newsletter_ajax' );

/**
 * No-JS subscribe fallback via admin-post.
 */
function inkwell_newsletter_form_handler() {
	if ( ! isset( $_POST['inkwell_newsletter_submit'] ) ) {
		return;
	}
	check_admin_referer( 'inkwell_newsletter_form', 'inkwell_nonce' );

	$back    = wp_get_referer() ?: home_url( '/' );
	$website = isset( $_POST['website'] ) ? sanitize_text_field( wp_unslash( $_POST['website'] ) ) : '';
	if ( '' !== $website ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'success', $back ) );
		exit;
	}
	if ( ! inkwell_newsletter_rate_limit() ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'rate-limited', $back ) );
		exit;
	}

	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$consent = isset( $_POST['consent'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['consent'] ) );
	$result  = inkwell_newsletter_subscribe( $email, $consent );
	$status  = $result[0] ? 'success' : 'error';

	wp_safe_redirect( add_query_arg( 'newsletter', $status, $back ) );
	exit;
}
add_action( 'admin_post_inkwell_newsletter_form', 'inkwell_newsletter_form_handler' );
add_action( 'admin_post_nopriv_inkwell_newsletter_form', 'inkwell_newsletter_form_handler' );

/**
 * No-JS unsubscribe handler.
 */
function inkwell_newsletter_unsubscribe_handler() {
	check_admin_referer( 'inkwell_newsletter_unsubscribe', 'inkwell_unsubscribe_nonce' );
	$back = wp_get_referer() ?: home_url( '/' );
	if ( ! inkwell_newsletter_rate_limit() ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'rate-limited', $back ) );
		exit;
	}
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	inkwell_newsletter_unsubscribe( $email );
	// Use one response to avoid exposing whether an address was subscribed.
	$status = 'removed';
	wp_safe_redirect( add_query_arg( 'newsletter', $status, $back ) );
	exit;
}
add_action( 'admin_post_inkwell_newsletter_unsubscribe', 'inkwell_newsletter_unsubscribe_handler' );
add_action( 'admin_post_nopriv_inkwell_newsletter_unsubscribe', 'inkwell_newsletter_unsubscribe_handler' );

/**
 * Message for redirected no-JS requests.
 *
 * @return string
 */
function inkwell_newsletter_notice() {
	$status = isset( $_GET['newsletter'] ) ? sanitize_key( wp_unslash( $_GET['newsletter'] ) ) : '';
	$messages = array(
		'success'      => __( 'Thank you — you are on the list!', 'inkwell-books' ),
		'error'        => __( 'Please check your email and consent, then try again.', 'inkwell-books' ),
		'rate-limited' => __( 'Too many attempts. Please try again later.', 'inkwell-books' ),
		'removed'      => __( 'If that address was subscribed, it has been removed from the reading list.', 'inkwell-books' ),
	);
	return isset( $messages[ $status ] ) ? $messages[ $status ] : '';
}

/**
 * Render the newsletter form.
 *
 * @return string
 */
function inkwell_newsletter_form() {
	static $instance = 0;
	$instance++;
	$id      = 'inkwell-newsletter-email-' . $instance;
	$consent = 'inkwell-newsletter-consent-' . $instance;
	$notice  = inkwell_newsletter_notice();
	$privacy = get_privacy_policy_url();

	ob_start();
	?>
	<form class="newsletter-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-inkwell-newsletter>
		<input type="hidden" name="action" value="inkwell_newsletter_form" />
		<?php wp_nonce_field( 'inkwell_newsletter_form', 'inkwell_nonce' ); ?>
		<span class="hp-field" aria-hidden="true">
			<label><?php esc_html_e( 'Leave this field empty', 'inkwell-books' ); ?><input type="text" name="website" tabindex="-1" autocomplete="off" /></label>
		</span>
		<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Email address', 'inkwell-books' ); ?></label>
		<input type="email" id="<?php echo esc_attr( $id ); ?>" name="email" placeholder="<?php esc_attr_e( 'you@example.com', 'inkwell-books' ); ?>" autocomplete="email" required />
		<button type="submit" name="inkwell_newsletter_submit" value="1" class="button button--light"><?php esc_html_e( 'Subscribe', 'inkwell-books' ); ?></button>
		<label class="newsletter-consent" for="<?php echo esc_attr( $consent ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $consent ); ?>" name="consent" value="1" required />
			<span>
				<?php esc_html_e( 'I agree to receive the reading-list newsletter and can unsubscribe at any time.', 'inkwell-books' ); ?>
				<?php if ( $privacy ) : ?>
					<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Privacy policy', 'inkwell-books' ); ?></a>
				<?php endif; ?>
			</span>
		</label>
		<p class="newsletter-note" role="status" aria-live="polite"><?php echo esc_html( $notice ); ?></p>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Render an unsubscribe form.
 *
 * @return string
 */
function inkwell_newsletter_unsubscribe_form() {
	ob_start();
	?>
	<form class="newsletter-unsubscribe-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="inkwell_newsletter_unsubscribe" />
		<?php wp_nonce_field( 'inkwell_newsletter_unsubscribe', 'inkwell_unsubscribe_nonce' ); ?>
		<label><?php esc_html_e( 'Email address', 'inkwell-books' ); ?> <input type="email" name="email" required autocomplete="email" /></label>
		<button type="submit" class="button"><?php esc_html_e( 'Unsubscribe', 'inkwell-books' ); ?></button>
		<p class="newsletter-note" role="status" aria-live="polite"><?php echo esc_html( inkwell_newsletter_notice() ); ?></p>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Shortcodes.
 *
 * @return string
 */
function inkwell_newsletter_shortcode() {
	return inkwell_newsletter_form();
}
add_shortcode( 'inkwell_newsletter', 'inkwell_newsletter_shortcode' );
add_shortcode( 'inkwell_newsletter_unsubscribe', 'inkwell_newsletter_unsubscribe_form' );

/**
 * Register a small, capability-protected subscriber management screen.
 */
function inkwell_newsletter_admin_menu() {
	add_management_page(
		__( 'Reading List', 'inkwell-books' ),
		__( 'Reading List', 'inkwell-books' ),
		'manage_options',
		'inkwell-reading-list',
		'inkwell_newsletter_admin_page'
	);
}
add_action( 'admin_menu', 'inkwell_newsletter_admin_menu' );

/**
 * Render subscriber management.
 */
function inkwell_newsletter_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$subscribers = get_option( 'inkwell_subscribers', array() );
	$subscribers = is_array( $subscribers ) ? $subscribers : array();
	ksort( $subscribers );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Reading List', 'inkwell-books' ); ?></h1>
		<p><?php echo esc_html( sprintf( __( '%d locally stored subscribers.', 'inkwell-books' ), count( $subscribers ) ) ); ?></p>
		<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=inkwell_newsletter_export' ), 'inkwell_newsletter_export' ) ); ?>"><?php esc_html_e( 'Export CSV', 'inkwell-books' ); ?></a></p>
		<table class="widefat striped">
			<thead><tr><th scope="col"><?php esc_html_e( 'Email address', 'inkwell-books' ); ?></th><th scope="col"><?php esc_html_e( 'Subscribed at (UTC)', 'inkwell-books' ); ?></th><th scope="col"><?php esc_html_e( 'Consent recorded', 'inkwell-books' ); ?></th></tr></thead>
			<tbody>
			<?php if ( ! $subscribers ) : ?>
				<tr><td colspan="3"><?php esc_html_e( 'No local subscribers.', 'inkwell-books' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $subscribers as $record ) : ?>
					<tr>
						<td><?php echo esc_html( isset( $record['email'] ) ? $record['email'] : '' ); ?></td>
						<td><?php echo esc_html( isset( $record['time'] ) ? $record['time'] : '' ); ?></td>
						<td><?php echo ! empty( $record['consent'] ) ? esc_html__( 'Yes', 'inkwell-books' ) : esc_html__( 'Legacy record', 'inkwell-books' ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Export local subscribers as CSV.
 */
function inkwell_newsletter_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to export this data.', 'inkwell-books' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'inkwell_newsletter_export' );
	$subscribers = get_option( 'inkwell_subscribers', array() );
	$subscribers = is_array( $subscribers ) ? $subscribers : array();

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=inkwell-reading-list.csv' );
	$output = fopen( 'php://output', 'w' );
	if ( false === $output ) {
		wp_die( esc_html__( 'Could not create the export.', 'inkwell-books' ) );
	}
	fputcsv( $output, array( 'email', 'subscribed_at_utc', 'consent' ) );
	foreach ( $subscribers as $record ) {
		fputcsv(
			$output,
			array(
				isset( $record['email'] ) ? $record['email'] : '',
				isset( $record['time'] ) ? $record['time'] : '',
				! empty( $record['consent'] ) ? 'yes' : 'legacy',
			)
		);
	}
	fclose( $output );
	exit;
}
add_action( 'admin_post_inkwell_newsletter_export', 'inkwell_newsletter_export' );

/**
 * Export locally stored newsletter data through WordPress privacy tools.
 *
 * @param string $email Email being exported.
 * @return array
 */
function inkwell_newsletter_personal_data_exporter( $email ) {
	$subscribers = get_option( 'inkwell_subscribers', array() );
	$key         = strtolower( sanitize_email( $email ) );
	$data        = array();
	if ( is_array( $subscribers ) && isset( $subscribers[ $key ] ) ) {
		$record = $subscribers[ $key ];
		$data[] = array(
			'group_id'    => 'inkwell-newsletter',
			'group_label' => __( 'Reading-list newsletter', 'inkwell-books' ),
			'item_id'     => 'subscriber-' . hash( 'sha256', $key ),
			'data'        => array(
				array( 'name' => __( 'Email address', 'inkwell-books' ), 'value' => $record['email'] ),
				array( 'name' => __( 'Subscribed at', 'inkwell-books' ), 'value' => isset( $record['time'] ) ? $record['time'] : '' ),
			),
		);
	}
	return array( 'data' => $data, 'done' => true );
}

/**
 * Erase locally stored newsletter data through WordPress privacy tools.
 *
 * @param string $email Email being erased.
 * @return array
 */
function inkwell_newsletter_personal_data_eraser( $email ) {
	$removed = inkwell_newsletter_unsubscribe( $email );
	return array(
		'items_removed'  => $removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * Register privacy exporters and erasers.
 */
function inkwell_newsletter_register_privacy_tools() {
	add_filter(
		'wp_privacy_personal_data_exporters',
		function ( $exporters ) {
			$exporters['inkwell-newsletter'] = array(
				'exporter_friendly_name' => __( 'Inkwell newsletter', 'inkwell-books' ),
				'callback'               => 'inkwell_newsletter_personal_data_exporter',
			);
			return $exporters;
		}
	);
	add_filter(
		'wp_privacy_personal_data_erasers',
		function ( $erasers ) {
			$erasers['inkwell-newsletter'] = array(
				'eraser_friendly_name' => __( 'Inkwell newsletter', 'inkwell-books' ),
				'callback'             => 'inkwell_newsletter_personal_data_eraser',
			);
			return $erasers;
		}
	);
}
add_action( 'init', 'inkwell_newsletter_register_privacy_tools' );
