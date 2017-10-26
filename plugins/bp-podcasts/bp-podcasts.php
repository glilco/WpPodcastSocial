<?php
/*
Plugin Name: BuddyPress Podcasts
Plugin URI: https://github.com/glilco/WpPodcastSocial
Description: Wordpress plugin to create a Podcasts social network
Version: 0.1a
Author: Murilo Ferraz
Author URI: https://github.com/glilco/
License: GPL2
*/

/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
function my_plugin_init() {
    require( plugin_dir_path( __FILE__ ) . 'bp-after-buddypress.php' );
}
add_action( 'bp_include', 'my_plugin_init' );
