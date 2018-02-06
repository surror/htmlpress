<?php
/**
 * HTMLPress initial setup
 *
 * @since 0.1.0
 * @package HTMLPress
 */

if ( ! class_exists( 'HTMLPress' ) ) :

	/**
	 * HTMLPress
	 *
	 * @since 0.1.0
	 */
	class HTMLPress {

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
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			include_once( HTMLPRESS_DIR . 'classes/class-helper.php' );
			include_once( HTMLPRESS_DIR . 'classes/class-ajax.php' );
			include_once( HTMLPRESS_DIR . 'classes/class-post-type.php' );
			include_once( HTMLPRESS_DIR . 'classes/class-render.php' );
			include_once( HTMLPRESS_DIR . 'classes/class-shortcodes.php' );
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	HTMLPress::get_instance();

endif;
