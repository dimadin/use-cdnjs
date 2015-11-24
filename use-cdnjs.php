<?php

/**
 * The Use cdnjs Plugin
 *
 * Use common JavaScript libraries from cdnjs instead of local copies.
 *
 * @package    Use_cdnjs
 * @subpackage Main
 */

/**
 * Plugin Name: Use cdnjs
 * Plugin URI:  http://blog.milandinic.com/wordpress/plugins/use-cdnjs/
 * Description: Use common JavaScript libraries from cdnjs instead of local copies.
 * Author:      Milan Dinić
 * Author URI:  http://blog.milandinic.com/
 * Version:     0.4-beta-1
 * Text Domain: use-cdnjs
 * Domain Path: /languages/
 * License:     GPL
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 */
add_action( 'plugins_loaded', array( 'Use_cdnjs', 'plugins_loaded' ), 10 );

if ( ! class_exists( 'Use_cdnjs' ) ) :
/**
 * Use cdnjs main class.
 *
 * Use common JavaScript libraries from cdnjs instead of local copies.
 */
class Use_cdnjs {
	/**
	 * Scripts and their data available for replacement.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @var array
	 */
	public $cdnjs_scripts = array();

	/**
	 * Styles and their data available for replacement.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @var array
	 */
	public $cdnjs_styles = array();

	/**
	 * Set class properties and add main methods to appropriate hooks.
	 * 
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		// Disables concatenation
		add_action( 'init',                              array( $this, 'disable_concatenation'        )        );

		// Put jQuery in noConflict mode
		add_filter( 'script_loader_tag',                 array( $this, 'jquery_noconflict'            ), 10, 3 );

		// Remove version query string
		add_filter( 'script_loader_src',                 array( $this, 'remove_version'               ), 10, 2 );
		add_filter( 'style_loader_src',                  array( $this, 'remove_version'               ), 10, 2 );

		// Replace WordPress paths with cdnjs URLs
		add_action( 'wp_default_scripts',                array( $this, 'replace_scripts'              ), 99    );
		add_action( 'wp_default_styles',                 array( $this, 'replace_styles'               ), 99    );

		// Apply filters that are hooked
		add_action( 'wp_enqueue_scripts',                array( $this, 'apply_filters'                ), 99    );
		add_action( 'wp_print_footer_scripts',           array( $this, 'apply_filters'                ),  9    );

		// Register plugins action links filter
		add_filter( 'plugin_action_links',               array( $this, 'action_links'                 ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'action_links'                 ), 10, 2 );

		// Define scripts and styles available for replacement with one on cdnjs
		$this->define_scripts();
		$this->define_styles();
	}

	/**
	 * Initialize Use_cdnjs object.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return Use_cdnjs $instance Instance of Use_cdnjs class.
	 */
	public static function &get_instance() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new Use_cdnjs;
		}

		return $instance;
	}

	/**
	 * Load plugin.
	 *
	 * @since 1.0
	 * @access public
	 */
	public static function plugins_loaded() {
		// Initialize class
		$use_cdnjs = Use_cdnjs::get_instance();
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param array  $links       Existing plugin's action links.
	 * @param string $plugin_file Path to the plugin file.
	 * @return array $links New plugin's action links.
	 */
	public function action_links( $links, $plugin_file ) {
		// Set basename
		$basename = plugin_basename( __FILE__ );

		// Check if it is for this plugin
		if ( $basename != $plugin_file ) {
			return $links;
		}

		// Load translations
		load_plugin_textdomain( 'use-cdnjs', false, dirname( $basename ) . '/languages' );

		// Add new links
		$links['donate']   = '<a href="http://blog.milandinic.com/donate/">' . __( 'Donate', 'use-cdnjs' ) . '</a>';
		$links['wpdev']    = '<a href="http://blog.milandinic.com/wordpress/custom-development/">' . __( 'WordPress Developer', 'use-cdnjs' ) . '</a>';

		return $links;
	}

	/**
	 * Define scripts and their data available for replacement.
	 * 
	 * @since 1.0
	 * @access protected
	 */
	protected function define_scripts() {
		/*
		 * What is needed is handle with which they are
		 * registered in WordPress, library name,
		 * file name, and is script minified and with
		 * what suffix on cdnjs.
		 *
		 * Sometimes all four of values are same as in
		 * WordPress, but some use different names.
		 */
		$scripts = array(
			'jquery-core'              => array(
				'library'  => 'jquery',
				'file'     => 'jquery',
				'minified' => '.min',
			),
			'jquery-migrate'           => array(
				'library'  => 'jquery-migrate',
				'file'     => 'jquery-migrate',
				'minified' => '.min',
			),
			'jquery-form'              => array(
				'library'  => 'jquery.form',
				'file'     => 'jquery.form',
				'minified' => '.min',
			),
			'jquery-color'             => array(
				'library'  => 'jquery-color',
				'file'     => 'jquery.color',
				'minified' => '.min',
			),
			'jquery-touch-punch'       => array(
				'library'  => 'jqueryui-touch-punch',
				'file'     => 'jquery.ui.touch-punch.min',
				'minified' => '',
			),
			'jcrop'                    => array(
				'library'  => 'jquery-jcrop',
				'file'     => 'js/jquery.Jcrop',
				'minified' => '.min',
			),
			'plupload'                 => array(
				'library'  => 'plupload',
				'file'     => 'plupload.full.min',
				'minified' => '',
			),
			'underscore'               => array(
				'library'  => 'underscore.js',
				'file'     => 'underscore',
				'minified' => '-min',
			),
			'backbone'                 => array(
				'library'  => 'backbone.js',
				'file'     => 'backbone',
				'minified' => '-min',
			),
			'twentysixteen-html5'      => array(
				'library'  => 'html5shiv',
				'file'     => 'html5shiv',
				'minified' => '.min',
			),
		);

		/**
		 * Filter scripts and their data available for replacement.
		 *
		 * @since 1.0
		 *
		 * @param array $scripts Scripts and their data available for replacement.
		 */
		$this->cdnjs_scripts = (array) apply_filters( 'use_cdnjs_scripts', $scripts );
	}

	/**
	 * Define styles and their data available for replacement.
	 * 
	 * @since 1.0
	 * @access protected
	 */
	protected function define_styles() {
		/*
		 * What is needed is handle with which they are
		 * registered in WordPress, library name,
		 * file name, and is script minified and with
		 * what suffix on cdnjs.
		 *
		 * Sometimes all four of values are same as in
		 * WordPress, but some use different names.
		 */
		$styles = array(
			'mediaelement'              => array(
				'library'  => 'mediaelement',
				'file'     => 'mediaelementplayer',
				'minified' => '.min',
			),
		);

		/**
		 * Filter styles and their data available for replacement.
		 *
		 * @since 1.0
		 *
		 * @param array $styles Styles and their data available for replacement.
		 */
		$this->cdnjs_styles = (array) apply_filters( 'use_cdnjs_styles', $styles );
	}

	/**
	 * Disables script and styles concatenation.
	 *
	 * It can only be done by using global
	 * variable that holds value, not via filter.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function disable_concatenation() {
		global $concatenate_scripts;

		$concatenate_scripts = false;
	}

	/**
	 * Print JavaScript to put jQuery in noConflict mode.
	 *
	 * WordPress copy of jQuery uses this at the end of
	 * the file. However, original copy (and one used in
	 * CDN) doesn't have this. By printing we replicate
	 * behaviour of when WordPress copy is used.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $tag    The `<script>` tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 * @return string $tag Modified tag for the enqueued script.
	 */
	public function jquery_noconflict( $tag, $handle, $src ) {
		if ( 'jquery-core' == $handle ) {
			$noconflict = "<script type='text/javascript'>try{jQuery.noConflict();}catch(e){};</script>";

			$tag = str_replace( "</script>", "</script>\n" . $noconflict, $tag );
		}

		return $tag;
	}

	/**
	 * Remove version query string from cdnjs source path.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $src    Item loader source path.
	 * @param string $handle Item handle.
	 * @return string $src Updated source path.
	 */
	public function remove_version( $src, $handle ) {
		// Only from URLs on cdnjs
		if ( preg_match( '/cdnjs\.cloudflare\.com\//', $src ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Replace WordPress scripts paths with cdnjs URLs.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param WP_Scripts $scripts Object with default styles.
	 */
	public function replace_scripts( &$scripts ) {
		// Call helper method with appropiate cdnjs items
		$this->replace( $scripts, $this->cdnjs_scripts );
	}

	/**
	 * Replace WordPress styles paths with cdnjs URLs.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param WP_Styles $styles Object with default styles.
	 */
	public function replace_styles( &$styles ) {
		// Call helper method with appropiate cdnjs items
		$this->replace( $styles, $this->cdnjs_styles );
	}

	/**
	 * Replace WordPress paths with cdnjs URLs.
	 *
	 * Loop through items registered on cdnjs and if item
	 * is registered in WordPress, get item's URL on cdnjs.
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @param WP_Dependencies $wordpress_items Object of appropiate child class.
	 */
	protected function replace( &$wordpress_items, $cdnjs_items ) {
		// Default extension is .js
		$extension = '.js';

		// If it is for styles, change extension
		if ( $wordpress_items instanceof WP_Styles ) {
			$extension = '.css';
		}

		// Loop through items registered on cdnjs
		foreach ( $cdnjs_items as $name => $data ) {
			// Check if item is registered in WordPress
			if ( $item = $wordpress_items->query( $name ) ) {
				// Set library and file name
				$library = $data['library'];
				$file    = $data['file'];

				// Check if item has minified version and set suffix
				if ( ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) && $data['minified'] ) {
					$suffix = $data['minified'];
				} else {
					$suffix = '';
				}

				// Use the same version as one that is registered for handle
				$version = $item->ver;

				/*
				 * Items that have hyphen in its version aren't allowed.
				 * This usually means that this is non-standard version.
				 */
				if ( false !== strpos( $version, '-' ) ) {
					continue;
				}

				// Always use HTTPS for URL on cdnjs
				$url = "https://cdnjs.cloudflare.com/ajax/libs/$library/$version/$file$suffix$extension";

				// Set item's path to one on cdnjs
				$item->src = $url;
			}
		}
	}

	/**
	 * Apply filters that are hooked.
	 *
	 * Replacements methods are fired again because
	 * plugins and themes register and enqueue items
	 * after default items are replaced.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function apply_filters() {
		// Only if there are any filters hooked
		if ( has_filter( 'use_cdnjs_scripts' ) ) {
			$this->replace_scripts( wp_scripts() );
		}

		if ( has_filter( 'use_cdnjs_styles' ) ) {
			$this->replace_styles( wp_styles() );
		}
	}
}
endif;
