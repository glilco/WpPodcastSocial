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
	function custom_field($meta_key='') {
		//get current group id and load meta_key value if passed. If not pass it blank
		return groups_get_groupmeta( bp_get_group_id(), $meta_key) ;
	}
	//code if using seperate files require( dirname( __FILE__ ) . '/buddypress-group-meta.php' );
	// This function is our custom field's form that is called in create a group and when editing group details
	function group_header_fields_markup() {
		global $bp, $wpdb;?>
		<label for="podcast-site">Podcast Site</label>
		<input id="podcast-site" type="text" name="podcast-site" value="<?php echo defined('custom_field')?custom_field('podcast-site'):""; ?>" />
		<br>
		<label for="podcast-feed-url">Podcast Feed URL</label>
		<input id="podcast-feed-url" type="text" name="podcast-feed-url" value="<?php echo defined('custom_field')?custom_field('podcast-feed-url'):""; ?>" />
		<br>
	<?php 
	}
	// This saves the custom group meta – props to Boone for the function
	// Where $plain_fields = array.. you may add additional fields, eg
	//  $plain_fields = array(
	//      'field-one',
	//      'field-two'
	//  );
	function group_header_fields_save( $group_id ) {
		global $bp, $wpdb;
		$plain_fields = array(
			'podcast-site',
			'podcast-feed-url'
		);
		foreach( $plain_fields as $field ) {
			$key = $field;
			if ( isset( $_POST[$key] ) ) {
				$value = $_POST[$key];
				groups_update_groupmeta( $group_id, $field, $value );
			}
		}
	}

	add_filter( 'groups_custom_group_fields_editable', 'group_header_fields_markup' );
	add_action( 'groups_group_details_edited', 'group_header_fields_save' );
	add_action( 'groups_created_group',  'group_header_fields_save' );

	// Show the custom field in the group header
	function show_field_in_header( ) {
		echo "<p> Site do podcast: <a href=\"" . custom_field('podcast-site') . "\">" . custom_field('podcast-site') . "</a></p>";
		echo "<p> Feed do podcast: <a href=\"" . custom_field('podcast-feed-url') . "\">" . custom_field('podcast-feed-url') . "</a></p>";
	}
	add_action('bp_group_header_meta' , 'show_field_in_header') ;
	
	
	
    require( plugin_dir_path( __FILE__ ) . 'bp-after-buddypress.php' );
}
add_action( 'bp_include', 'my_plugin_init' );


