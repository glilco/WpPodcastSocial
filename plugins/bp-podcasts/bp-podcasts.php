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
