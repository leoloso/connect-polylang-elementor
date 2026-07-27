<?php

defined( 'ABSPATH' ) || exit;


/**
 * Is Elementor (free) plugin active or not?
 *
 * @since  1.0.0
 *
 * @return bool TRUE if plugin is active, FALSE otherwise.
 */
function cpel_is_elementor_active() {

	return defined( 'ELEMENTOR_VERSION' );

}

/**
 * Check Elementor version
 *
 * @since 2.0.0
 *
 * @param string $ver version to compare.
 * @return bool
 */
function cpel_elementor_min_version( $ver ) {

	return cpel_is_elementor_active() && version_compare( ELEMENTOR_VERSION, $ver, '>=' );

}

/**
 * Is Elementor Pro plugin active or not?
 *
 * @since  1.0.0
 *
 * @return bool TRUE if plugin is active, FALSE otherwise.
 */
function cpel_is_elementor_pro_active() {

	return defined( 'ELEMENTOR_PRO_VERSION' );

}

/**
 * Is Polylang (free) OR Polylang Pro (Premium) plugin active or not?
 *   Note: This is for checking the base Polylang functionality which is
 *         identical in free and Pro version.
 *
 * @since  1.0.0
 *
 * @return bool TRUE if plugin is active, FALSE otherwise.
 */
function cpel_is_polylang_active() {

	return defined( 'POLYLANG_BASENAME' );

}

/**
 * Is Polylang Pro (Premium) plugin active or not?
 *
 * @since  1.0.0
 *
 * @return bool TRUE if plugin is active, FALSE otherwise.
 */
function cpel_is_polylang_pro_active() {

	return defined( 'POLYLANG_PRO' );

}

/**
 * Is Polylang API active
 *
 * @since  2.0.8
 *
 * @return bool TRUE if plugin is active, FALSE otherwise.
 */
function cpel_is_polylang_api_active() {

	return cpel_is_polylang_active() && function_exists( 'pll_get_post' );

}

/**
 * Admin notice shown when a required plugin is missing.
 *
 * Polylang is not declared in the "Requires Plugins" plugin header because that
 * header matches plugin folder names and offers no "or" syntax: declaring
 * "polylang" makes WordPress block activation on sites running Polylang Pro,
 * which lives in "polylang-pro" and cannot be active alongside the free version.
 * Requirements are therefore reported here instead.
 *
 * Also covers WordPress < 6.5, where the "Requires Plugins" header is ignored
 * altogether and the plugin would otherwise stay silently inactive.
 *
 * @since  2.6.1
 *
 * @return void
 */
function cpel_missing_requirements_notice() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$missing = array();

	if ( ! cpel_is_polylang_api_active() ) {
		$missing[] = __( 'Polylang (or Polylang Pro)', 'connect-polylang-elementor' );
	}

	if ( ! cpel_is_elementor_active() ) {
		$missing[] = __( 'Elementor', 'connect-polylang-elementor' );
	}

	if ( empty( $missing ) ) {
		return;
	}

	$message = sprintf(
		/* translators: %s: list of required plugin names. */
		__( '<strong>Connect Polylang for Elementor</strong> requires %s to be installed and activated.', 'connect-polylang-elementor' ),
		esc_html( wp_sprintf_l( '%l', $missing ) )
	);

	$message .= ' ' . sprintf(
		'<a href="%s">%s</a>',
		esc_url( is_network_admin() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' ) ),
		esc_html__( 'Manage plugins', 'connect-polylang-elementor' )
	);

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		wp_kses(
			$message,
			array(
				'strong' => array(),
				'a'      => array( 'href' => array() ),
			)
		)
	);

}

/**
 * Is Polylang Multidomain
 *
 * @since  2.1.1
 *
 * @return bool TRUE if is polylang multi-domain configuration, FALSE otherwise.
 */
function cpel_is_polylang_multidomain() {

	return cpel_is_polylang_api_active() && 3 === PLL()->options['force_lang'] && ! empty( PLL()->options['domains'] );

}

/**
 * Is Elementor Editor
 *
 * @since  2.3.0
 *
 * @return bool TRUE if is Elementor Editor, FALSE otherwise.
 */
function cpel_is_elementor_editor() {

	return is_admin()
		&& isset( $_GET['action'], $_GET['post'] )
		&& 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) )
		&& absint( $_GET['post'] ) > 0;

}

/**
 * Is post a translation in secondary language
 *
 * @since  2.0.0
 *
 * @return bool TRUE if is a translation, FALSE otherwise.
 */
function cpel_is_translation( $post_id = null ) {

	$post_id = $post_id ? $post_id : get_the_ID();
	$default = pll_default_language();

	return pll_get_post_language( $post_id ) !== $default && pll_get_post( $post_id, $default );

}

/**
 * Flag code
 *
 * @since 2.0.0
 * @since 2.0.5 don't return code for custom flags
 *
 * @param  string $flag_url full flag url.
 * @return string|bool  flag code or false
 */
function cpel_flag_code( $flag_url ) {

	return preg_match( '/polylang\/flags\/(\w+).(?:jpg|png|svg)$/i', $flag_url, $matchs ) ? $matchs[1] : false;

}

/**
 * SVG flag info
 *
 * @since 2.0.0
 *
 * @param  string $flag_code flag two letters code.
 * @return array|bool  SVG flag info or false
 */
function cpel_flag_svg( $flag_code ) {

	$flag_path = "/assets/flags/$flag_code.svg";

	if ( file_exists( CPEL_DIR . $flag_path ) ) {
		return array(
			'path' => $flag_path,
			'url'  => plugins_url( $flag_path, CPEL_FILE ),
		);
	}

	return false;

}

/**
 * Flag emoji
 *
 * @since 2.4.0
 *
 * @param  string $flag_code flag code.
 * @return string|false  flag emoji or false
 */
function cpel_flag_emoji( $flag_code ) {

	$emojis = include __DIR__ . '/util/emojis.php';

	return apply_filters( 'cpel/filter/flag_emoji', isset( $emojis[ $flag_code ] ) ? $emojis[ $flag_code ] : false, $flag_code );

}
