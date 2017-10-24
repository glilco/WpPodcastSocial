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




function podcast_group_types() {
    bp_groups_register_group_type( 'podcast', array(
        'labels' => array(
            'name' => 'Podcasts',
            'singular_name' => 'Podcast'
        ),
 
        // New parameters as of BP 2.7.
        'has_directory' => 'podcasts',
        'show_in_create_screen' => true,
        'show_in_list' => true,
        'description' => 'Podcast representation',
        'create_screen_checked' => true
    ) );
}
add_action( 'bp_groups_register_group_types', 'podcast_group_types' );



function rewrite_users_podcast(){
    global $wp; 
    $wp->add_query_var('podcastusername');
    add_rewrite_rule(
        '^([^/]+)/?$',
        'index.php?pagename=usuariopodcast&podcastusername=$matches[1]',
        'top' );
        
    global $wp_rewrite; $wp_rewrite->flush_rules();
}
add_action( 'init', 'rewrite_users_podcast' );

function plugin_function_name($template) {
    $pagename = get_query_var('pagename');
    if(is_page() && $pagename=="usuariopodcast") {
        $usuario_username = get_query_var('podcastusername');
		$usuario = get_user_by( "slug", $usuario_username );
        if($usuario) {
            return plugin_dir_path( __FILE__ ) . '/page-usuariopodcast.php';
        }
    }
    return $template;
}
add_filter( "page_template", "plugin_function_name" );



 
function redireciona_nao_usuario() {
  $pagename = get_query_var('pagename');
    if(is_page() && $pagename=="usuariopodcast") {
        $usuario_username = get_query_var('podcastusername');
		$usuario = get_user_by( "slug", $usuario_username );
        if(!$usuario) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }
}
add_action( 'template_redirect', 'redireciona_nao_usuario' );




function bp_group_meta_init() {
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
}
add_action( 'bp_include', 'bp_group_meta_init' );
/* If you have code that does not need BuddyPress to run, then add it here. */

/**
 * Create groups
 */
require plugin_dir_path( __FILE__ ) . '/bp-podcasts-create-group.php';

