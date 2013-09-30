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
		
	add_action( 'admin_enqueue_scripts', 'UKMWambassador_scriptsandstyles' );
}

function UKMambassador_menu() {
	$page = add_menu_page('Ambassadører', 'Ambassadører', 'editor', 'UKMambassador', 'UKMambassador', 'http://ico.ukm.no/cloud-menu.png',197);
}

function UKMambassador() {
	require_once('UKM/monstring.class.php');
	$pl = new monstring(get_option('pl_id'));
	
				if(get_option('site_type')=='fylke') {
				$monstringer = new SQL("SELECT `pl`.`pl_id`, `pl_name` FROM `smartukm_place` AS `pl`
										JOIN `smartukm_rel_pl_k` AS `rel` ON (`rel`.`pl_id` = `pl`.`pl_id`)
										JOIN `smartukm_kommune` AS `k` ON (`k`.`id` = `rel`.`k_id`)
										WHERE `k`.`idfylke` = '#fylke'
										AND `pl`.`season` = '#season'
										GROUP BY `pl`.`pl_id`
										ORDER BY `pl`.`pl_name`",
										array('fylke'=>get_option('fylke'),
											  'season'=>get_option('season')));
				$monstringer = $monstringer->run();
				$options = '<select name="pl_invite_id">';
				if($monstringer)
					while($r = mysql_fetch_assoc($monstringer)) {
						$options .= '<option value="'.$r['pl_id'].'">'.utf8_encode($r['pl_name']).'</option>';
					}
				$options .= '</select>';
			} else {
				$options = '<input type="hidden" value="'.get_option('pl_id').'" name="pl_invite_id" />';
			}
	
	
	$infos = array('site_type' => get_option('site_type'),
				   'ambassadorer' => $pl->ambassadorer());
	echo TWIG('ambassador_mine.twig.html', $infos , dirname(__FILE__));
}

function UKMWambassador_scriptsandstyles() {
	wp_enqueue_style('ukmambassador', plugin_dir_url( __FILE__ ) .'/css/ukmambassador.css');
}