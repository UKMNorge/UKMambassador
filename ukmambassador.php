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

add_filter('UKMWPNETWDASH_messages', 'UKMambassador_network_dash_messages', 150);

function UKMambassador_network_dash_messages( $MESSAGES ) {
	if( date('Y') > 2016 && date('m') > 8 ) {
		$MESSAGES[] = array('level' 	=> 'alert-error',
							'module'	=> 'Ambassadører',
							'header'	=> 'Mest sannsynlig er det på tide å slette ambassadør-modulen',
							'body' 		=> 'I utgangspunktet burde ikke denne modulen være nødvendig lengre.',
							);
	}
	return $MESSAGES;
}

## HOOK MENU AND SCRIPTS
if(is_admin()) {
	$type = get_option('site_type');
	if( $type == 'fylke' || $type == 'kommune' ) {
		if( !get_option('ambassador_bye_bye') || $_GET['page'] == 'UKMambassador' ) {
			add_action('UKM_admin_menu', 'UKMambassador_menu');
		}
	}
}

function UKMambassador_menu() {
	UKM_add_menu_page('kommunikasjon','Ambassadører', 'Ambassadører', 'editor', 'UKMambassador', 'UKMambassador', '//ico.ukm.no/ambassador-menu.png',30);
	UKM_add_scripts_and_styles('UKMambassador', 'UKMambassador_scripts_and_styles' );
}

function UKMambassador_scripts_and_styles(){
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
}

function UKMambassador() {	
	require_once('UKM/monstring.class.php');
	require_once('UKM/ambassador.class.php');

	$monstring = new monstring_v2( get_option('pl_id') );
	$infos = [
		'site_type' => get_option('site_type'),
		'monstring' => $monstring,
	];

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		if( !isset( $_POST['transfer'] ) ) {
			$infos['message'] = [
				'level' => 'danger',
				'body' => 'Du må velge minst én ambassadør å overføre'
			];
		} else {
			$transfer = $_POST['transfer'];

			foreach( $transfer as $ambassador_id ) {
				$monstring->reloadInnslag();
				$person = null;
				$innslag = null;
				$ambassador = new ambassador( $ambassador_id );

				require_once('UKM/kommune.class.php');
				require_once('UKM/write_person.class.php');
				require_once('UKM/write_innslag.class.php');
				require_once('UKM/innslag_typer.class.php');
				require_once('UKM/logger.class.php'); 
				
				// Sett opp logger
				global $current_user;
				get_currentuserinfo(); 
				UKMlogger::setID( 'wordpress', $current_user->ID, get_option('pl_id') );
				
				// Finn valgt kommune
				$kommune = new kommune( $_POST['kommune_'. $ambassador->getFacebookId() ] );

				// Hent eksisterende person, eller opprett ny
                // Ambassadør-systemet har nøyaktig fødselsdato, oppdater derfor herfra
                if( null == $ambassador->birthday || empty( $ambassador->birthday ) ) {
                    $fodt = null;
                } else {
                    $dob = DateTime::createFromFormat ( 'd/m/Y', $ambassador->birthday );
                    $fodt = $dob->getTimestamp();
                }
				$person = write_person::create(
					$ambassador->getFirstname(),
					$ambassador->getLastname(),
					$ambassador->getPhone(),
					$fodt,
					$kommune->getId()
				);

				// Oppdater e-post hvis vi har den
				if( !empty( $ambassador->getEmail() ) ) {
					$person->setEpost( $ambassador->getEmail() );
					write_person::save( $person );
				}

				// Opprett innslaget
				$innslag = write_innslag::create(
					$kommune,
					$monstring,
					innslag_typer::getByName('ressurs'),
					$person->getNavn(),
					$person
				);

				$innslag->getPersoner()->leggTil( $person );
				
				$person_med_context = $innslag->getPersoner()->get( $person->getId() );
				$person_med_context->setRolle(['ambassador'=>'Ambassadør']);
				write_person::save( $person_med_context );
				write_innslag::savePersoner( $innslag );

				$ambassador->deaktiver();
				
				$infos['overfort'][] = $innslag->getNavn();
			}
			echo TWIG('ambassador_overfort.html.twig', $infos , dirname(__FILE__));
			return;
		}
	}

	$options = array();
	$options[] = array('pl_id' => get_option('pl_id'));
	
	if(isset($_GET['delete'])){
		$amb = new ambassador( (int) $_GET['delete'] );
		$res_del = $amb->delete();
	}
	
	$pl = new monstring( get_option('pl_id') );
	$infos['ambassadorer'] = $pl->ambassadorer();
	$infos['monstringer'] = $options;
	$infos['invites'] = $send_status;
	
	if( !is_array( $infos['ambassadorer'] ) || (is_array( $infos['ambassadorer'] ) && sizeof( $infos['ambassadorer'] ) == 0 ) ) {
		update_option('ambassador_bye_bye', true);
	} 

	if(isset($_GET['delete'])){
		$infos['deleted'] = $res_del;
	}
	echo TWIG('ambassador_mine.twig.html', $infos , dirname(__FILE__));
}