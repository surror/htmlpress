<?php
/**
 * Shortcode presentation
 *
 * @since 0.1.0
 * @package HTMLPress
 */

if ( ! class_exists( 'HTMLPress_Shortcodes' ) ) :

	/**
	 * Shortcodes
	 */
	class HTMLPress_Shortcodes {

		/**
		 * Instance
		 *
		 * @since 0.1.0
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new HTMLPress_Shortcodes();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_shortcode( 'htmlpress' , array( $this, 'module' ) );
			add_shortcode( 'htmlpress-user' , array( $this, 'user' ) );
		}

		/**
		 * Shortcode
		 *
		 * How to use shortcode?
		 * [htmlpress module="{ID}"]
		 *
		 * @since 0.1.0
		 *
		 * @param  array $atts Shotcode attributes.
		 * @return mixed       Shortcode markup.
		 */
		function module( $atts ) {

			$atts = shortcode_atts( array(
				'module' => '',
			), $atts, 'htmlpress' );

			if ( empty( $atts['module'] ) ) {
				return '';
			}

			$post_id = absint( $atts['module'] );

			$template_html = HTMLPress_Helper::get_instance()->get_post_file_contents( $post_id, 'template_file_html_url' );

			// Enqueue assets if exist.
			HTMLPress_Helper::get_instance()->enqueue_dependant_js( $post_id );
			HTMLPress_Helper::get_instance()->enqueue_dependant_css( $post_id );

			return do_shortcode( htmlspecialchars_decode( $template_html ) );
		}

		/**
		 * How to use shortcode?
		 * [htmlpress-user field=""]
		 *
		 * E.g.
		 * 1. [htmlpress-user field="display_name"]
		 * 2. [htmlpress-user field="user_email"]
		 *
		 * Valid values for the `$field` parameter include:
		 *
		 * - admin_color
		 * - aim
		 * - comment_shortcuts
		 * - description
		 * - display_name
		 * - first_name
		 * - ID
		 * - jabber
		 * - last_name
		 * - nickname
		 * - plugins_last_view
		 * - plugins_per_page
		 * - rich_editing
		 * - syntax_highlighting
		 * - user_activation_key
		 * - user_description
		 * - user_email
		 * - user_firstname
		 * - user_lastname
		 * - user_level
		 * - user_login
		 * - user_nicename
		 * - user_pass
		 * - user_registered
		 * - user_status
		 * - user_url
		 * - yim
		 *
		 * @param  array $atts Shortcode attributes.
		 * @return mixed       Shortcode markup.
		 */
		function user( $atts ) {

			$atts = shortcode_atts( array(
				'field'       => '',
			), $atts, 'htmlpress-user' );

			$output = '';
			switch ( $atts['field'] ) {

				case 'avatar' :
								$output = get_avatar( esc_url( get_the_author_meta( 'ID' ) ) );
					break;

				default:
								$output = esc_attr( get_the_author_meta( $atts['field'] ) );
					break;
			}

			return $output;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	HTMLPress_Shortcodes::get_instance();

endif;
