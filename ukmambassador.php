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
	if($blog_id == 1)
		add_action('admin_menu', 'UKMambassador_Norgemenu');
	else
		add_action('admin_menu', 'UKMambassador_menu');

		
	add_action( 'admin_enqueue_scripts', 'UKMambassador_scriptsandstyles' );
}

function UKMambassador_menu() {
	$page = add_menu_page('Ambassadører', 'Ambassadører', 'editor', 'UKMambassador', 'UKMambassador', 'http://ico.ukm.no/ambassador-menu.png',120);
}

function UKMambassador_Norgemenu() {
	$page = add_menu_page('Ambassadører', 'Ambassadører', 'editor', 'UKMambassadorNorge', 'UKMambassadorNorge', 'http://ico.ukm.no/ambassador-menu.png',120);
	add_action( 'admin_print_styles-' . $page, 'UKMambassador_scripts_and_styles' );
}

function UKMambassador_scripts_and_styles(){
	wp_enqueue_script('handlebars_js');
	wp_enqueue_script('bootstrap_js');
	wp_enqueue_style('bootstrap_css');

}


function UKMambassadorNorge() {
	echo TWIG('ambassador_norge.twig.html', array(), dirname(__FILE__));
}


function UKMambassador() {
	if($_SERVER['REQUEST_METHOD']==='POST') {
		$send_status = UKMambassador_invite();
	} else
		$send_status = false;
		
	require_once('UKM/monstring.class.php');
	$pl = new monstring(get_option('pl_id'));
	
	$options = array();
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
		if($monstringer)
			while($r = mysql_fetch_assoc($monstringer)) {
				$options[] = array('pl_id' => $r['pl_id'], 'name' => utf8_encode($r['pl_name']));
			}
	} else {
		$options[] = array('pl_id' => get_option('pl_id'));
	}
	
	$infos = array('site_type' => get_option('site_type'),
				   'ambassadorer' => $pl->ambassadorer(),
				   'monstringer' => $options,
				   'invites' => $send_status);
	echo TWIG('ambassador_mine.twig.html', $infos , dirname(__FILE__));
}

function UKMambassador_invite() {
	$invites = explode(',', $_POST['ambassadorinvite']);
	$send_status = array();
	
	if(isset($_POST['pl_invite_id']))
		$plid = $_POST['pl_invite_id'];
	else
		$plid = get_option('pl_id');
	
	require_once('UKM/ambassador.class.php');
	$ambassador = new ambassador(false);
	
	for($i=0; $i<sizeof($invites); $i++) {
		$send_status[] = $ambassador->invite($invites[$i], $plid);
	}
	return $send_status;
}

function UKMambassador_scriptsandstyles() {
	wp_enqueue_style('ukmambassador', plugin_dir_url( __FILE__ ) .'/css/ukmambassador.css');
}