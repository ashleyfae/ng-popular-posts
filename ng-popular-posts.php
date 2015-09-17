<?php
/*
 * Plugin Name: NG Popular Posts
 * Plugin URI: https://www.nosegraze.com
 * Description: Widget that displays a number of popular posts.
 * Version: 1.0
 * Author: Nose Graze
 * Author URI: https://www.nosegraze.com
 * License: GPL2
 * 
 * @package ng-popular-posts
 * @copyright Copyright (c) 2015, Nose Graze Ltd.
 * @license GPL2+
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-popular-posts-widget.php';

/**
 * Register the widget with WordPress.
 *
 * Note: PHP 5.3+ only.
 */
add_action( 'widgets_init', function () {
	register_widget( 'NG_Popular_Posts_Widget' );
} );
