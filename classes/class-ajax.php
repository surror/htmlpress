<?php
/**
 * HTMLPress_Ajax initial setup
 *
 * @since 0.1.0
 * @package HTMLPress
 */

if ( ! class_exists( 'HTMLPress_Ajax' ) ) :

	/**
	 * HTMLPress
	 *
	 * @since 0.1.0
	 */
	class HTMLPress_Ajax {

		/**
		 * Instance
		 *
		 * @property private
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

			/**
			 * Hooks for - Front End
			 */
			add_action( 'wp_ajax_htmlpress_save_module',                         array( $this, 'save_module' ) );
			add_action( 'wp_ajax_noprev_htmlpress_save_module',                  array( $this, 'save_module' ) );

			add_action( 'wp_ajax_htmlpress_duplicate_template',                    array( $this, 'duplicate_module' ) );
			add_action( 'wp_ajax_noprev_htmlpress_duplicate_template',             array( $this, 'duplicate_module' ) );

			add_action( 'wp_ajax_htmlpress_template_thumbnail_generator',          array( $this, 'generate_post_thumbnail' ) );
			add_action( 'wp_ajax_noprev_htmlpress_template_thumbnail_generator',   array( $this, 'generate_post_thumbnail' ) );

			/**
			 * Hooks for - Back End Editor
			 */
			add_action( 'wp_ajax_htmlpress_download',                           array( $this, 'download_zip' ) );
			add_action( 'wp_ajax_noprev_htmlpress_download',                    array( $this, 'download_zip' ) );

		}

		/**
		 * Generate Post Thumbnail
		 *
		 * @since 0.1.0
		 * @return void
		 */
		function generate_post_thumbnail() {

			$dir_info   = self::create_local_dir();
			$filesystem = HTMLPress_Helper::get_instance()->get_filesystem();
			$local_file = trailingslashit( $dir_info['path'] );

			$img     = ( isset( $_POST['imgBase64'] ) ) ? $_POST['imgBase64'] : '' ;
			$post_id = ( isset( $_POST['post_id'] ) ) ? absint( $_POST['post_id'] ) : '' ;

			if ( empty( $img ) ) {
				wp_send_json_error( __( 'Invalid image data.', 'htmlpress' ) );
			}

			$img        = str_replace( 'data:image/png;base64,', '', $img );
			$img        = str_replace( ' ', '+', $img );
			$data       = base64_decode( $img );
			$image_name = $post_id . '.png';
			$image_url  = $local_file . $image_name;

			// File Created.
			if ( ! $filesystem->put_contents( $image_url, $data ) ) {
				wp_send_json_error( __( 'Unable to save the file.', 'htmlpress' ) );
			}

			// Deleted existing post thumbnail.
			// wp_delete_attachment( get_post_thumbnail_id( $post_id ), true );
			// Set generated featured image to the post.
			$upload_dir       = wp_upload_dir(); // Set upload folder.
			$image_data       = $filesystem->get_contents( $image_url ); // Get image data.
			$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name.
			$filename         = basename( $unique_file_name ); // Create image file name.

			// Check folder permission and define file location.
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			// Create the image  file on the server.
			$filesystem->put_contents( $file, $image_data );

			// Check image file type.
			$wp_filetype = wp_check_filetype( $filename, null );

			// Set attachment data.
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => sanitize_file_name( $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			// Create the attachment.
			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

			// Include image.php.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Define attachment metadata.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

			// Assign metadata to attachment.
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// And finally assign featured image to post.
			set_post_thumbnail( $post_id, $attach_id );

			wp_die( );
		}

		/**
		 * Download Zip
		 *
		 * @since 0.1.0
		 *
		 * @hook htmlpress_download
		 * @return void
		 */
		function download_zip() {

			$post_id       = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			$template_html = '';
			$template_css  = '';
			$template_js   = '';
			$zip           = new ZipArchive();
			$download      = $post_id . '.zip';

			$zip->open( $download, ZipArchive::CREATE );

			$template_html = HTMLPress_Helper::get_instance()->get_post_file_contents( $post_id, 'template_file_html_url' );
			$template_css  = HTMLPress_Helper::get_instance()->get_post_file_contents( $post_id, 'template_file_css_url' );
			$template_js   = HTMLPress_Helper::get_instance()->get_post_file_contents( $post_id, 'template_file_js_url' );

			$zip->addFromString( $post_id . '/' . $post_id . '.html', $template_html );
			$zip->addFromString( $post_id . '/' . $post_id . '.css', $template_css );
			$zip->addFromString( $post_id . '/' . $post_id . '.js', $template_js );

			$zip->close();

			header( 'Content-Type: application/zip' );
			header( "Content-Disposition: attachment; filename = $download" );
			header( 'Content-Length: ' . filesize( $download ) );
			readfile( $download );
			unlink( $download );
			exit();
		}

		/**
		 * Duplicate
		 *
		 * @since 0.1.0
		 *
		 * @hook htmlpress_duplicate_template
		 * @return void
		 */
		function duplicate_module() {

			// Get the original post.
			$post_id     = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
			$post        = get_post( $post_id );
			$new_post_id = $this->create_duplicate( $post );

			if ( ! empty( $new_post_id ) ) {
				echo esc_url( get_post_permalink( $new_post_id ) );
			}

			wp_die();
		}

		/**
		 * Create Duplicate
		 *
		 * @since 0.1.0
		 *
		 * @param  object $post Post object.
		 * @return integer       New post id.
		 */
		function create_duplicate( $post ) {

			if ( 'htmlpress' !== $post->post_type ) {
				wp_die( esc_html__( 'Could not duplicate this post type template.', 'htmlpress' ) );
			}

			$new_post_author = wp_get_current_user();
			$post_date_gmt   = get_gmt_from_date( $post->post_date );

			$new_post = array(
				'post_title'            => 'Copy ' . $post->post_title,
				'post_author'           => $new_post_author->ID,
				'comment_status'        => $post->comment_status,
				'ping_status'           => $post->ping_status,
				'post_content'          => $post->post_content,
				'post_content_filtered' => $post->post_content_filtered,
				'post_excerpt'          => $post->post_excerpt,
				'post_mime_type'        => $post->post_mime_type,
				'post_parent'           => $post->post_parent,
				'post_password'         => $post->post_password,
				'post_status'           => $post->post_status,
				'post_type'             => $post->post_type,
				'post_date'             => $post->post_date,
				'post_date_gmt'         => $post_date_gmt,
			);

			$new_post_id = wp_insert_post( wp_slash( $new_post ) );

			/**
			 * Post meta
			 */
			$template_html = HTMLPress_Helper::get_instance()->get_post_file_contents( $post->ID, 'template_file_html_url' );
			$template_css  = HTMLPress_Helper::get_instance()->get_post_file_contents( $post->ID, 'template_file_css_url' );
			$template_js   = HTMLPress_Helper::get_instance()->get_post_file_contents( $post->ID, 'template_file_js_url' );

			$scripts       = get_post_meta( $post->ID, 'selected-scripts', true );
			$styles        = get_post_meta( $post->ID, 'selected-styles', true );

			$dir_info  = self::create_local_dir(); // Check folder exist or not?
			$post_path = trailingslashit( $dir_info['path'] . $new_post_id );
			$post_dir  = trailingslashit( $dir_info['url'] . $new_post_id );
			$dir = array(
				'path' => $post_path,
				'url' => $post_dir,
			);

			if ( wp_mkdir_p( $dir['path'] ) ) {
				self::seve_file( $dir, $new_post_id, 'html', $template_html );
				self::seve_file( $dir, $new_post_id, 'css', $template_css );
				self::seve_file( $dir, $new_post_id, 'js', $template_js );
			}

			update_post_meta( $new_post_id, 'selected-scripts', $scripts );
			update_post_meta( $new_post_id, 'selected-styles', $styles );

			return $new_post_id;
		}

		/**
		 * Save Module
		 *
		 * @since 0.1.0
		 * @return void
		 */
		function save_module() {

			$post_id         = ( isset( $_POST['post_id'] ) ) ? absint( $_POST['post_id'] ) : '';
			$scripts         = ( isset( $_POST['selected_scripts'] ) ) ? $_POST['selected_scripts'] : array();
			$styles          = ( isset( $_POST['selected_styles'] ) ) ? $_POST['selected_styles'] : array();
			$source_codehtml = ( isset( $_POST['source_codehtml'] ) ) ? urldecode( $_POST['source_codehtml'] ) : '';
			$source_codejs   = ( isset( $_POST['source_codejs'] ) ) ? urldecode( $_POST['source_codejs'] ) : '';
			$source_codecss  = ( isset( $_POST['source_codecss'] ) ) ? urldecode( $_POST['source_codecss'] ) : '';

			update_post_meta( $post_id, 'selected-scripts', json_encode( $scripts ) );
			update_post_meta( $post_id, 'selected-styles', json_encode( $styles ) );

			$dir_info  = self::create_local_dir(); // Check folder exist or not?
			$post_path = trailingslashit( $dir_info['path'] . $post_id );
			$post_dir  = trailingslashit( $dir_info['url'] . $post_id );
			$dir = array(
				'path' => $post_path,
				'url' => $post_dir,
			);

			if ( wp_mkdir_p( $dir['path'] ) ) {
				self::seve_file( $dir, $post_id, 'html', $source_codehtml );
				self::seve_file( $dir, $post_id, 'css', $source_codecss );
				self::seve_file( $dir, $post_id, 'js', $source_codejs );
			}

			wp_die();
		}

		/**
		 * Save File
		 *
		 * @since 0.1.0
		 * @param  string $dir      Directory.
		 * @param  string $post_id  Post ID.
		 * @param  string $ext      File extension.
		 * @param  string $contents Contents.
		 * @return void
		 */
		public static function seve_file( $dir = '', $post_id = '', $ext = '', $contents = '' ) {
			$file_path = $dir['path'] . $post_id . '.' . $ext;
			$file_url = $dir['url'] . $post_id . '.' . $ext;
			$filesystem = HTMLPress_Helper::get_instance()->get_filesystem(); // Check folder exist or not?

			$filesystem->put_contents( $file_path, $contents );
			update_post_meta( $post_id, 'template_file_' . $ext . '_url', $file_url );
		}

		/**
		 * Create local directory if not exist.
		 *
		 * @since 0.1.0
		 * @param  string $dir_name Directory name.
		 * @return array            Directory details.
		 */
		static function create_local_dir( $dir_name = 'htmlpress' ) {
			$wp_info    = wp_upload_dir();
			$filesystem = HTMLPress_Helper::get_instance()->get_filesystem(); // Check folder exist or not?

			// SSL workaround.
			if ( self::is_ssl() ) {
				$wp_info['baseurl'] = str_ireplace( 'http://', 'https://', $wp_info['baseurl'] );
			}

			// Build the paths.
			$dir_info = array(
				'path'   => $wp_info['basedir'] . '/' . $dir_name . '/',
				'url'    => $wp_info['baseurl'] . '/' . $dir_name . '/',
			);

			// Create the upload dir if it doesn't exist.
			if ( ! file_exists( $dir_info['path'] ) ) {

				// Create the directory.
				wp_mkdir_p( $dir_info['path'] );

				// Add an index file for security.
				$filesystem->put_contents( $dir_info['path'] . 'index.html', '' );
			}

			return $dir_info;
		}

		/**
		 * Is SSL
		 *
		 * @since 0.1.0
		 * @return boolean SSL status.
		 */
		static public function is_ssl() {
			if ( is_ssl() ) {
				return true;
			} elseif ( 0 === stripos( get_option( 'siteurl' ), 'https://' ) ) {
				return true;
			} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
				return true;
			}

			return false;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	HTMLPress_Ajax::get_instance();

endif;
