<?php

function create_pages_fly($my_page) {
	$title = isset($my_page['title']) && trim($my_page['title']) !=''?$my_page['title']:$my_page['slug'];
	
    $createPage = array(
      'post_title'    => $title,
      'post_content'  => isset($my_page['content'])?$my_page['content']:'',
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type'     => 'page',
      'post_name'     => $my_page['slug']
    );

    // Insert the post into the database
    wp_insert_post( $createPage );
}

function create_podcast_pages() {
	create_pages(array(
		array(
			'slug'=>'createpodcast',
			'title'=>'Cadastrar Podcast'
		),
		array(
			'slug'=>'loadpodcastslist',
			'title'=>'Enviar lista de podcasts'
		),
		array(
			'slug'=>'listreceived',
			'title'=>'Lista de podcasts recebida',
			'content'=>"<p>A sua lista foi recebida com sucesso e está sendo processada.</p>
        <p>Em breve, todos os podcasts enviados estarão na sua lista de podcasts assinados.</p>
        <p>Caso, após alguns minutos, algum dos podcasts da sua lista não tenha sido carregado, entre em contato com o administrador.</p>"
		),
		array(
			'slug'=>'getopmlpodcasts',
		),
		array(
			'slug'=>'getmyopmlpodcasts',
		),
	));
}
add_action('init', 'create_podcast_pages');

function create_pages($page_data) {
	foreach($page_data as $my_page) {
		if( get_page_by_path($my_page['slug']) == NULL ) {
			create_pages_fly($my_page);
		}
	}
}


function plugin_function_name($template) {
    $pagename = get_query_var('pagename');
    if(is_page() && $pagename=='createpodcast') {
        return plugin_dir_path( __FILE__ ) . '/pages/page-createpodcast.php';
    } else if(is_page() && $pagename=='loadpodcastslist') {
        return plugin_dir_path( __FILE__ ) . '/pages/page-loadpodcastslist.php';
    } else if(is_page() && $pagename=='getopmlpodcasts' || $pagename=='getmyopmlpodcasts') {
        return plugin_dir_path( __FILE__ ) . '/pages/page-getopmlpodcasts.php';
    }
    return $template;
}
add_filter( "page_template", "plugin_function_name" );
