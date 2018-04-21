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

add_action('network_admin_menu', 'UKMambassador_Norgemenu');
add_filter('UKMWPNETWDASH_messages', 'UKMambassador_network_dash_messages', 150);


if( get_option('ukm_top_page') == 'ambassadorer' ) {
	add_action('save_post', 'UKMambassador_cleanCache' );
	add_action('delete_post', 'UKMambassador_cleanCache' );
}

function UKMambassador_cleanCache( $post_id ) {
	require_once('hook_cache.inc.php');
	hook_ambassador_cache_delete( $post_id );
}


function UKMambassador_network_dash_messages( $MESSAGES ) {
	$sql = new SQL("SELECT COUNT(`amb`.`amb_id`) AS `num`
					FROM `ukm_ambassador` AS `amb`
					JOIN `ukm_ambassador_skjorte` AS `skjorte`
						ON (`amb`.`amb_id` = `skjorte`.`amb_id`)
					WHERE `skjorte`.`sendt` = 'false'");
	$ant_venter = (int) $sql->run('field', 'num');
	
	if( $ant_venter > 4) {
		$MESSAGES[] = array('level' 	=> 'alert-'. ($ant_venter > 9 ? 'error' : 'info'),
							'module'	=> 'Ambassadører',
							'header'	=> $ant_venter . ' ambassadører venter på velkomstpakke',
							'body' 		=> 'Pakkene må sendes ut regelmessig. Er det kort tid siden forrige pakke ble sendt ut kan dette varselet stå en liten stund.',
							'link'		=> 'admin.php?page=UKMambassadorNorge&action=pakke'
							);
	}
	return $MESSAGES;
}

## HOOK MENU AND SCRIPTS
if(is_admin()) {
	$type = get_option('site_type');
	if( $type == 'fylke' || $type == 'kommune' ) {
		add_action('UKM_admin_menu', 'UKMambassador_menu');
	}
}

function UKMambassador_menu() {
	UKM_add_menu_page('resources','Ambassadører', 'Ambassadører', 'editor', 'UKMambassador', 'UKMambassador', '//ico.ukm.no/ambassador-menu.png',10);
	UKM_add_scripts_and_styles('UKMambassador', 'UKMambassador_scripts_and_styles' );
}

function UKMambassador_Norgemenu() {
	$page = add_menu_page('Ambassadører', 'Ambassadører', 'editor', 'UKMambassadorNorge', 'UKMambassadorNorge', '//ico.ukm.no/ambassador-menu.png',140);
	add_action( 'admin_print_styles-' . $page, 'UKMambassador_scripts_and_styles' );
}

function UKMambassador_scripts_and_styles(){

	wp_enqueue_style('ukmambassador_css', plugin_dir_url( __FILE__ ) .'/css/ukmambassador.css');
	wp_enqueue_script('ukmambassador_js', plugin_dir_url( __FILE__ ) .'/ukmambassador.js');

	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
}


function UKMambassadorNorge() {
	if(!isset($_GET['action']))
		$_GET['action'] = 'info';
		
	switch($_GET['action']) {
		case 'pakke': 
			require_once('amb_norge_controller_pakke.inc.php');
			echo TWIG('ambassador_norge_pakke.twig.html', $infos, dirname(__FILE__));
			break;
		case 'liste':
			require_once('amb_norge_controller_liste.inc.php');
			echo TWIG('ambassador_norge_liste.twig.html', $infos, dirname(__FILE__));
			break;
		case 'update':
			require_once('amb_norge_controller_update.inc.php');
			echo TWIG('ambassador_norge_update.twig.html', $infos, dirname(__FILE__));
			break;
		default:
			require_once('amb_norge_controller_info.inc.php');
			echo TWIG('ambassador_norge.twig.html', $infos, dirname(__FILE__));
			break;
	}
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
	
	require_once('UKM/ambassador.class.php');
	if(isset($_GET['delete'])){
		$amb = new ambassador( (int) $_GET['delete'] );
		$res_del = $amb->delete();
	}
	
	$infos = array('site_type' => get_option('site_type'),
				   'ambassadorer' => $pl->ambassadorer(),
				   'monstringer' => $options,
				   'invites' => $send_status);
	if(isset($_GET['delete'])){
		$infos['deleted'] = $res_del;
	}
	echo TWIG('ambassador_mine.twig.html', $infos , dirname(__FILE__));
}

function UKMambassador_invite() {
	$invites = explode(',', $_POST['ambassadorinvite']);
	$invites = str_replace(' ', '', $invites);
	
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