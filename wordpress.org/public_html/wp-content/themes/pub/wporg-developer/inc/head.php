<?php
/**
 * HTML head markup and customizations.
 *
 * @package wporg-developer
 */

/**
 * Class to handle HTML head markup.
 */
class DevHub_Head {

	/**
	 * Initializes module.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'do_init' ) );
	}

	/**
	 * Handles adding/removing hooks as needed.
	 */
	public static function do_init() {
		add_action( 'wp_head', array( __CLASS__, 'output_head_tags' ), 2 );
	}

	/**
	 * Outputs tags for the page head.
	 */
	public static function output_head_tags() {
		$fields = [
			// FYI: 'description' and 'og:description' are set further down.
			'og:title'       => wp_get_document_title(),
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:type'        => 'website',
			'og:url'         => home_url( '/' ),
			'twitter:card'   => 'summary_large_image',
			'twitter:site'   => '@WordPress',
		];

		$desc = '';

		// Customize description and any other tags.
		if ( is_front_page() ) {
			$desc = __( 'Official WordPress developer resources including a code reference, handbooks (for APIs, plugin and theme development, block editor), and more.', 'wporg' );
		}
		elseif ( is_page( 'reference' ) ) {
			$desc = __( 'Want to know what&#8217;s going on inside WordPress? Find out more information about its functions, classes, methods, and hooks.', 'wporg' );
		}
		elseif ( DevHub\is_parsed_post_type() ) {
			if ( is_singular() ) {
				$desc = DevHub\get_summary();
			}
			elseif ( is_post_type_archive() ) {
				$post_type_items = get_post_type_object( get_post_type() )->labels->all_items;
				/* translators: %s: translated label for all items of a post type. */
				$desc = sprintf( __( 'Code Reference archive for WordPress %s.', 'wporg' ), strtolower( $post_type_items ) );
			}
		}
		elseif ( is_singular() ) {
			$post = get_queried_object();
			$desc = apply_filters( 'the_content', $post->post_content, $post->ID );
		}

		// Actually set field values for description.
		if ( $desc ) {
			$desc = wp_strip_all_tags( $desc );
			$desc = str_replace( '&nbsp;', ' ', $desc );
			$desc = preg_replace( '/\s+/', ' ', $desc );

			// Trim down to <150 characters based on full words.
			if ( strlen( $desc ) > 150 ) {
				$truncated = '';
				$words = preg_split( "/[\n\r\t ]+/", $desc, -1, PREG_SPLIT_NO_EMPTY );

				while ( $words ) {
					$word = array_shift( $words );
					if ( strlen( $truncated ) + strlen( $word ) >= 141 ) { /* 150 - strlen( ' &hellip;' ) */
						break;
					}

					$truncated .= $word . ' ';
				}

				$truncated = trim( $truncated );

				if ( $words ) {
					$truncated .= '&hellip;';
				}

				$desc = $truncated;
			}

			$fields[ 'description' ]   = $desc;
			$fields[ 'og:description'] = $desc;
		}

		// Output fields.
		foreach ( $fields as $property => $content ) {
			$attribute = 0 === strpos( $property, 'og:' ) ? 'property' : 'name';
			printf(
				'<meta %s="%s" content="%s" />' . "\n",
				esc_attr( $attribute ),
				esc_attr( $property ),
				esc_attr( $content )
			);
		}
	}

} // DevHub_Head

DevHub_Head::init();
