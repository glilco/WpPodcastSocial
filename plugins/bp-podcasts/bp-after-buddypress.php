<?php

if(!bp_is_active('groups')) {
  die('Needs BuddyPress groups to work');
}

if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );


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
    if( get_page_by_title( 'uploadopml' ) == NULL )
        create_pages_fly( 'uploadopml' );   
}
add_action('init', 'create_podcast_pages');




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
    } else if(is_page() && $pagename=='cadastrapodcast') {
        return plugin_dir_path( __FILE__ ) . '/page-cadastrapodcast.php';
    } else if(is_page() && $pagename=='uploadopml') {
        return plugin_dir_path( __FILE__ ) . '/page-uploadopml.php';
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



require( plugin_dir_path( __FILE__ ) . 'bp-class-parse-feed.php' );

require( plugin_dir_path( __FILE__ ) . 'bp-class-parse-opml.php' );



/**
 * Create groups
 */
require plugin_dir_path( __FILE__ ) . '/bp-podcasts-create-group.php';



function recebe_feed_url() {
    $podcast = create_podcast_feed($_POST["feed_url"]);
    
    wp_redirect( bp_get_group_permalink( $podcast ) );
    exit(); 
}
add_action( 'admin_post_nopriv_cadastra_podcast', 'recebe_feed_url' );
add_action( 'admin_post_cadastra_podcast', 'recebe_feed_url' );




function recebe_opml() {
    if (!is_user_logged_in()) {
        wp_redirect( get_permalink(get_page_by_title( 'uploadopml' )->ID) );
    }
    $opml_parser = new ParseOPML($_FILES['opml_file']['tmp_name']);
    
    $podcasts_urls = $opml_parser->parse_opml_file();
    
    foreach($podcasts_urls as $feed_url) {
      $podcast = create_podcast_feed($feed_url);
      echo '<br/> Podcast:';
      var_dump($podcast);
      echo '<br/> Feed URL';
      var_dump($feed_url);
      
      if($podcast) {
        echo '<br/> Groups Accept Invite:';
        var_dump(groups_accept_invite( get_current_user_id(), $podcast->id ));
      }
    }
die();
    wp_redirect( home_url() );
    exit(); 
}
add_action( 'admin_post_nopriv_upload_opml', 'recebe_opml' );
add_action( 'admin_post_upload_opml', 'recebe_opml' );


function create_podcast_feed($feed_url) {
    $groups = get_existent_podcast_feed($feed_url);
    
    
    $group = null;
    if($groups['total'] > 0) {
      $group = $groups['groups'][0];
    } else {
      $feed_parser = new ParseFeed($feed_url);
      $podcast_data= $feed_parser->parse_podcast_feed();
      unset($feed_parser);
      
      if(!$podcast_data) {
          echo '<br /> Não foi possível obter dados do podcast ';
          return false;
      }
      
      echo '<br/><br/> Podcast Data:';
      
      var_dump($podcast_data);
      echo '<br/><br/>';
      
      
      
      $group_id = create_a_group($podcast_data['TITLE'], $podcast_data['DESCRIPTION'], $podcast_data['LINK'], $feed_url, $podcast_data['URL']);
      $group = groups_get_group( array( 'group_id' => $group_id) );
    }
    
    echo '<br /> Grupo metadata' . $group->name . '<br />';
    var_dump(groups_get_groupmeta($group->id));
    echo '<br /><br />';
  
    return $group;
}

function get_existent_podcast_feed($feed_url) {
    $groups = groups_get_groups(array(
      'meta_query' => array(
          'meta_key'=>'podcast-feed-url',
          'meta_value'=>untrailingslashit($feed_url)
      )
    ));
    
    echo '<br /><br /> Grupos existentes <br />';
    var_dump($groups);
    echo '<br /> Grupos existentes, Feed URL ' . $feed_url . '<br /><br />';
    return $groups;
}



