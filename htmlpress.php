<?php
/* 
 * Plugin Name: HTMLPress
 * Plugin URI: http://surror.com/
 * Description: Simple HTML snippets generator.
 * Version: 0.1.0
 * Author: Surror
 * Author URI: https://profiles.wordpress.org/surror/
 * Text Domain: htmlpress
 *
 * @package HTMLPress
 */

define( 'HTMLPRESS_VER', '0.1.0' );
define( 'HTMLPRESS_FILE', __FILE__ );
define( 'HTMLPRESS_BASE', plugin_basename( HTMLPRESS_FILE ) );
define( 'HTMLPRESS_DIR', plugin_dir_path( HTMLPRESS_FILE ) );
define( 'HTMLPRESS_URI', plugins_url( '/', HTMLPRESS_FILE ) );

require_once( 'classes/class-htmlpress.php' );
