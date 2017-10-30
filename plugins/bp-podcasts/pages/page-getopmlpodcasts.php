<?php
$xml = new SimpleXMLElement('<xml/>');
$opml = $xml->addChild('opml');
$opml->addAttribute('version', '1.0');

$head = $opml->addChild('head');
$head->addChild('title','Backup de podcasts gerado pela Rede de Podcasts');

$body = $opml->addChild('body');
$outline = $body->addChild('outline');
$outline->addAttribute('text','feeds');

if ( bp_has_groups('per_page=0') )  {
	while ( bp_groups() ) {
		bp_the_group();
		$grupo = $outline->addChild('outline');
		$grupo->addAttribute('type','rss');
		$grupo->addAttribute('text',bp_get_group_name());
		$grupo->addAttribute('xmlUrl', custom_field('podcast-feed-url'));
	}
}


ob_clean(); //clear buffer
header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="opml_backup.xml"');
echo $xml->asXML(); 
exit(); 
