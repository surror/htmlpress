<?php
/**
 * Post Types
 *
 * @since 0.1.0
 * @package HTMLPress
 */

if ( ! class_exists( 'HTMLPress_Post_Types' ) ) :

	/**
	 * Post Type
	 *
	 * @since 0.1.0
	 */
	class HTMLPress_Post_Types {

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
			add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
			add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 5 );
			add_action( 'rest_api_init', array( __CLASS__, 'rest_api' ) );
		}

		/**
		 * Rest API
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public static function rest_api() {
			register_rest_field( 'htmlpress',
				'template-html',
				array(
			       'get_callback'    => array( __CLASS__, 'rest_api_get_post_meta' ),
			       'schema'          => null,
			    )
			);
			register_rest_field( 'htmlpress',
				'template-css',
				array(
			       'get_callback'    => array( __CLASS__, 'rest_api_get_post_meta' ),
			       'schema'          => null,
			    )
			);
			register_rest_field( 'htmlpress',
				'template-js',
				array(
			       'get_callback'    => array( __CLASS__, 'rest_api_get_post_meta' ),
			       'schema'          => null,
			    )
			);
			register_rest_field( 'htmlpress',
				'selected-scripts',
				array(
			       'get_callback'    => array( __CLASS__, 'rest_api_get_post_meta' ),
			       'schema'          => null,
			    )
			);
			register_rest_field( 'htmlpress',
				'selected-styles',
				array(
			       'get_callback'    => array( __CLASS__, 'rest_api_get_post_meta' ),
			       'schema'          => null,
			    )
			);
		}

		/**
		 * Rest API Post Meta
		 *
		 * @since 0.1.0
		 *
		 * @param  string $object_type Post type.
		 * @param  string $meta_kay    Meta key.
		 * @param  array  $args        Arguments.
		 * @return mixed Post meta.
		 */
		public static function rest_api_get_post_meta( $object_type, $meta_kay, $args = array() ) {
			return get_post_meta( $object_type['id'], $meta_kay, true );
		}

		/**
		 * Create a taxonomy
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public static function register_taxonomy() {
			$labels = array(
				'name'                  => _x( 'Categories', 'Taxonomy plural name', 'htmlpress' ),
				'singular_name'         => _x( 'Category', 'Taxonomy singular name', 'htmlpress' ),
				'search_items'          => __( 'Search Categories', 'htmlpress' ),
				'popular_items'         => __( 'Popular Categories', 'htmlpress' ),
				'all_items'             => __( 'All Categories', 'htmlpress' ),
				'parent_item'           => __( 'Parent Category', 'htmlpress' ),
				'parent_item_colon'     => __( 'Parent Category', 'htmlpress' ),
				'edit_item'             => __( 'Edit Category', 'htmlpress' ),
				'update_item'           => __( 'Update Category', 'htmlpress' ),
				'add_new_item'          => __( 'Add New Category', 'htmlpress' ),
				'new_item_name'         => __( 'New Category Name', 'htmlpress' ),
				'add_or_remove_items'   => __( 'Add or remove Categories', 'htmlpress' ),
				'choose_from_most_used' => __( 'Choose from most used Categories', 'htmlpress' ),
				'menu_name'             => __( 'Categories', 'htmlpress' ),
			);

			$args = array(
				'labels'            => $labels,
				'public'            => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_tagcloud'     => true,
				'show_ui'           => true,
				'query_var'         => true,
				'rewrite'           => true,
				'query_var'         => true,
				'capabilities'      => array(),
			);

			register_taxonomy( 'htmlpress-category', array( 'htmlpress' ), $args );
		}

		/**
		 * Register post type
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public static function register_post_type() {

			$labels = array(
				'menu_name'          => __( 'HTMLPress', 'htmlpress' ),
				'name'               => __( 'Modules', 'htmlpress' ),
				'singular_name'      => __( 'Modules', 'htmlpress' ),
				'all_items'          => __( 'All Modules', 'htmlpress' ),
				'add_new'            => _x( 'Add New Module', 'htmlpress', 'htmlpress' ),
				'add_new_item'       => __( 'Add New Module', 'htmlpress' ),
				'edit_item'          => __( 'Edit Modules', 'htmlpress' ),
				'new_item'           => __( 'New Modules', 'htmlpress' ),
				'view_item'          => __( 'View Modules', 'htmlpress' ),
				'search_items'       => __( 'Search Modules', 'htmlpress' ),
				'not_found'          => __( 'No Modules found', 'htmlpress' ),
				'not_found_in_trash' => __( 'No Modules found in Trash', 'htmlpress' ),
				'parent_item_colon'  => __( 'Parent Modules:', 'htmlpress' ),
			);

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => array(),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => null,
				'menu_icon'           => 'dashicons-editor-code',
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'has_archive'         => true,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'show_in_rest'        => true,
				'capability_type'     => 'post',
				'supports'            => array( 'title', 'thumbnail', 'custom-fields', 'author' ),
			);

			register_post_type( 'htmlpress', $args );
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	HTMLPress_Post_Types::get_instance();

endif;
