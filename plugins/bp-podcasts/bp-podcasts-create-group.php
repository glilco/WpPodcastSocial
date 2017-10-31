<?php

function create_a_group($name, $description, $site_link, $feed_url, $image_url, $itunes_image, $itunes_author, $id_usuario_add) {
	
	$groups = get_existent_podcast_feed($feed_url, $site_link);
	
	if($groups['total'] > 0) {
		$group = $groups['groups'][0];
		if(isset($id_usuario_add) && trim($id_usuario_add) !=='') {
			groups_accept_invite( $id_usuario_add , $group->id );
		}
		return $group->id;
	}
	
	$user_admin = get_users(array('role'=>'administrator','number'=>1))[0];
	
    $creator_id = $user_admin->ID;
	
   	$parameters = array(
		'creator_id'   => $creator_id,
		'name'         => $name,
		'description'  => $description,
		'enable_forum' => 0,
		'date_created' => bp_core_current_time()
	);
  
    $saved = groups_create_group($parameters);

    if ( $saved )
    {
        $id = $saved;
        
        groups_update_groupmeta( $id, 'podcast-site', esc_url_raw(untrailingslashit($site_link)) );
        groups_update_groupmeta( $id, 'podcast-feed-url', esc_url_raw(untrailingslashit($feed_url)) );
        
        if(isset($itunes_author) && trim($itunes_author) != '') {
			groups_update_groupmeta( $id, 'podcast-author', $itunes_author );
		}
        
        /* Choose image to use. If there is not a valid image, return the group id */
        if(isset($itunes_image) && trim($itunes_image) !== '' && @getimagesize($itunes_image)) {
			$image_url = $itunes_image;
		} else if(!isset($image_url) || trim($image_url) === '' || !@getimagesize($image_url)) {
			return $id;
		}

		if(isset($image_url) && trim($image_url) !== '') {
			$upload_dir   = trailingslashit(wp_upload_dir()['path']);
			
			
			$image_url_no_parameters = explode("?", $image_url)[0];
			$image_file_name = basename($image_url_no_parameters);
			$file_path = $upload_dir . $image_file_name;
			ini_set("user_agent","Opera/9.80 (Windows NT 6.1; U; Edition Campaign 21; en-GB) Presto/2.7.62 Version/11.00");
			$file = @fopen($image_url, 'r');
			
			
			if($file) {
			
				if(file_put_contents($file_path, $file)) {
					
					$type_params = array(
						'item_id'   => $id,
						'object'    => 'group',
						'component' => 'groups',
						'image'     => $file_path,
					);
					
					$avatar_attachment = new BP_Attachment_Avatar();

					$shrinked = $avatar_attachment->shrink( $file_path, bp_core_avatar_full_width() );
					
					
					if(!bp_attachments_create_item_type('cover_image', $type_params)) {
						return false;
					}
					
					$type_params['image'] = $shrinked['path'];
					
					/* Set SuperUser to be able to change avatar */
					$save_current_user = wp_get_current_user();
					//$super_user = get_user_by('login', $super_admins[0]);
					wp_set_current_user($user_admin->ID, $user_admin->name); 
					
					if(!bp_attachments_create_item_type('avatar', $type_params)) {
						return false;
					}
					
					wp_delete_file($shrinked['path']);
					wp_delete_file($file_path);
					
					/* Restore the logged user */
					if($save_current_user->ID == 0) {
						wp_logout();
					} else {
						wp_set_current_user($save_current_user->ID, $save_current_user->name);
					}
				}
			}
		}
		
		if(isset($id_usuario_add) && trim($id_usuario_add) !=='') {
			groups_accept_invite( $id_usuario_add , $id );
		}
		
        return $id;
    }
    return false;
}
