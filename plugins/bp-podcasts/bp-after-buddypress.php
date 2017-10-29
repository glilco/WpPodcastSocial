<?php
global $debug_text;
$debug_text = true;

if(!bp_is_active('groups') && !is_admin()) {
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
    if( get_page_by_title( 'createpodcast' ) == NULL )
        create_pages_fly( 'createpodcast' );
    if( get_page_by_title( 'loadpodcastslist' ) == NULL )
        create_pages_fly( 'loadpodcastslist' );   
}
add_action('init', 'create_podcast_pages');




function plugin_function_name($template) {
    $pagename = get_query_var('pagename');
    if(is_page() && $pagename=='createpodcast') {
        return plugin_dir_path( __FILE__ ) . '/pages/page-createpodcast.php';
    } else if(is_page() && $pagename=='loadpodcastslist') {
        return plugin_dir_path( __FILE__ ) . '/pages/page-loadpodcastslist.php';
    }
    return $template;
}
add_filter( "page_template", "plugin_function_name" );



require( plugin_dir_path( __FILE__ ) . '/classes/bp-class-parse-feed.php' );

require( plugin_dir_path( __FILE__ ) . '/classes/bp-class-parse-opml.php' );



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


function formatSeconds( $seconds )
{
  $hours = 0;
  $milliseconds = str_replace( "0.", '', $seconds - floor( $seconds ) );

  if ( $seconds > 3600 )
  {
    $hours = floor( $seconds / 3600 );
  }
  $seconds = $seconds % 3600;


  return str_pad( $hours, 2, '0', STR_PAD_LEFT )
       . gmdate( ':i:s', $seconds )
       . ($milliseconds ? ".$milliseconds" : '')
  ;
}

function recebe_opml() {
	global $debug_text;
	
    if (!is_user_logged_in()) {
        wp_redirect( get_permalink(get_page_by_title( 'loadpodcastslist' )->ID) );
    }
    
    
    $user_id = get_current_user_id();
    
    $opml_parser = new ParseOPML($_FILES['opml_file']['tmp_name']);
    
    $podcasts_urls = $opml_parser->parse_opml_file();
    
    foreach($podcasts_urls as $feed_url) {
      $podcast = create_podcast_feed($feed_url);
      if($debug_text)echo '<br/> Podcast:';
      if($debug_text)var_dump($podcast);
      if($debug_text)echo '<br/> Feed URL';
      if($debug_text)var_dump($feed_url);
      
      if($podcast) {
        if($debug_text)echo "<br/> Groups Accept Invite: user: $user_id podcast: $podcast->id ";
        $accept_user = groups_accept_invite( $user_id , $podcast->id );
        if($debug_text)var_dump($accept_user);
      }
    }
    
    wp_redirect( home_url() );
    exit(); 
}
add_action( 'admin_post_nopriv_upload_opml', 'recebe_opml' );
add_action( 'admin_post_upload_opml', 'recebe_opml' );


function create_podcast_feed($feed_url) {
	global $debug_text;
    $groups = get_existent_podcast_feed($feed_url);
    
    
    $group = null;
    if($groups['total'] > 0) {
      $group = $groups['groups'][0];
    } else {
      $feed_parser = new ParseFeed($feed_url);
      $podcast_data= $feed_parser->parse_podcast_feed();
      unset($feed_parser);
      
      
      if($debug_text)echo '<br/><br/> Podcast Data:';
      
      if($debug_text)var_dump($podcast_data);
      if($debug_text)echo '<br/><br/>';

      if(!$podcast_data || !isset($podcast_data['TITLE']) || !isset($podcast_data['DESCRIPTION']) || !isset($podcast_data['LINK'])) {
          if($debug_text)echo '<br /> Não foi possível obter dados do podcast ';
          return false;
      }
      
      $groups = get_existent_podcast_feed($feed_url, $podcast_data['LINK']);
      if($groups['total'] > 0) {
		$group = $groups['groups'][0];
	  } else {
		$url_image = isset($podcast_data['URL'])?$podcast_data['URL']:'';
		$itunes_image = isset($podcast_data['ITUNESIMAGE'])?$podcast_data['ITUNESIMAGE']:'';
		$itunes_author = isset($podcast_data['ITUNES:AUTHOR'])?$podcast_data['ITUNES:AUTHOR']:'';
		$group_id = create_a_group($podcast_data['TITLE'], $podcast_data['DESCRIPTION'], $podcast_data['LINK'],  $feed_url, $url_image, $itunes_image, $itunes_author);
		$group = groups_get_group( array( 'group_id' => $group_id) );
	  }
    }
    
    if($debug_text)echo '<br /> Grupo metadata' . $group->name . '<br />';
    if($debug_text)var_dump(groups_get_groupmeta($group->id));
    if($debug_text)echo '<br /><br />';
  
    return $group;
}

function get_existent_podcast_feed($feed_url, $podcast_site="") {
	global $debug_text;
	$meta_query = array(
      'relation' => 'OR',
      array(
          'key'=>'podcast-feed-url',
          'value'=>esc_url_raw(untrailingslashit($feed_url))
      )
	);
	
	if(isset($podcast_site) && trim($podcast_site) !== '') {
		$meta_query[] = array(
          'key'=>'podcast-site',
          'value'=>esc_url_raw(untrailingslashit($podcast_site))
		);
	}
	
    $groups = groups_get_groups(array(
	  'type'=>'active',
	  'per_page' => 1,
      'meta_query' => $meta_query
    ));
    
    if($debug_text)echo '<br /><br /> Grupos existentes <br />';
    if($debug_text)var_dump($groups);
    if($debug_text)echo '<br /> Grupos existentes, Feed URL ' . $feed_url . '<br /><br />';
    return $groups;
}

add_action( 'wp_enqueue_scripts', 'addcssAndScripts');
function addcssAndScripts()
{
    if ( is_page('loadpodcastslist') )
    {
        wp_enqueue_script( 'load-button', plugin_dir_url( __FILE__ ) . '/js/bp-load-button.js', array('jquery'));
    }
    wp_enqueue_script( 'hide-group-admin', plugin_dir_url( __FILE__ ) . '/js/bp-hide-group-admin.js', array('jquery'));
}



