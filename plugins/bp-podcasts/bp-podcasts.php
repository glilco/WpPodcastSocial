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

function create_pages_fly($pageName, $pageContent="Starter content") {
    $createPage = array(
      'post_title'    => $pageName,
      'post_content'  => $pageContent,
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type'     => 'page',
      'post_name'     => $pageName
    );

    // Insert the post into the database
    wp_insert_post( $createPage );
}

function create_podcast_pages() {
    if( get_page_by_title( 'cadastrapodcast' ) == NULL )
        create_pages_fly( 'cadastrapodcast' );
    if( get_page_by_title( 'usuariopodcast' ) == NULL )
        create_pages_fly( 'usuariopodcast' );
    if( get_page_by_title( 'podcastcadastrado' ) == NULL )
        create_pages_fly( 'podcastcadastrado', "Podcast cadastrado com sucesso" );   
}
add_action('init', 'create_podcast_pages');


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
        '^user/([^/]+)/?$',
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
    } else if(is_page() && $pagename=="cadastrapodcast") {
        return plugin_dir_path( __FILE__ ) . '/page-cadastrapodcast.php';
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



function recebe_feed_url() {
    $feed_parse = parse_podcast_feed($_POST["feed_url"]);
    var_dump($feed_parse);
    
    die();
    
    wp_redirect( get_permalink( get_page_by_title('podcastcadastrado') ) );
    exit(); 
}
add_action( 'admin_post_nopriv_cadastra_podcast', 'recebe_feed_url' );
add_action( 'admin_post_cadastra_podcast', 'recebe_feed_url' );


function parse_podcast_feed($feed_url) {
//Reading XML using the SAX(Simple API for XML) parser 
   global $podcast, $elements, $is_image, $continue_parsing;
   $podcast   = array();
   $elements   = null;
   $is_image = false;
   $parser = xml_parser_create(); 
   $continue_parsing = true;

   
   // Called to this function when tags are opened 
   function startElements($parser, $name, $attrs) {
      global $podcast, $elements, $is_image,$continue_parsing;
      
      if(!empty($name)) {
         $elements = $name;
         $is_image = $name == "IMAGE"?true:$is_image;
         
         if($name == "ITEM") {
            $continue_parsing = false;
         }
      }
   }
   
   // Called to this function when tags are closed 
   function endElements($parser, $name) {
      global $elements,$is_image, $continue_parsing;
      
      if(!empty($name) && $continue_parsing) {
         $elements = null;
         if($name == "IMAGE") {
            $is_image = false;
         }
      }
   }
   
   // Called on the text between the start and end of the tags
   function characterData($parser, $data) {
      global $podcast, $elements,$is_image,$continue_parsing;
      
      if(!empty($data) && $continue_parsing) {
         if ($elements == 'TITLE' || $elements == 'LINK' ||  $elements == 'DESCRIPTION' ||  $elements == 'URL') {
            if($is_image) {
                if($elements == 'URL') {
                    $podcast[$elements] = $data;
                }
            } else {
                if($elements != 'URL') {
                    $podcast[$elements] = $data;
                }
            }
         }
      }
   }
   
   // Creates a new XML parser and returns a resource handle referencing it to be used by the other XML functions. 
   
   
   xml_set_element_handler($parser, "startElements", "endElements");
   xml_set_character_data_handler($parser, "characterData");
   
   // open xml file
   if (!($handle = fopen($feed_url, "r"))) {
      return false;
   }
   
   while($data = fread($handle, 4096)) {
      if(!$continue_parsing) {
        break;
      }
      $ok = xml_parse($parser, $data);  // start parsing an xml document 
   }
   
   xml_parser_free($parser);
   return $podcast;
   
}




