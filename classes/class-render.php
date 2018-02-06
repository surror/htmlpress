<?php
/**
 * HTMLPress_Render initial setup
 *
 * @since 0.1.0
 * @package HTMLPress
 */

if ( ! class_exists( 'HTMLPress_Render' ) ) :

	/**
	 * Render
	 */
	class HTMLPress_Render {

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
			add_action( 'init', 	array( $this, 'init' ) );
		}

		/**
		 * Int
		 *
		 * @since 0.1.0
		 * @return void
		 */
		function init() {

			// Prevent for non logged in user.
			if ( ! is_user_logged_in() ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', 	array( $this, 'assets' ) );
			add_filter( 'template_include', 	array( $this, 'htmlpress_single_module' ) );
			add_action( 'htmlpress_module_builder', 	array( $this, 'contents' ) );
		}

		/**
		 * Assets
		 */
		function assets() {

			wp_register_style( 'fontawesome', HTMLPRESS_URI . 'assets/vander/css/font-awesome.css' );
			wp_register_style( 'select2', HTMLPRESS_URI . 'assets/vander/select2/dist/css/select2.css' );
			wp_register_style( 'bootstrap-grid', HTMLPRESS_URI . 'assets/vander/bootstrap-v4/css/bootstrap-grid.css' );
			wp_register_style( 'bootstrap-reboot', HTMLPRESS_URI . 'assets/vander/bootstrap-v4/css/bootstrap-reboot.css' );
			wp_register_style( 'bootstrap', HTMLPRESS_URI . 'assets/vander/bootstrap-v4/css/bootstrap.css' );

			// Vendor assets.
			wp_register_script( 'bootstrap', HTMLPRESS_URI . 'assets/vander/bootstrap/js/bootstrap.js', array( 'jquery' ) );
			wp_register_script( 'select2', HTMLPRESS_URI . 'assets/vander/select2/dist/js/select2.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ) );
			wp_register_script( 'ace', HTMLPRESS_URI . 'assets/vander/ace-builds-master/src-min/ace.js', array( 'jquery' ) );
			wp_register_script( 'html2canvas', HTMLPRESS_URI . 'assets/vander/html2canvas/dist/html2canvas.js' , array( 'jquery' ) );

			// Template assets.
			wp_enqueue_style( 'author-template', HTMLPRESS_URI . 'assets/admin/css/author-template.css', array( 'fontawesome', 'select2' ) );
			wp_enqueue_script( 'author-template', HTMLPRESS_URI . 'assets/admin/js/author-template.js', array( 'jquery', 'select2', 'ace', 'html2canvas' ) );

			/**
			 * Current Template Meta
			 */
			$selected_scripts = get_post_meta( get_the_id(), 'selected-scripts', true );
			$selected_styles  = get_post_meta( get_the_id(), 'selected-styles', true );

			wp_localize_script( 'author-template', '_s', array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'post_meta' => array(
					'selected_scripts' => $selected_scripts,
					'selected_styles'  => $selected_styles,
		        ),
		    ) );
		}

		/**
		 * Template Include
		 *
		 * @param  string $template File path of the render template.
		 * @return string           Return the _s templates template.
		 */
		function htmlpress_single_module( $template ) {

			// If template is htmlpress then use custom template.
			if ( 'htmlpress' === get_post_type() ) {
				return HTMLPRESS_DIR . 'includes/module-builder.php';
			}

			return $template;
		}

		/**
		 * Content
		 *
		 * @since 0.1.0
		 * @return void
		 */
		function contents() {
			add_thickbox();
			?>
			<div id="htmlpress">
				<div class="htmlpress-header">
					<div class="logo">
						<h1> <?php the_title( ); ?> </h1>
						<div class="shortcode-info">
							<div><?php _e( 'Use shortcode:', 'htmlpress' ); ?></div>
							<div><input type="text" value="[htmlpress module='<?php echo get_the_ID(); ?>']"></div>
						</div>
					</div>
					<div class="toolbar" data-id="<?php echo get_the_id(); ?>">
						<ul>
							<li> <span title="<?php _e( 'Preview for Laptop', 'htmlpress' ); ?>" class="preview-laptop"> <i class="dashicons dashicons-laptop"></i> </span> </li>
							<li> <span title="<?php _e( 'Preview for Tablet', 'htmlpress' ); ?>" class="preview-tablet"> <i class="dashicons dashicons-tablet"></i> </span> </li>
							<li> <span title="<?php _e( 'Preview for Smartphone', 'htmlpress' ); ?>" class="preview-smartphone"> <i class="dashicons dashicons-smartphone"></i> </span> </li>
							<li> | </li>
							<li> <span href="#" title="<?php _e( 'Save', 'htmlpress' ); ?>" class="save-template"><i class="dashicons dashicons-upload"></i> </span> </li>
							<li> <a title="<?php _e( 'Download Zip', 'htmlpress' ); ?>" href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=htmlpress_download&id=' . get_the_ID() ) ); ?>" ><i class="dashicons dashicons-download"></i></a> </li>
							<li> <span href="#" title="<?php _e( 'Duplicate Template', 'htmlpress' ); ?>" class="duplicate-template"><i class="dashicons dashicons-welcome-add-page"></i> </span> </li>
							<li> <span href="#" title="<?php _e( 'Full Screen', 'htmlpress' ); ?>" class="icon-full-screen"><i class="dashicons dashicons-visibility"></i> </span> </li>
							<li> | </li>
							<li> <span href="#" title="<?php _e( 'More', 'htmlpress' ); ?>" onClick="jQuery('.more-content').slideToggle();"><i class="dashicons dashicons-menu"></i> </span> </li>
						</ul>

						<!-- Toolbar Contents -->
						<div class="toolbar-contents">
							<div class="more-content" style="display: none;">
								<p>
									<button id="saveHTMLBtn" ><i class="dashicons dashicons-format-image" title="<?php _e( 'Generate Featured Image', 'htmlpress' ); ?>"></i> <span class="msg"> <?php _e( 'Generate Featured Image', 'htmlpress' ); ?> </span> </button>
									<br/>
									<?php _e( 'Note: This is the beta feature. Generate the screenshot of the HTML makrup and set it as a featured image.', 'htmlpress' ); ?>
									<!-- Featured Image Generator -->
									<div id="saveHTMLCanvas"></div>
								</p>
							</div>
						</div>

					</div>
				</div>

				<?php
				$template_html = HTMLPress_Helper::get_instance()->get_post_file_contents( get_the_ID(), 'template_file_html_url' );
				$template_css  = HTMLPress_Helper::get_instance()->get_post_file_contents( get_the_ID(), 'template_file_css_url' );
				$template_js   = HTMLPress_Helper::get_instance()->get_post_file_contents( get_the_ID(), 'template_file_js_url' );

				$selected_scripts = json_decode( get_post_meta( get_the_id(), 'selected-scripts', true ) );
				$selected_styles  = json_decode( get_post_meta( get_the_id(), 'selected-styles', true ) );
				if ( is_array( $selected_scripts ) && count( $selected_scripts ) > 0 ) {
					foreach ( $selected_scripts as $key => $handle ) {
						wp_enqueue_script( $handle );
					}
					wp_add_inline_script( $handle, $template_js );
				}

				if ( is_array( $selected_styles ) && count( $selected_styles ) > 0 ) {
					foreach ( $selected_styles as $key => $handle ) {
						wp_enqueue_style( $handle );
					}
					wp_add_inline_style( $handle, $template_css );
				}

				?>

				<!-- .preview -->
				<div class="preview col-md-12">
					<div class="frame"> <iframe id="htmlpress-preview-frame" frameborder="0"></iframe> </div>
				</div>

				<!-- editors wrap -->
				<div class="editors col-md-12">

					<!-- editors -->
					<div class="row">
						
						<!-- html editor -->
						<div class="col-md-4 editor-template-html">
							<label for="template-html"> <h2> <?php _e( 'HTML', 'htmlpress' ); ?> </h2> </label>
							<div class="template-editor-contents template-html-contents">
							</div>
							<pre name="template-html" id="template-html"><?php echo esc_html( $template_html ); ?></pre>
						</div>
				
						<!-- css editor -->
						<div class="col-md-4 editor-template-css">
							<label for="template-css">
								<h2> <?php _e( 'CSS', 'htmlpress' ); ?> <i class="dashicons dashicons-admin-generic"></i></h2>
							</label>
							<div class="template-editor-contents template-css-contents">
								<p><?php _e( 'Select registered CSS:', 'htmlpress' ); ?></p>
								<?php HTMLPress_Helper::get_instance()->registered_styles_dropdown( $selected_styles ); ?>
							</div>
							<pre name="template-css" id="template-css"><?php echo esc_html( $template_css ); ?></pre>
						</div>
				
						<div class="col-md-4 editor-template-js">
							<label for="template-js"> <h2> <?php _e( 'JS', 'htmlpress' ); ?> <i class="dashicons dashicons-admin-generic"></i></h2> </label>
							
							<div class="template-editor-contents template-js-contents">

								<p><?php _e( 'Select registered JS:', 'htmlpress' ); ?></p>
								<?php HTMLPress_Helper::get_instance()->registered_scripts_dropdown( $selected_scripts ); ?>
							</div>
							<pre name="template-js" id="template-js"><?php echo stripslashes( $template_js ); ?></pre>
						</div>
					</div>
				</div>
			</div><!-- #htmlpress -->
			<?php
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	HTMLPress_Render::get_instance();

endif;

