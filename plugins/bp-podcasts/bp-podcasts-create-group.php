<?php

function create_a_group($name, $description, $site_link, $feed_url) {
   	$parameters = array(
		'name'         => $name,
		'description'  => $description,
		'enable_forum' => 0,
		'date_created' => bp_core_current_time()
	);


    $saved = groups_create_group($parameters);

    if ( $saved )
    {
        $id = $saved;
        groups_update_groupmeta( $id, 'total_member_count', 1 );
        groups_update_groupmeta( $id, 'last_activity', time() );
        
        groups_update_groupmeta( $id, 'podcast-site', $site_link );
        groups_update_groupmeta( $id, 'podcast-feed-url', $feed_url );
    
        return $id;    
    }    
    return false;

}
