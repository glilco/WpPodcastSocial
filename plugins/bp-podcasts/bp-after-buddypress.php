<?php
global $debug_text;
$debug_text = false;

if(!bp_is_active('groups') && !is_admin()) {
  die('Needs BuddyPress groups to work');
}

require( plugin_dir_path( __FILE__ ) . 'bp-create-pages.php' );

require( plugin_dir_path( __FILE__ ) . '/classes/bp-class-parse-feed.php' );

require( plugin_dir_path( __FILE__ ) . '/classes/bp-class-parse-opml.php' );

require( plugin_dir_path( __FILE__ ) . '/bp-podcasts-create-group.php' );



function recebe_feed_url() {
	$id_usuario='';
	if(is_user_logged_in()) {
		$id_usuario=get_current_user_id();
	}
    $podcast = create_podcast_feed($_POST["feed_url"], $id_usuario);
    
    wp_redirect( bp_get_group_permalink( $podcast ) );
    exit(); 
}
add_action( 'admin_post_nopriv_cadastra_podcast', 'recebe_feed_url' );
add_action( 'admin_post_cadastra_podcast', 'recebe_feed_url' );



function recebe_opml() {
	global $debug_text;
	
    if (!is_user_logged_in()) {
        wp_redirect( get_permalink(get_page_by_path( 'loadpodcastslist' )->ID) );
    }
    
    
    $user_id = get_current_user_id();
    
    $opml_parser = new ParseOPML($_FILES['opml_file']['tmp_name']);
    
    $podcasts_urls = $opml_parser->parse_opml_file();
    
    $time_difference = 10;
    
    $last_time = time();
    foreach($podcasts_urls as $feed_url) {
      $sche_time = time() + $time_difference > $last_time + $time_difference?time() + $time_difference:$last_time + $time_difference;
	  //wp_schedule_single_event( $sche_time, 'create_podcast_hook', array($feed_url, $user_id) );
	  $created = create_podcast_feed($feed_url, $user_id, $sche_time);
	  if(!$created) {
		$last_time = $sche_time;
	  }
    }
    wp_redirect( get_permalink(get_page_by_path( 'listreceived' )->ID) );
    exit(); 
}
add_action( 'admin_post_nopriv_upload_opml', 'recebe_opml' );
add_action( 'admin_post_upload_opml', 'recebe_opml' );

function create_podcast_feed($feed_url, $user_id, $schedule=0) {
	global $debug_text;
    $groups = get_existent_podcast_feed($feed_url);
    
    
    $group = null;
    if($groups['total'] > 0) {
        $group = $groups['groups'][0];
    } else if($schedule) {
        wp_schedule_single_event( $schedule, 'podcast_group_creation_hook', array($feed_url, $user_id, true) );
        return false;
    } else {
	    $group = parse_feed_and_create_podcast($feed_url, $user_id);
	}
    
    if($debug_text)echo '<br /> Grupo metadata' . $group->name . '<br />';
    if($debug_text)var_dump(groups_get_groupmeta($group->id));
    if($debug_text)echo '<br /><br />';
	
	if(isset($user_id) && trim($user_id) !=='') {
		groups_accept_invite( $user_id , $group->id );
	}
	
    return $group;
}

add_action( 'podcast_group_creation_hook', 'parse_feed_and_create_podcast', 10, 2);
function parse_feed_and_create_podcast($feed_url, $user_id, $schedule = false) {
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
	$group = false;

	$groups = get_existent_podcast_feed($feed_url, $podcast_data['LINK']);
	if($groups['total'] > 0) {
		$group = $groups['groups'][0];
	} else {
		$url_image = isset($podcast_data['URL'])?$podcast_data['URL']:'';
		$itunes_image = isset($podcast_data['ITUNESIMAGE'])?$podcast_data['ITUNESIMAGE']:'';
		$itunes_author = isset($podcast_data['ITUNES:AUTHOR'])?$podcast_data['ITUNES:AUTHOR']:'';

		$group_id = false;

		
		$group_id = create_a_group($podcast_data['TITLE'], $podcast_data['DESCRIPTION'], $podcast_data['LINK'],  $feed_url, $url_image, $itunes_image, $itunes_author, $user_id);
			
		if(!$schedule) {
			$group = groups_get_group( array( 'group_id' => $group_id) );
		}
	}
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
