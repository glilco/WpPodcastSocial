<?php
$pagename = get_query_var('pagename');
$grous_args = array('per_page'=>0);
if($pagename == 'getmyopmlpodcasts') {
	if(is_user_logged_in()) {
		$grous_args['user_id'] = get_current_user_id();
	} else {
		@wp_redirect( home_url());
		exit();
	}
}

@ob_clean(); //clear buffer
@header('Content-type: text/xml');
@header('Content-Disposition: attachment; filename="opml_backup.xml"');



$xml = new SimpleXMLElement('<xml/>');
$opml = $xml->addChild('opml');
$opml->addAttribute('version', '1.0');

$head = $opml->addChild('head');
$head->addChild('title','Backup de podcasts gerado pela Rede de Podcasts');

$body = $opml->addChild('body');
$outline = $body->addChild('outline');
$outline->addAttribute('text','feeds');


if ( bp_has_groups($grous_args) )  {
	while ( bp_groups() ) {
		bp_the_group();
		$grupo = $outline->addChild('outline');
		$grupo->addAttribute('type','rss');
		$grupo->addAttribute('text',bp_get_group_name());
		$grupo->addAttribute('xmlUrl', custom_field('podcast-feed-url'));
	}
}

echo $xml->asXML(); 
exit(); 
