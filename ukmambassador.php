<?php  
/* 
Plugin Name: UKM Ambassadør
Plugin URI: http://www.ukm-norge.no
Description: 2013-versjonen av UKM-ambassadører
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/

require_once('UKM/inc/twig-admin.inc.php');

## HOOK MENU AND SCRIPTS
if(is_admin()) {
	global $blog_id;
	if($blog_id != 1)
		add_action('admin_menu', 'UKMambassador_menu');
}

function UKMambassador_menu() {
	$page = add_menu_page('Ambassadører', 'Ambassadører', 'editor', 'UKMambassador', 'UKMambassador', 'http://ico.ukm.no/cloud-menu.png',197);
}

function UKMambassador() {
	$pl = new place(get_option('pl_id'));
	$infos = array('site_type' => get_option('site_type'),
				   'ambassadorer' => $pl->ambassadorer());
	echo TWIG('ambassador_mine.twig.html', $infos , dirname(__FILE__));
}