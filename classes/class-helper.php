<?php
/**
 * HTMLPress_Ajax initial setup
 *
 * @since 0.1.0
 * @package HTMLPress
 */

if ( ! class_exists( 'HTMLPress_Helper' ) ) :

	/**
	 * Helper
	 *
	 * @since 0.1.0
	 */
	class HTMLPress_Helper {

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
		}

		/**
		 * File System
		 *
		 * @since 0.1.0
		 * @return object Class object.
		 */
		public static function get_filesystem() {
			global $wp_filesystem;

			require_once ABSPATH . '/wp-admin/includes/file.php';

			WP_Filesystem();

			return $wp_filesystem;
		}

		/**
		 * Get file content
		 *
		 * @since 0.1.0
		 * @param  integer $post_id  Post ID.
		 * @param  string  $meta_key Meta key.
		 * @param  string  $contents Default contents.
		 * @return mixed             File content.s
		 */
		public static function get_post_file_contents( $post_id = 0, $meta_key = '', $contents = '' ) {

			$template_html_url = get_post_meta( $post_id, $meta_key, true );
			if ( ! empty( $template_html_url ) ) {
				return self::get_filesystem()->get_contents( $template_html_url );
			}

			return $contents;
		}

		/**
		 * Enqueue JS
		 *
		 * @since 0.1.0
		 * @param  integer $post_id Post id.
		 * @return void
		 */
		public static function enqueue_dependant_js( $post_id = 0 ) {

			// Module Dependant JS.
			$selected_scripts = json_decode( get_post_meta( $post_id, 'selected-scripts', true ) );
			if ( is_array( $selected_scripts ) && count( $selected_scripts ) > 0 ) {
				foreach ( $selected_scripts as $key => $script ) {
					wp_enqueue_script( 'dynamic-js-' . $key, $script );
				}
			}

			// Module JS.
			$module_js_url = get_post_meta( $post_id, 'template_file_js_url', true );
			if ( ! empty( $module_js_url ) ) {
				wp_enqueue_script( $post_id . '-js', $module_js_url, array( 'jquery' ), HTMLPRESS_VER, true );
			}
		}

		/**
		 * Enqueue CSS
		 *
		 * @since 0.1.0
		 * @param  integer $post_id  Post ID.
		 * @return void
		 */
		public static function enqueue_dependant_css( $post_id = 0 ) {

			// Module Dependant CSS.
			$selected_styles  = json_decode( get_post_meta( $post_id, 'selected-styles', true ) );
			if ( is_array( $selected_styles ) && count( $selected_styles ) > 0 ) {
				foreach ( $selected_styles as $key => $style ) {
					wp_enqueue_style( 'dynamic-css-' . $key, $style );
				}
			}

			// Module CSS.
			$module_css_url = get_post_meta( $post_id, 'template_file_css_url', true );
			if ( ! empty( $module_css_url ) ) {
				wp_enqueue_style( $post_id . '-css', $module_css_url, '', HTMLPRESS_VER, 'all' );
			}
		}

		/**
		 * Scripts dropdown
		 *
		 * @since 0.1.0
		 *
		 * @param  array $selected_scripts Selected scripts.
		 * @return void
		 */
		public static function registered_scripts_dropdown( $selected_scripts ) {

			global $wp_scripts;

			$stored = self::get_script_with_urls( $selected_scripts );
			?>
			<select class="htmlpress-wp-scripts" multiple="multiple">
				<?php
				foreach ( self::get_all_scripts( $selected_scripts ) as $key => $handle ) {
					$selected = '';
					if ( in_array( $handle, $selected_scripts ) ) {
						$selected = ' selected ';
					}
					?>
					<option <?php echo esc_attr( $selected ); ?> title="<?php echo esc_attr( $handle ); ?>" value="<?php echo esc_attr( $handle ); ?>"> <?php echo esc_html( $handle ); ?> </option>
				<?php } ?>
			</select>
			<?php
		}

		/**
		 * Get file content
		 *
		 * @since 0.1.0
		 *
		 * @param  array $selected   Selected scripts.
		 * @return array             Sorted all scripts.
		 */
		public static function get_all_scripts( $selected = array() ) {
			$all_scripts = array();
			foreach ( wp_scripts()->registered as $script ) {
				if ( $script->src ) {
					$all_scripts[] = $script->handle;
				}
			}

			$sorted = array();
			if( is_array( $selected ) ) {
				$sorted = array_merge( $selected, $all_scripts );
			}
			$sorted = array_unique( $sorted );

			return $sorted;
		}

		/**
		 * Get scripts with URLs
		 *
		 * @since 0.1.0
		 * @param  array $selected Selected scripts.
		 * @return array            Scripts.
		 */
		public static function get_script_with_urls( $selected = array() ) {

			$all_scripts = array();
			foreach ( wp_scripts()->registered as $script ) {
				if ( $script->src ) {
					if ( strpos( $script->src, 'http' ) !== false ) {
						$all_scripts[ $script->handle ] = $script->src;
					} else {
						$all_scripts[ $script->handle ] = site_url() . $script->src;
					}
				}
			}

			if ( is_array( $selected ) ) {
				$selected_urls = array();
				foreach ( $selected as $key => $handle ) {
					if ( isset( $all_scripts[ $handle ] ) ) {
						$selected_urls[ $handle ] = $all_scripts[ $handle ];
					}
				}
				return $selected_urls;
			}

			return $all_scripts;
		}

		/**
		 * Styles Dropdown
		 *
		 * @since 0.1.0
		 *
		 * @param  array $selected_styles   Selected styless.
		 * @return void
		 */
		public static function registered_styles_dropdown( $selected_styles = array() ) {
			global $wp_styles;
			?>
			<select class="htmlpress-wp-styles" multiple="multiple">
				<?php
				foreach ( $wp_styles->registered as $style ) {
					if ( $style->src ) {
						$selected = '';
						if ( strpos( $style->src, 'http' ) !== false ) {
							if ( is_array( $selected_styles ) && in_array( $style->src, $selected_styles ) ) {
								$selected = ' selected ';
							} ?>
							<option <?php echo esc_attr( $selected ); ?> title="<?php echo esc_attr( $style->src ); ?>" value="<?php echo esc_attr( $style->src ); ?>"> <?php echo esc_html( $style->handle ); ?> </option>
						<?php } else {
							if ( is_array( $selected_styles ) && in_array( site_url() . $style->src, $selected_styles ) ) {
								$selected = ' selected ';
							} ?>
							<option <?php echo esc_attr( $selected ); ?> title="<?php echo site_url() . esc_attr( $style->src ); ?>" value="<?php echo site_url() . esc_attr( $style->src ); ?>"> <?php echo esc_html( $style->handle ); ?> </option>
						<?php
						}
					}
				}
				?>
			</select>
			<?php
		}
	}

	/**
	 * Kicking this off by calling  $string'get_instance()' method
	 */
	HTMLPress_Helper::get_instance();

endif;
